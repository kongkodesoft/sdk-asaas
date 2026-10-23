<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

final readonly class DeleteResult
{
    public function __construct(public bool $deleted, public string $id)
    {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(Fields::bool($d, 'deleted') ?? false, Fields::string($d, 'id') ?? '');
    }
}
