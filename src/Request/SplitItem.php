<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class SplitItem implements RequestData
{
    public function __construct(
        public string $walletId,
        public ?float $fixedValue = null,
        public ?float $percentualValue = null,
        public ?string $externalReference = null,
        public ?string $description = null,
    ) {
        if ($fixedValue === null && $percentualValue === null) {
            throw new \InvalidArgumentException('Informe fixedValue ou percentualValue no split.');
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
