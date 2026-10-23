<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

use Kongkodesoft\Asaas\Enum\BillingType;

/**
 * Cobrança. Para pagamento instantâneo com cartão informe creditCard + creditCardHolderInfo + remoteIp
 * (ou creditCardToken + remoteIp). Para Pix use BillingType::Pix e depois Payments::pixQrCode().
 */
final readonly class CreatePayment implements RequestData
{
    /**
     * @param list<SplitItem>|null $split
     */
    public function __construct(
        public string $customer,
        public BillingType $billingType,
        public float $value,
        public \DateTimeInterface $dueDate,
        public ?string $description = null,
        public ?int $daysAfterDueDateToRegistrationCancellation = null,
        public ?string $externalReference = null,
        public ?int $installmentCount = null,
        public ?float $totalValue = null,
        public ?float $installmentValue = null,
        public ?Discount $discount = null,
        public ?Interest $interest = null,
        public ?Fine $fine = null,
        public ?array $split = null,
        public ?Callback $callback = null,
        public ?CreditCard $creditCard = null,
        public ?CreditCardHolderInfo $creditCardHolderInfo = null,
        #[\SensitiveParameter]
        public ?string $creditCardToken = null,
        public ?bool $authorizeOnly = null,
        public ?string $remoteIp = null,
        public ?ThreeDSecure $threeDSecure = null,
        public ?DeviceInfo $deviceInfo = null,
        public ?string $pixAutomaticAuthorizationId = null,
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('O valor da cobrança deve ser positivo.');
        }
        if ($billingType === BillingType::CreditCard
            && ($creditCard !== null || $creditCardToken !== null)
        ) {
            if ($remoteIp === null) {
                throw new \InvalidArgumentException('remoteIp é obrigatório para cobrança com cartão.');
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
