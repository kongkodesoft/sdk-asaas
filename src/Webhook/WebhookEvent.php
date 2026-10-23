<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Webhook;

use Kongkodesoft\Asaas\Response\Payment;
use Kongkodesoft\Asaas\Response\Subscription;

/**
 * A entrega é at-least-once: deduplique por $id antes de processar.
 */
final readonly class WebhookEvent
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        /** Ex.: PAYMENT_RECEIVED, SUBSCRIPTION_CREATED, PIX_AUTOMATIC_RECURRING_AUTHORIZATION_ACTIVATED. */
        public string $type,
        public ?\DateTimeImmutable $dateCreated,
        public ?Payment $payment,
        public ?Subscription $subscription,
        public array $raw,
    ) {
    }

    public function is(string $type): bool
    {
        return $this->type === $type;
    }

    public function isPayment(): bool
    {
        return str_starts_with($this->type, 'PAYMENT_');
    }

    public function isSubscription(): bool
    {
        return str_starts_with($this->type, 'SUBSCRIPTION_');
    }

    public function isPixAutomatic(): bool
    {
        return str_starts_with($this->type, 'PIX_AUTOMATIC_');
    }
}
