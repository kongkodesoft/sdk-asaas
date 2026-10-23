<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

use Kongkodesoft\Asaas\Enum\FineType;

final readonly class Fine implements RequestData
{
    public function __construct(
        public float $value,
        public ?FineType $type = null,
    ) {
    }

    public function toArray(): array
    {
        return ['value' => $this->value, 'type' => $this->type];
    }
}
