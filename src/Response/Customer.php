<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

final readonly class Customer
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $cpfCnpj,
        public ?string $email,
        public ?string $externalReference,
        public bool $deleted,
        public ?\DateTimeImmutable $dateCreated,
        public array $raw,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'id') ?? '',
            Fields::string($d, 'name'),
            Fields::string($d, 'cpfCnpj'),
            Fields::string($d, 'email'),
            Fields::string($d, 'externalReference'),
            Fields::bool($d, 'deleted') ?? false,
            Fields::date($d, 'dateCreated'),
            $d,
        );
    }
}
