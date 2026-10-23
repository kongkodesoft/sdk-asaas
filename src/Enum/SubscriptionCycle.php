<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum SubscriptionCycle: string
{
    case Weekly = 'WEEKLY';
    case Biweekly = 'BIWEEKLY';
    case Monthly = 'MONTHLY';
    case Bimonthly = 'BIMONTHLY';
    case Quarterly = 'QUARTERLY';
    case Semiannually = 'SEMIANNUALLY';
    case Yearly = 'YEARLY';
}
