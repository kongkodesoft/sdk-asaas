<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Sandbox;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Kongkodesoft\Asaas\Asaas;
use Kongkodesoft\Asaas\Config;
use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Enum\SubscriptionCycle;
use Kongkodesoft\Asaas\Environment;
use Kongkodesoft\Asaas\Request\CreateCustomer;
use Kongkodesoft\Asaas\Request\CreatePayment;
use Kongkodesoft\Asaas\Request\CreateSubscription;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/** Executa contra o sandbox real. Requer ASAAS_SANDBOX_KEY. */
#[Group('sandbox')]
final class SandboxTest extends TestCase
{
    private Asaas $sdk;

    protected function setUp(): void
    {
        $key = getenv('ASAAS_SANDBOX_KEY');
        if ($key === false || $key === '') {
            self::markTestSkipped('Defina ASAAS_SANDBOX_KEY para rodar os testes de sandbox.');
        }
        $this->sdk = Asaas::create(
            new Config($key, Environment::Sandbox),
            new Client(['http_errors' => false, 'timeout' => 30]),
            new HttpFactory(),
        );
    }

    public function testPixPaymentAndPixSubscription(): void
    {
        $customer = $this->sdk->customers->create(new CreateCustomer('SDK Teste', '24971563792', email: 'sdk@example.com'));
        $due = new \DateTimeImmutable('+5 days');

        $payment = $this->sdk->payments->create(new CreatePayment($customer->id, BillingType::Pix, 5.0, $due));
        self::assertSame(BillingType::Pix, $payment->billingType);
        self::assertNotSame('', $this->sdk->payments->pixQrCode($payment->id)->payload);

        $sub = $this->sdk->subscriptions->create(new CreateSubscription($customer->id, BillingType::Pix, 5.0, $due, SubscriptionCycle::Monthly));
        self::assertNotSame('', $sub->id);

        $this->sdk->subscriptions->delete($sub->id);
        $this->sdk->payments->delete($payment->id);
        $this->sdk->customers->delete($customer->id);
    }
}
