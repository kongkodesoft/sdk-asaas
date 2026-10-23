<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

/** Informações do dispositivo para autenticação 3DS. Todos os campos são obrigatórios. */
final readonly class DeviceInfo implements RequestData
{
    public function __construct(
        public int $colorDepth,
        public int $screenHeight,
        public int $screenWidth,
        public int $timeZoneOffset,
        public string $language,
        public string $userAgent,
        public string $remoteIp,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
