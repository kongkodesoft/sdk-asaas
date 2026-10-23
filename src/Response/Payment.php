<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Enum\CardBrand;
use Kongkodesoft\Asaas\Enum\PaymentStatus;

final readonly class Payment
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        public ?string $customer,
        public ?string $subscription,
        public ?string $installment,
        public ?float $value,
        public ?float $netValue,
        public ?BillingType $billingType,
        public ?PaymentStatus $status,
        public ?\DateTimeImmutable $dueDate,
        public ?\DateTimeImmutable $paymentDate,
        public ?string $invoiceUrl,
        public ?string $externalReference,
        public ?string $creditCardToken,
        public ?string $creditCardBrand,
        public ?CardBrand $cardBrand,
        public ?string $pixTransaction,
        public ?string $threeDSecureChallengeUrl,
        public bool $deleted,
        public array $raw,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        $card = Fields::map($d, 'creditCard');

        return new self(
            Fields::string($d, 'id') ?? '',
            Fields::string($d, 'customer'),
            Fields::string($d, 'subscription'),
            Fields::string($d, 'installment'),
            Fields::float($d, 'value'),
            Fields::float($d, 'netValue'),
            Fields::enum($d, 'billingType', BillingType::class),
            Fields::enum($d, 'status', PaymentStatus::class),
            Fields::date($d, 'dueDate'),
            Fields::date($d, 'paymentDate'),
            Fields::string($d, 'invoiceUrl'),
            Fields::string($d, 'externalReference'),
            Fields::string($card, 'creditCardToken'),
            Fields::string($card, 'creditCardBrand'),
            Fields::enum($card, 'creditCardBrand', CardBrand::class),
            Fields::string($d, 'pixTransaction'),
            Fields::string($d, 'threeDSecureChallengeUrl'),
            Fields::bool($d, 'deleted') ?? false,
            $d,
        );
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [PaymentStatus::Received, PaymentStatus::Confirmed, PaymentStatus::ReceivedInCash], true);
    }
}
