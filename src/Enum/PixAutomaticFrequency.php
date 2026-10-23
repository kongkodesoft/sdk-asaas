<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixAutomaticFrequency: string
{
    case Weekly = 'WEEKLY';
    case Monthly = 'MONTHLY';
    case Quarterly = 'QUARTERLY';
    case Semiannually = 'SEMIANNUALLY';
    case Annually = 'ANNUALLY';
}
