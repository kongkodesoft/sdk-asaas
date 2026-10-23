<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

/** Leitura tipada e tolerante de campos de payloads (sem estado). */
final class Fields
{
    private function __construct()
    {
    }

    /** @param array<array-key, mixed> $d */
    public static function string(array $d, string $key): ?string
    {
        $v = $d[$key] ?? null;

        return is_scalar($v) ? (string) $v : null;
    }

    /** @param array<array-key, mixed> $d */
    public static function float(array $d, string $key): ?float
    {
        $v = $d[$key] ?? null;

        return is_numeric($v) ? (float) $v : null;
    }

    /** @param array<array-key, mixed> $d */
    public static function int(array $d, string $key): ?int
    {
        $v = $d[$key] ?? null;

        return is_numeric($v) ? (int) $v : null;
    }

    /** @param array<array-key, mixed> $d */
    public static function bool(array $d, string $key): ?bool
    {
        $v = $d[$key] ?? null;

        return is_bool($v) ? $v : null;
    }

    /** @param array<array-key, mixed> $d */
    public static function date(array $d, string $key): ?\DateTimeImmutable
    {
        $v = $d[$key] ?? null;
        if (!is_string($v) || $v === '') {
            return null;
        }
        try {
            return new \DateTimeImmutable($v);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Valores desconhecidos viram null (compatibilidade com novos valores da API); o original fica em $raw.
     *
     * @template T of \BackedEnum
     *
     * @param array<array-key, mixed> $d
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public static function enum(array $d, string $key, string $enum): ?\BackedEnum
    {
        $v = $d[$key] ?? null;

        return is_string($v) || is_int($v) ? $enum::tryFrom($v) : null;
    }

    /**
     * @param array<array-key, mixed> $d
     *
     * @return array<array-key, mixed>
     */
    public static function map(array $d, string $key): array
    {
        $v = $d[$key] ?? null;

        return is_array($v) ? $v : [];
    }
}
