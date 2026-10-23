<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Enum\SubscriptionCycle;
use Kongkodesoft\Asaas\Enum\SubscriptionStatus;

final readonly class Subscription
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        public ?string $customer,
        public ?BillingType $billingType,
        public ?SubscriptionCycle $cycle,
        public ?float $value,
        public ?\DateTimeImmutable $nextDueDate,
        public ?\DateTimeImmutable $endDate,
        public ?SubscriptionStatus $status,
        public ?string $description,
        public ?string $externalReference,
        public ?int $maxPayments,
        public ?string $creditCardToken,
        public bool $deleted,
        public array $raw,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'id') ?? '',
            Fields::string($d, 'customer'),
            Fields::enum($d, 'billingType', BillingType::class),
            Fields::enum($d, 'cycle', SubscriptionCycle::class),
            Fields::float($d, 'value'),
            Fields::date($d, 'nextDueDate'),
            Fields::date($d, 'endDate'),
            Fields::enum($d, 'status', SubscriptionStatus::class),
            Fields::string($d, 'description'),
            Fields::string($d, 'externalReference'),
            Fields::int($d, 'maxPayments'),
            Fields::string(Fields::map($d, 'creditCard'), 'creditCardToken'),
            Fields::bool($d, 'deleted') ?? false,
            $d,
        );
    }
}
