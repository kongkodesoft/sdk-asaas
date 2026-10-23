<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixInstructionPurpose: string
{
    case RetryAfterDueDate = 'RETRY_AFTER_DUE_DATE';
    case Schedule = 'SCHEDULE';
}
