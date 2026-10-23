<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

use Kongkodesoft\Asaas\Enum\PixAutomaticCreationMode;
use Kongkodesoft\Asaas\Enum\PixAutomaticFrequency;
use Kongkodesoft\Asaas\Enum\PixAutomaticOrigin;
use Kongkodesoft\Asaas\Enum\PixAutomaticRetryPolicy;
use Kongkodesoft\Asaas\Enum\PixAutomaticStatus;

final readonly class PixAutomaticAuthorization
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        public ?PixAutomaticStatus $status,
        public ?PixAutomaticFrequency $frequency,
        public ?string $contractId,
        public ?string $customerId,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $finishDate,
        public ?float $value,
        public ?float $minLimitValue,
        /** Pix copia e cola do QR Code de autorização. */
        public ?string $payload,
        public ?string $encodedImage,
        public ?string $subscriptionId,
        public ?PixAutomaticOrigin $originType,
        public ?PixAutomaticCreationMode $paymentCreationMode,
        public ?PixAutomaticRetryPolicy $retryPolicy,
        public array $raw,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'id') ?? '',
            Fields::enum($d, 'status', PixAutomaticStatus::class),
            Fields::enum($d, 'frequency', PixAutomaticFrequency::class),
            Fields::string($d, 'contractId'),
            Fields::string($d, 'customerId'),
            Fields::date($d, 'startDate'),
            Fields::date($d, 'finishDate'),
            Fields::float($d, 'value'),
            Fields::float($d, 'minLimitValue'),
            Fields::string($d, 'payload'),
            Fields::string($d, 'encodedImage'),
            Fields::string($d, 'subscriptionId'),
            Fields::enum($d, 'originType', PixAutomaticOrigin::class),
            Fields::enum($d, 'paymentCreationMode', PixAutomaticCreationMode::class),
            Fields::enum($d, 'retryPolicy', PixAutomaticRetryPolicy::class),
            $d,
        );
    }
}
