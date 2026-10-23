<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Unit;

use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Enum\PaymentStatus;
use Kongkodesoft\Asaas\Enum\PixAutomaticFrequency;
use Kongkodesoft\Asaas\Enum\PixAutomaticStatus;
use Kongkodesoft\Asaas\Enum\SubscriptionCycle;
use Kongkodesoft\Asaas\Request\CreatePayment;
use Kongkodesoft\Asaas\Request\CreatePixAutomaticAuthorization;
use Kongkodesoft\Asaas\Request\CreateSubscription;
use Kongkodesoft\Asaas\Request\CreditCard;
use Kongkodesoft\Asaas\Request\CreditCardHolderInfo;
use Kongkodesoft\Asaas\Request\ImmediateQrCode;
use Kongkodesoft\Asaas\Tests\Support\Factory;
use Kongkodesoft\Asaas\Tests\Support\FakeClient;
use Kongkodesoft\Asaas\Tests\Support\RecordingSleeper;
use PHPUnit\Framework\TestCase;

final class ResourcesTest extends TestCase
{
    private FakeClient $client;

    protected function setUp(): void
    {
        $this->client = new FakeClient();
    }

    private function sdk(): \Kongkodesoft\Asaas\Asaas
    {
        return Factory::sdk($this->client, new RecordingSleeper());
    }

    private function card(): CreditCard
    {
        return new CreditCard('Ana', '4111111111111111', '12', '2030', '123');
    }

    private function holder(): CreditCardHolderInfo
    {
        return new CreditCardHolderInfo('Ana', 'a@a.com', '12345678909', '89223005', '277', '4738010919');
    }

    public function testSubscriptionWithCard(): void
    {
        $this->client->json(200, [
            'id' => 'sub_1', 'billingType' => 'CREDIT_CARD', 'cycle' => 'MONTHLY', 'status' => 'ACTIVE', 'value' => 29.9,
            'nextDueDate' => '2030-02-01', 'creditCard' => ['creditCardToken' => 'tok', 'creditCardNumber' => '1111', 'creditCardBrand' => 'VISA'],
        ]);
        $sub = $this->sdk()->subscriptions->create(new CreateSubscription(
            'cus_1',
            BillingType::CreditCard,
            29.9,
            new \DateTimeImmutable('2030-02-01'),
            SubscriptionCycle::Monthly,
            creditCard: $this->card(),
            creditCardHolderInfo: $this->holder(),
            remoteIp: '1.2.3.4',
        ));

        $body = json_decode((string) $this->client->requests[0]->getBody(), true);
        self::assertSame('MONTHLY', $body['cycle']);
        self::assertSame('4111111111111111', $body['creditCard']['number']);
        self::assertSame('1.2.3.4', $body['remoteIp']);
        self::assertSame('tok', $sub->creditCardToken);
        self::assertSame(SubscriptionCycle::Monthly, $sub->cycle);
    }

    public function testSubscriptionRejectsBoleto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateSubscription('c', BillingType::Boleto, 1, new \DateTimeImmutable(), SubscriptionCycle::Monthly);
    }

    public function testCardPaymentRequiresRemoteIp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreatePayment('c', BillingType::CreditCard, 1, new \DateTimeImmutable(), creditCard: $this->card(), creditCardHolderInfo: $this->holder());
    }

    public function testCardIsMaskedWhenDumped(): void
    {
        $dump = print_r($this->card(), true);
        self::assertStringNotContainsString('4111111111111111', $dump);
        self::assertStringNotContainsString('123', str_replace('1111', '', $dump));
    }

    public function testConfigHidesApiKey(): void
    {
        $dump = print_r(new \Kongkodesoft\Asaas\Config('super-secret'), true);
        self::assertStringNotContainsString('super-secret', $dump);
    }

    public function testPixQrCodeAndStatus(): void
    {
        $this->client
            ->json(200, ['encodedImage' => 'abc', 'payload' => '000201', 'expirationDate' => '2030-01-01 23:59:59'])
            ->json(200, ['status' => 'RECEIVED'])
            ->json(200, ['id' => 'pay_1', 'status' => 'CONFIRMED', 'billingType' => 'CREDIT_CARD']);

        $qr = $this->sdk()->payments->pixQrCode('pay_1');
        self::assertSame('000201', $qr->payload);
        self::assertNotNull($qr->expirationDate);
        self::assertSame(PaymentStatus::Received, $this->sdk()->payments->status('pay_1'));
        self::assertTrue($this->sdk()->payments->get('pay_1')->isPaid());
    }

    public function testUnknownEnumValuesDoNotBreak(): void
    {
        $this->client->json(200, ['id' => 'pay_1', 'status' => 'BRAND_NEW_STATUS']);
        $payment = $this->sdk()->payments->get('pay_1');
        self::assertNull($payment->status);
        self::assertSame('BRAND_NEW_STATUS', $payment->raw['status']);
    }

    public function testTokenize(): void
    {
        $this->client->json(200, ['creditCardNumber' => '1111', 'creditCardBrand' => 'VISA', 'creditCardToken' => 'tok']);
        $token = $this->sdk()->creditCards->tokenize('cus_1', $this->card(), $this->holder(), '1.2.3.4');
        self::assertSame('tok', $token->token);
        self::assertStringEndsWith('creditCard/tokenizeCreditCard', (string) $this->client->requests[0]->getUri());
        self::assertStringNotContainsString('=> tok', print_r($token, true));
    }

    public function testPixAutomaticLifecycle(): void
    {
        $this->client
            ->json(200, ['id' => 'auth_1', 'status' => 'CREATED', 'frequency' => 'MONTHLY', 'payload' => 'pix', 'encodedImage' => 'img'])
            ->json(200, ['data' => [['id' => 'ins_1', 'status' => 'REFUSED', 'authorization' => ['id' => 'auth_1']]], 'hasMore' => false, 'totalCount' => 1, 'limit' => 10, 'offset' => 0])
            ->json(200, ['id' => 'ins_1', 'status' => 'SCHEDULED'])
            ->json(200, []);
        $sdk = $this->sdk();

        $auth = $sdk->pixAutomatic->createAuthorization(new CreatePixAutomaticAuthorization(
            PixAutomaticFrequency::Monthly,
            'contract-1',
            new \DateTimeImmutable('2030-01-01'),
            'cus_1',
            new ImmediateQrCode(3600, 10.0),
        ));
        self::assertSame(PixAutomaticStatus::Created, $auth->status);
        $body = json_decode((string) $this->client->requests[0]->getBody(), true);
        self::assertSame('MONTHLY', $body['frequency']);
        self::assertEquals(['expirationSeconds' => 3600, 'originalValue' => 10.0], $body['immediateQrCode']);

        $page = $sdk->pixAutomatic->listInstructions(authorizationId: 'auth_1');
        self::assertSame('auth_1', $page->data[0]->authorizationId);
        $sdk->pixAutomatic->retryInstruction('ins_1', new \DateTimeImmutable('2030-02-01'));
        self::assertSame(['dueDate' => '2030-02-01'], json_decode((string) $this->client->requests[2]->getBody(), true));
        $sdk->pixAutomatic->cancelAuthorization('auth_1');
        self::assertSame('DELETE', $this->client->requests[3]->getMethod());
    }
}
