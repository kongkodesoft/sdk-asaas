<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixAutomaticRetryPolicy: string
{
    case AllowThreeInSevenDays = 'ALLOW_THREE_IN_SEVEN_DAYS';
    case NotAllowed = 'NOT_ALLOWED';
}
