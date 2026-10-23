<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

use Kongkodesoft\Asaas\Config;
use Kongkodesoft\Asaas\Exception\ApiException;
use Kongkodesoft\Asaas\Exception\AuthenticationException;
use Kongkodesoft\Asaas\Exception\ForbiddenException;
use Kongkodesoft\Asaas\Exception\NetworkException;
use Kongkodesoft\Asaas\Exception\NotFoundException;
use Kongkodesoft\Asaas\Exception\RateLimitException;
use Kongkodesoft\Asaas\Exception\ServerException;
use Kongkodesoft\Asaas\Exception\ValidationException;
use Kongkodesoft\Asaas\Response\Fields;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Transporte PSR-18. Sem estado mutável: seguro para uso concorrente em coroutines.
 *
 * Retry:
 *  - 429: todos os métodos (a requisição foi rejeitada, não processada); espera RateLimit-Reset se presente.
 *  - 5xx e falhas de rede: somente GET (a API não documenta Idempotency-Key; repetir POST pode duplicar cobrança).
 */
final readonly class HttpTransport implements Transport
{
    public function __construct(
        private Config $config,
        private ClientInterface $client,
        private RequestFactoryInterface $requests,
        private StreamFactoryInterface $streams,
        private Sleeper $sleeper = new UsleepSleeper(),
    ) {
    }

    public function request(string $method, string $path, array $query = [], ?array $json = null): array
    {
        $method = strtoupper($method);
        $url = $this->config->baseUrl() . '/' . ltrim($path, '/');
        $queryString = http_build_query(Payload::query($query), '', '&', PHP_QUERY_RFC3986);
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        $body = null;
        if ($json !== null && $method !== 'GET') {
            $body = json_encode(Payload::normalize($json), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $maxAttempts = $this->config->retry->maxAttempts;
        for ($attempt = 1; ; $attempt++) {
            try {
                $response = $this->client->sendRequest($this->build($method, $url, $body));
            } catch (ClientExceptionInterface $e) {
                if ($method === 'GET' && $attempt < $maxAttempts) {
                    $this->sleeper->sleep($this->backoff($attempt));
                    continue;
                }
                throw new NetworkException('Falha de rede ao chamar o Asaas: ' . $e->getMessage(), 0, $e);
            }

            $status = $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                return $this->decode($response, $status);
            }

            $exception = $this->toException($response, $status);
            $retryable = $status === 429 || ($status >= 500 && $method === 'GET');
            if ($retryable && $attempt < $maxAttempts) {
                $delay = $exception instanceof RateLimitException && $exception->resetSeconds !== null
                    ? min((float) $exception->resetSeconds, $this->config->retry->maxDelaySeconds)
                    : $this->backoff($attempt);
                $this->sleeper->sleep($delay);
                continue;
            }

            throw $exception;
        }
    }

    private function build(string $method, string $url, ?string $body): \Psr\Http\Message\RequestInterface
    {
        $request = $this->requests->createRequest($method, $url)
            ->withHeader('access_token', $this->config->apiKey)
            ->withHeader('User-Agent', $this->config->userAgent)
            ->withHeader('Accept', 'application/json');

        if ($body !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streams->createStream($body));
        }

        return $request;
    }

    private function backoff(int $attempt): float
    {
        $retry = $this->config->retry;
        $exp = min($retry->maxDelaySeconds, $retry->baseDelaySeconds * (2 ** ($attempt - 1)));

        return $exp * (random_int(50, 100) / 100);
    }

    /** @return array<array-key, mixed> */
    private function decode(ResponseInterface $response, int $status): array
    {
        $raw = (string) $response->getBody();
        if ($raw === '') {
            return [];
        }
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ApiException('Resposta inválida (JSON malformado) do Asaas.', $status, [], $e);
        }
        if (!is_array($data)) {
            throw new ApiException('Resposta inesperada do Asaas (esperado objeto JSON).', $status);
        }

        return $data;
    }

    private function toException(ResponseInterface $response, int $status): ApiException
    {
        $errors = [];
        $data = json_decode((string) $response->getBody(), true);
        if (is_array($data) && isset($data['errors']) && is_array($data['errors'])) {
            foreach ($data['errors'] as $error) {
                if (is_array($error)) {
                    $errors[] = [
                        'code' => Fields::string($error, 'code') ?? '',
                        'description' => Fields::string($error, 'description') ?? '',
                    ];
                }
            }
        }

        $message = $errors !== []
            ? implode('; ', array_map(static fn (array $e): string => $e['description'], $errors))
            : sprintf('Asaas respondeu HTTP %d.', $status);

        if ($status === 429) {
            $reset = $response->getHeaderLine('RateLimit-Reset');

            return new RateLimitException($message, $errors, ctype_digit($reset) ? (int) $reset : null);
        }

        return match (true) {
            $status === 400 => new ValidationException($message, $status, $errors),
            $status === 401 => new AuthenticationException($message, $status, $errors),
            $status === 403 => new ForbiddenException($message, $status, $errors),
            $status === 404 => new NotFoundException($message, $status, $errors),
            $status >= 500 => new ServerException($message, $status, $errors),
            default => new ApiException($message, $status, $errors),
        };
    }
}
