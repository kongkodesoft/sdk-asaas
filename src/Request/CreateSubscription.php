<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Enum\SubscriptionCycle;

/**
 * Assinatura recorrente. Escopo do SDK: cartão de crédito ou Pix.
 * Com cartão informe creditCard + creditCardHolderInfo + remoteIp (ou creditCardToken + remoteIp).
 */
final readonly class CreateSubscription implements RequestData
{
    /**
     * @param list<SplitItem>|null $split
     */
    public function __construct(
        public string $customer,
        public BillingType $billingType,
        public float $value,
        public \DateTimeInterface $nextDueDate,
        public SubscriptionCycle $cycle,
        public ?string $description = null,
        public ?\DateTimeInterface $endDate = null,
        public ?int $maxPayments = null,
        public ?string $externalReference = null,
        public ?Discount $discount = null,
        public ?Interest $interest = null,
        public ?Fine $fine = null,
        public ?array $split = null,
        public ?Callback $callback = null,
        public ?CreditCard $creditCard = null,
        public ?CreditCardHolderInfo $creditCardHolderInfo = null,
        #[\SensitiveParameter]
        public ?string $creditCardToken = null,
        public ?string $remoteIp = null,
    ) {
        if ($billingType !== BillingType::CreditCard && $billingType !== BillingType::Pix) {
            throw new \InvalidArgumentException('Assinaturas suportam somente CREDIT_CARD ou PIX.');
        }
        if ($value <= 0) {
            throw new \InvalidArgumentException('O valor da assinatura deve ser positivo.');
        }
        if ($billingType === BillingType::CreditCard && ($creditCard !== null || $creditCardToken !== null)) {
            if ($remoteIp === null) {
                throw new \InvalidArgumentException('remoteIp é obrigatório para assinatura com cartão.');
            }
            if ($creditCardToken === null && $creditCardHolderInfo === null) {
                throw new \InvalidArgumentException('Informe creditCard e creditCardHolderInfo ou creditCardToken.');
            }
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
