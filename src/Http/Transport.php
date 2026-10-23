<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

interface Transport
{
    /**
     * @param array<array-key, mixed> $query parâmetros de query (enums, datas e bools são normalizados)
     * @param array<array-key, mixed>|null $json corpo JSON (nulls são omitidos; enums e datas normalizados)
     *
     * @return array<array-key, mixed> corpo decodificado; array vazio para respostas sem corpo
     *
     * @throws \Kongkodesoft\Asaas\Exception\AsaasException
     */
    public function request(string $method, string $path, array $query = [], ?array $json = null): array;
}
