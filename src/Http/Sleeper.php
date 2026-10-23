<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

/**
 * Pausa entre tentativas. Em Swoole, use uma implementação que chame
 * Swoole\Coroutine::sleep() (ou habilite SWOOLE_HOOK_SLEEP para o padrão ceder a coroutine).
 */
interface Sleeper
{
    public function sleep(float $seconds): void;
}
