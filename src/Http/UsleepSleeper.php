<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

final readonly class UsleepSleeper implements Sleeper
{
    public function sleep(float $seconds): void
    {
        if ($seconds > 0) {
            usleep((int) round($seconds * 1_000_000));
        }
    }
}
