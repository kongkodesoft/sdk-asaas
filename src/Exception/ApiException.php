<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Exception;

/** Resposta HTTP de erro da API (4xx/5xx) ou corpo inesperado. */
class ApiException extends AsaasException
{
    /**
     * @param list<array{code: string, description: string}> $errors
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $errors = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /** @return list<string> */
    public function errorCodes(): array
    {
        return array_map(static fn (array $e): string => $e['code'], $this->errors);
    }
}
