<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Support;

use Kongkodesoft\Asaas\Http\Sleeper;

final class RecordingSleeper implements Sleeper
{
    /** @var list<float> */
    public array $delays = [];

    public function sleep(float $seconds): void
    {
        $this->delays[] = $seconds;
    }
}
