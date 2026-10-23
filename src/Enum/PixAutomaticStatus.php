<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixAutomaticStatus: string
{
    case Created = 'CREATED';
    case Active = 'ACTIVE';
    case Cancelled = 'CANCELLED';
    case Refused = 'REFUSED';
    case Expired = 'EXPIRED';
}
