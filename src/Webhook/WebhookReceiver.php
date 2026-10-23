<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Webhook;

use Kongkodesoft\Asaas\Exception\WebhookException;
use Kongkodesoft\Asaas\Response\Fields;
use Kongkodesoft\Asaas\Response\Payment;
use Kongkodesoft\Asaas\Response\Subscription;

final readonly class WebhookReceiver
{
    public function __construct(
        #[\SensitiveParameter]
        private string $authToken,
    ) {
        if ($authToken === '') {
            throw new \InvalidArgumentException('authToken não pode ser vazio; configure um token no webhook do Asaas.');
        }
    }

    /**
     * Valida o header `asaas-access-token` (tempo constante) e converte o corpo.
     * Responda HTTP 200 rapidamente e processe de forma assíncrona.
     *
     * @throws WebhookException
     */
    public function receive(string $body, ?string $accessTokenHeader): WebhookEvent
    {
        if ($accessTokenHeader === null || !hash_equals($this->authToken, $accessTokenHeader)) {
            throw new WebhookException('Token de webhook inválido.');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new WebhookException('Payload de webhook inválido.', 0, $e);
        }
        if (!is_array($data)) {
            throw new WebhookException('Payload de webhook inválido.');
        }

        $type = Fields::string($data, 'event');
        if ($type === null || $type === '') {
            throw new WebhookException('Webhook sem o campo "event".');
        }

        $payment = Fields::map($data, 'payment');
        $subscription = Fields::map($data, 'subscription');

        return new WebhookEvent(
            Fields::string($data, 'id') ?? '',
            $type,
            Fields::date($data, 'dateCreated'),
            $payment === [] ? null : Payment::fromArray($payment),
            $subscription === [] ? null : Subscription::fromArray($subscription),
            $data,
        );
    }
}
