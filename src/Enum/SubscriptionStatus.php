<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum SubscriptionStatus: string
{
    case Active = 'ACTIVE';
    case Expired = 'EXPIRED';
    case Inactive = 'INACTIVE';
}
