<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Http;

use Kongkodesoft\Asaas\Request\RequestData;

/** Normalização pura (sem estado) de payloads e query strings. */
final class Payload
{
    private function __construct()
    {
    }

    /**
     * Remove nulls e converte enums, datas e DTOs em valores serializáveis.
     *
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    public static function normalize(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $out[$key] = self::value($value);
        }

        return $out;
    }

    /**
     * @param array<array-key, mixed> $params
     *
     * @return array<array-key, string>
     */
    public static function query(array $params): array
    {
        $out = [];
        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }
            $normalized = self::value($value);
            if (is_array($normalized)) {
                throw new \InvalidArgumentException(sprintf('O parâmetro de query "%s" não pode ser um array.', $key));
            }
            if (is_bool($normalized)) {
                $out[$key] = $normalized ? 'true' : 'false';
            } elseif (is_scalar($normalized)) {
                $out[$key] = (string) $normalized;
            } else {
                throw new \InvalidArgumentException(sprintf('O parâmetro de query "%s" possui tipo não suportado.', $key));
            }
        }

        return $out;
    }

    private static function value(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
            $value instanceof RequestData => self::normalize($value->toArray()),
            is_array($value) => self::normalize($value),
            default => $value,
        };
    }
}
