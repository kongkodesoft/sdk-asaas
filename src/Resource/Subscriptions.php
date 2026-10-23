<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Enum\PaymentStatus;
use Kongkodesoft\Asaas\Request\CreateSubscription;
use Kongkodesoft\Asaas\Request\CreditCard;
use Kongkodesoft\Asaas\Request\CreditCardHolderInfo;
use Kongkodesoft\Asaas\Response\DeleteResult;
use Kongkodesoft\Asaas\Response\Page;
use Kongkodesoft\Asaas\Response\Payment;
use Kongkodesoft\Asaas\Response\Subscription;

final readonly class Subscriptions extends AbstractResource
{
    public function create(CreateSubscription $request): Subscription
    {
        return Subscription::fromArray($this->transport->request('POST', 'subscriptions', json: $request->toArray()));
    }

    public function get(string $id): Subscription
    {
        return Subscription::fromArray($this->transport->request('GET', 'subscriptions/' . self::id($id)));
    }

    /**
     * @param array<array-key, mixed> $fields billingType, status, nextDueDate, value, cycle, description, endDate,
     *                                      updatePendingPayments, externalReference, discount, interest, fine, split, callback
     */
    public function update(string $id, array $fields): Subscription
    {
        return Subscription::fromArray($this->transport->request('PUT', 'subscriptions/' . self::id($id), json: $fields));
    }

    public function delete(string $id): DeleteResult
    {
        return DeleteResult::fromArray($this->transport->request('DELETE', 'subscriptions/' . self::id($id)));
    }

    /** Troca o cartão da assinatura (não cobra na hora). */
    public function updateCreditCard(
        string $id,
        ?CreditCard $creditCard = null,
        ?CreditCardHolderInfo $holderInfo = null,
        #[\SensitiveParameter]
        ?string $creditCardToken = null,
        ?string $remoteIp = null,
    ): Subscription {
        if ($remoteIp === null) {
            throw new \InvalidArgumentException('remoteIp é obrigatório.');
        }
        if ($creditCardToken === null && ($creditCard === null || $holderInfo === null)) {
            throw new \InvalidArgumentException('Informe creditCard e holderInfo ou creditCardToken.');
        }

        return Subscription::fromArray($this->transport->request('POST', 'subscriptions/' . self::id($id) . '/updateCreditCard', json: [
            'creditCard' => $creditCard,
            'creditCardHolderInfo' => $holderInfo,
            'creditCardToken' => $creditCardToken,
            'remoteIp' => $remoteIp,
        ]));
    }

    /**
     * @param array<array-key, mixed> $filters customer, billingType, status, externalReference, includeDeleted...
     *
     * @return Page<Subscription>
     */
    public function list(array $filters = [], int $offset = 0, int $limit = 10): Page
    {
        return $this->page('subscriptions', $filters, $offset, $limit, Subscription::fromArray(...));
    }

    /**
     * @param array<array-key, mixed> $filters
     *
     * @return \Generator<int, Subscription>
     */
    public function iterate(array $filters = [], int $limit = 100): \Generator
    {
        return $this->iterateAll('subscriptions', $filters, $limit, Subscription::fromArray(...));
    }

    /** @return Page<Payment> */
    public function payments(string $id, ?PaymentStatus $status = null, int $offset = 0, int $limit = 10): Page
    {
        return $this->page('subscriptions/' . self::id($id) . '/payments', ['status' => $status], $offset, $limit, Payment::fromArray(...));
    }
}
