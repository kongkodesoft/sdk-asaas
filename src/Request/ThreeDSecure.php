<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class ThreeDSecure implements RequestData
{
    public function __construct(public string $callbackUrl)
    {
    }

    public function toArray(): array
    {
        return ['callbackUrl' => $this->callbackUrl];
    }
}
