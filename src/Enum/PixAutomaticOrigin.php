<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Enum;

enum PixAutomaticOrigin: string
{
    case ImmediatePaymentAndRecurringQrCode = 'IMMEDIATE_PAYMENT_AND_RECURRING_QR_CODE';
    case PaymentAndRecurringOfferQrCode = 'PAYMENT_AND_RECURRING_OFFER_QR_CODE';
}
