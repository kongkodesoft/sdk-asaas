<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas;

use Kongkodesoft\Asaas\Http\RetryPolicy;

final readonly class Config
{
    public function __construct(
        #[\SensitiveParameter]
        public string $apiKey,
        public Environment $environment = Environment::Production,
        public string $userAgent = 'kongkodesoft-asaas-sdk-php',
        public RetryPolicy $retry = new RetryPolicy(),
        /** Usado somente ao construir o cliente HTTP padrão; clientes PSR-18 injetados controlam seus próprios timeouts. */
        public float $timeoutSeconds = 30.0,
        public float $connectTimeoutSeconds = 10.0,
    ) {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException('A chave de API (access_token) não pode ser vazia.');
        }
        if (trim($userAgent) === '') {
            throw new \InvalidArgumentException('O User-Agent é obrigatório pela API do Asaas.');
        }
    }

    public function baseUrl(): string
    {
        return $this->environment->value;
    }

    /** @return array<string, string> */
    public function __debugInfo(): array
    {
        return [
            'apiKey' => '***',
            'environment' => $this->environment->name,
            'userAgent' => $this->userAgent,
        ];
    }
}
