<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class ImmediateQrCode implements RequestData
{
    public function __construct(
        public int $expirationSeconds,
        public float $originalValue,
        public ?string $pixKey = null,
        public ?string $description = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
