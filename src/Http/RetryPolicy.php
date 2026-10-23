<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

/** Política de retry imutável. Veja HttpTransport para as regras de quando aplicar. */
final readonly class RetryPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public float $baseDelaySeconds = 0.5,
        public float $maxDelaySeconds = 30.0,
    ) {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('maxAttempts deve ser >= 1.');
        }
        if ($baseDelaySeconds < 0 || $maxDelaySeconds < 0) {
            throw new \InvalidArgumentException('Os atrasos não podem ser negativos.');
        }
    }

    public static function none(): self
    {
        return new self(maxAttempts: 1);
    }
}
