<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class Interest implements RequestData
{
    public function __construct(public float $value)
    {
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
