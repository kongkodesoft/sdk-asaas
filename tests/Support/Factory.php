<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Support;

use GuzzleHttp\Psr7\HttpFactory;
use Kongkodesoft\Asaas\Asaas;
use Kongkodesoft\Asaas\Config;
use Kongkodesoft\Asaas\Environment;
use Kongkodesoft\Asaas\Http\RetryPolicy;

final class Factory
{
    public static function sdk(FakeClient $client, RecordingSleeper $sleeper, int $attempts = 3): Asaas
    {
        return Asaas::create(
            new Config('secret-key', Environment::Sandbox, retry: new RetryPolicy($attempts, 0.1, 5.0)),
            $client,
            new HttpFactory(),
            $sleeper,
        );
    }
}
