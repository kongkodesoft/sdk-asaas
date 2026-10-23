<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class Callback implements RequestData
{
    public function __construct(
        public string $successUrl,
        public bool $autoRedirect = true,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
