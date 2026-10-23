<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Unit;

use Kongkodesoft\Asaas\Exception\WebhookException;
use Kongkodesoft\Asaas\Webhook\WebhookReceiver;
use PHPUnit\Framework\TestCase;

final class WebhookTest extends TestCase
{
    private const BODY = '{"id":"evt_1","event":"PAYMENT_RECEIVED","dateCreated":"2030-01-01 10:00:00","payment":{"id":"pay_1","status":"RECEIVED"}}';

    public function testParsesValidEvent(): void
    {
        $event = (new WebhookReceiver('tok'))->receive(self::BODY, 'tok');
        self::assertSame('evt_1', $event->id);
        self::assertTrue($event->isPayment());
        self::assertTrue($event->is('PAYMENT_RECEIVED'));
        self::assertSame('pay_1', $event->payment?->id);
        self::assertNull($event->subscription);
    }

    public function testRejectsBadToken(): void
    {
        $this->expectException(WebhookException::class);
        (new WebhookReceiver('tok'))->receive(self::BODY, 'nope');
    }

    public function testRejectsMissingToken(): void
    {
        $this->expectException(WebhookException::class);
        (new WebhookReceiver('tok'))->receive(self::BODY, null);
    }

    public function testRejectsMalformedPayload(): void
    {
        $this->expectException(WebhookException::class);
        (new WebhookReceiver('tok'))->receive('{oops', 'tok');
    }
}
