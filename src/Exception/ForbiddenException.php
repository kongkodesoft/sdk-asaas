<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Exception;

/** HTTP 403: acesso negado (permissão, IP não autorizado, bloqueio temporário). */
final class ForbiddenException extends ApiException
{
}
