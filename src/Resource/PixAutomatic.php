<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Enum\PixAutomaticStatus;
use Kongkodesoft\Asaas\Enum\PixInstructionStatus;
use Kongkodesoft\Asaas\Request\CreatePixAutomaticAuthorization;
use Kongkodesoft\Asaas\Response\Page;
use Kongkodesoft\Asaas\Response\PixAutomaticAuthorization;
use Kongkodesoft\Asaas\Response\PixPaymentInstruction;

/** Pix Automático: recorrência de recebimento via Pix, mediante autorização do pagador. */
final readonly class PixAutomatic extends AbstractResource
{
    public function createAuthorization(CreatePixAutomaticAuthorization $request): PixAutomaticAuthorization
    {
        return PixAutomaticAuthorization::fromArray(
            $this->transport->request('POST', 'pix/automatic/authorizations', json: $request->toArray()),
        );
    }

    public function getAuthorization(string $id): PixAutomaticAuthorization
    {
        return PixAutomaticAuthorization::fromArray(
            $this->transport->request('GET', 'pix/automatic/authorizations/' . self::id($id)),
        );
    }

    /** Cancela a autorização. */
    public function cancelAuthorization(string $id): void
    {
        $this->transport->request('DELETE', 'pix/automatic/authorizations/' . self::id($id));
    }

    /** @return Page<PixAutomaticAuthorization> */
    public function listAuthorizations(
        ?PixAutomaticStatus $status = null,
        ?string $customerId = null,
        int $offset = 0,
        int $limit = 10,
    ): Page {
        return $this->page(
            'pix/automatic/authorizations',
            ['status' => $status, 'customerId' => $customerId],
            $offset,
            $limit,
            PixAutomaticAuthorization::fromArray(...),
        );
    }

    public function getInstruction(string $id): PixPaymentInstruction
    {
        return PixPaymentInstruction::fromArray(
            $this->transport->request('GET', 'pix/automatic/paymentInstructions/' . self::id($id)),
        );
    }

    /** @return Page<PixPaymentInstruction> */
    public function listInstructions(
        ?string $authorizationId = null,
        ?string $customerId = null,
        ?string $paymentId = null,
        ?PixInstructionStatus $status = null,
        int $offset = 0,
        int $limit = 10,
    ): Page {
        return $this->page(
            'pix/automatic/paymentInstructions',
            ['authorizationId' => $authorizationId, 'customerId' => $customerId, 'paymentId' => $paymentId, 'status' => $status],
            $offset,
            $limit,
            PixPaymentInstruction::fromArray(...),
        );
    }

    /** Reagenda uma instrução recusada para nova data de vencimento. */
    public function retryInstruction(string $id, \DateTimeInterface $dueDate): PixPaymentInstruction
    {
        return PixPaymentInstruction::fromArray(
            $this->transport->request('POST', 'pix/automatic/paymentInstructions/' . self::id($id) . '/retries', json: ['dueDate' => $dueDate]),
        );
    }
}
