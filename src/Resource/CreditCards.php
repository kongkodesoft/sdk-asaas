<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Request\CreditCard;
use Kongkodesoft\Asaas\Request\CreditCardHolderInfo;
use Kongkodesoft\Asaas\Response\CreditCardToken;

final readonly class CreditCards extends AbstractResource
{
    /** Tokeniza um cartão para cobranças/assinaturas futuras sem reenviar os dados. */
    public function tokenize(
        string $customerId,
        CreditCard $creditCard,
        CreditCardHolderInfo $holderInfo,
        string $remoteIp,
    ): CreditCardToken {
        return CreditCardToken::fromArray($this->transport->request('POST', 'creditCard/tokenizeCreditCard', json: [
            'customer' => $customerId,
            'creditCard' => $creditCard,
            'creditCardHolderInfo' => $holderInfo,
            'remoteIp' => $remoteIp,
        ]));
    }
}
