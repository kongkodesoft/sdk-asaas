<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

use Kongkodesoft\Asaas\Enum\PixAutomaticCreationMode;
use Kongkodesoft\Asaas\Enum\PixAutomaticFrequency;
use Kongkodesoft\Asaas\Enum\PixAutomaticRetryPolicy;

final readonly class CreatePixAutomaticAuthorization implements RequestData
{
    public function __construct(
        public PixAutomaticFrequency $frequency,
        public string $contractId,
        public \DateTimeInterface $startDate,
        public string $customerId,
        public ImmediateQrCode $immediateQrCode,
        public ?\DateTimeInterface $finishDate = null,
        public ?float $value = null,
        public ?string $description = null,
        public ?float $minLimitValue = null,
        public ?PixAutomaticCreationMode $paymentCreationMode = null,
        public ?PixAutomaticRetryPolicy $retryPolicy = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
