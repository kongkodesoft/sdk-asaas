<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

use Kongkodesoft\Asaas\Enum\DiscountType;

final readonly class Discount implements RequestData
{
    public function __construct(
        public float $value,
        public ?int $dueDateLimitDays = null,
        public ?DiscountType $type = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'dueDateLimitDays' => $this->dueDateLimitDays,
            'type' => $this->type,
        ];
    }
}
