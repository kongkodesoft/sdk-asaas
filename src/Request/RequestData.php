<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

interface RequestData
{
    /** @return array<array-key, mixed> */
    public function toArray(): array;
}
