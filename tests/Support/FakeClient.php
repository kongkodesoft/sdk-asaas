<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** Cliente PSR-18 falso: devolve respostas enfileiradas e registra requisições. */
final class FakeClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface|\Throwable> */
    private array $queue = [];

    /** @param array<array-key, mixed> $body @param array<string, string> $headers */
    public function json(int $status, array $body = [], array $headers = []): self
    {
        $this->queue[] = new Response($status, $headers + ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function fail(\Throwable $e): self
    {
        $this->queue[] = $e;

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue) ?? throw new \LogicException('Fila de respostas vazia.');
        if ($next instanceof \Throwable) {
            throw $next;
        }

        return $next;
    }
}
