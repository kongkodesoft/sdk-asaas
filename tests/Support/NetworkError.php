<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Support;

use Psr\Http\Client\ClientExceptionInterface;

final class NetworkError extends \RuntimeException implements ClientExceptionInterface
{
}
