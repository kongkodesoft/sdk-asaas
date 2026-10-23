<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Enum\PaymentStatus;
use Kongkodesoft\Asaas\Request\CreatePayment;
use Kongkodesoft\Asaas\Request\CreditCard;
use Kongkodesoft\Asaas\Request\CreditCardHolderInfo;
use Kongkodesoft\Asaas\Request\DeviceInfo;
use Kongkodesoft\Asaas\Request\ThreeDSecure;
use Kongkodesoft\Asaas\Response\DeleteResult;
use Kongkodesoft\Asaas\Response\Page;
use Kongkodesoft\Asaas\Response\Payment;
use Kongkodesoft\Asaas\Response\PixQrCode;

final readonly class Payments extends AbstractResource
{
    /** Cria a cobrança (Pix, boleto, cartão). Com dados de cartão a cobrança é capturada imediatamente. */
    public function create(CreatePayment $request): Payment
    {
        return Payment::fromArray($this->transport->request('POST', 'payments', json: $request->toArray()));
    }

    public function get(string $id): Payment
    {
        return Payment::fromArray($this->transport->request('GET', 'payments/' . self::id($id)));
    }

    /** @param array<array-key, mixed> $fields */
    public function update(string $id, array $fields): Payment
    {
        return Payment::fromArray($this->transport->request('PUT', 'payments/' . self::id($id), json: $fields));
    }

    public function delete(string $id): DeleteResult
    {
        return DeleteResult::fromArray($this->transport->request('DELETE', 'payments/' . self::id($id)));
    }

    public function restore(string $id): Payment
    {
        return Payment::fromArray($this->transport->request('POST', 'payments/' . self::id($id) . '/restore'));
    }

    public function status(string $id): ?PaymentStatus
    {
        $status = $this->transport->request('GET', 'payments/' . self::id($id) . '/status')['status'] ?? null;

        return is_string($status) ? PaymentStatus::tryFrom($status) : null;
    }

    /** QR Code Pix estático/dinâmico da cobrança (billingType PIX). */
    public function pixQrCode(string $id): PixQrCode
    {
        return PixQrCode::fromArray($this->transport->request('GET', 'payments/' . self::id($id) . '/pixQrCode'));
    }

    public function payWithCreditCard(
        string $id,
        ?CreditCard $creditCard = null,
        ?CreditCardHolderInfo $holderInfo = null,
        #[\SensitiveParameter]
        ?string $creditCardToken = null,
        ?string $remoteIp = null,
        ?ThreeDSecure $threeDSecure = null,
        ?DeviceInfo $deviceInfo = null,
    ): Payment {
        if ($creditCardToken === null && ($creditCard === null || $holderInfo === null)) {
            throw new \InvalidArgumentException('Informe creditCard e holderInfo ou creditCardToken.');
        }

        return Payment::fromArray($this->transport->request('POST', 'payments/' . self::id($id) . '/payWithCreditCard', json: [
            'creditCard' => $creditCard,
            'creditCardHolderInfo' => $holderInfo,
            'creditCardToken' => $creditCardToken,
            'remoteIp' => $remoteIp,
            'threeDSecure' => $threeDSecure,
            'deviceInfo' => $deviceInfo,
        ]));
    }

    /** Captura uma cobrança pré-autorizada (authorizeOnly). */
    public function captureAuthorized(string $id): Payment
    {
        return Payment::fromArray($this->transport->request('POST', 'payments/' . self::id($id) . '/captureAuthorizedPayment'));
    }

    /**
     * @param list<array{id: string, value: float}>|null $splitRefunds
     */
    public function refund(string $id, ?float $value = null, ?string $description = null, ?array $splitRefunds = null): Payment
    {
        return Payment::fromArray($this->transport->request('POST', 'payments/' . self::id($id) . '/refund', json: [
            'value' => $value,
            'description' => $description,
            'splitRefunds' => $splitRefunds,
        ]));
    }

    /**
     * @param array<array-key, mixed> $filters customer, billingType, status, subscription, installment, externalReference,
     *                                       dateCreated[ge], dueDate[le], paymentDate[ge] etc.
     *
     * @return Page<Payment>
     */
    public function list(array $filters = [], int $offset = 0, int $limit = 10): Page
    {
        return $this->page('payments', $filters, $offset, $limit, Payment::fromArray(...));
    }

    /**
     * @param array<array-key, mixed> $filters
     *
     * @return \Generator<int, Payment>
     */
    public function iterate(array $filters = [], int $limit = 100): \Generator
    {
        return $this->iterateAll('payments', $filters, $limit, Payment::fromArray(...));
    }
}
