<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

use Kongkodesoft\Asaas\Enum\CardBrand;

final readonly class CreditCardToken
{
    public function __construct(
        #[\SensitiveParameter]
        public string $token,
        public string $lastDigits,
        public ?CardBrand $brand,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'creditCardToken') ?? '',
            Fields::string($d, 'creditCardNumber') ?? '',
            Fields::enum($d, 'creditCardBrand', CardBrand::class),
        );
    }

    /** @return array<string, string> */
    public function __debugInfo(): array
    {
        return ['token' => '***', 'lastDigits' => $this->lastDigits, 'brand' => $this->brand === null ? '' : $this->brand->value];
    }
}
