<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas;

enum Environment: string
{
    case Production = 'https://api.asaas.com/v3';
    case Sandbox = 'https://api-sandbox.asaas.com/v3';
}
