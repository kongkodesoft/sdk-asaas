<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Exception;

/** HTTP 429: rate limit, cota de 12h ou concorrência de GET. */
final class RateLimitException extends ApiException
{
    /**
     * @param list<array{code: string, description: string}> $errors
     */
    public function __construct(
        string $message,
        array $errors = [],
        public readonly ?int $resetSeconds = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $errors, $previous);
    }
}
