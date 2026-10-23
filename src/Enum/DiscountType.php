<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum DiscountType: string
{
    case Fixed = 'FIXED';
    case Percentage = 'PERCENTAGE';
}
