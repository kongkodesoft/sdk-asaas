<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class CreditCard implements RequestData
{
    public function __construct(
        public string $holderName,
        #[\SensitiveParameter]
        public string $number,
        public string $expiryMonth,
        public string $expiryYear,
        #[\SensitiveParameter]
        public string $ccv,
    ) {
    }

    public function toArray(): array
    {
        return [
            'holderName' => $this->holderName,
            'number' => $this->number,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'ccv' => $this->ccv,
        ];
    }

    /** @return array<string, string> */
    public function __debugInfo(): array
    {
        return [
            'holderName' => $this->holderName,
            'number' => '**** **** **** ' . substr($this->number, -4),
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'ccv' => '***',
        ];
    }
}
