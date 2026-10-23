<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixInstructionStatus: string
{
    case AwaitingRequest = 'AWAITING_REQUEST';
    case Scheduled = 'SCHEDULED';
    case Done = 'DONE';
    case Cancelled = 'CANCELLED';
    case Refused = 'REFUSED';
}
