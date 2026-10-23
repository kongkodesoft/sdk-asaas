<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Unit;

use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Exception\AuthenticationException;
use Kongkodesoft\Asaas\Exception\NetworkException;
use Kongkodesoft\Asaas\Exception\NotFoundException;
use Kongkodesoft\Asaas\Exception\RateLimitException;
use Kongkodesoft\Asaas\Exception\ServerException;
use Kongkodesoft\Asaas\Exception\ValidationException;
use Kongkodesoft\Asaas\Request\CreatePayment;
use Kongkodesoft\Asaas\Tests\Support\Factory;
use Kongkodesoft\Asaas\Tests\Support\FakeClient;
use Kongkodesoft\Asaas\Tests\Support\NetworkError;
use Kongkodesoft\Asaas\Tests\Support\RecordingSleeper;
use PHPUnit\Framework\TestCase;

final class TransportTest extends TestCase
{
    private FakeClient $client;
    private RecordingSleeper $sleeper;

    protected function setUp(): void
    {
        $this->client = new FakeClient();
        $this->sleeper = new RecordingSleeper();
    }

    public function testSendsHeadersAndNoBodyOnGet(): void
    {
        $this->client->json(200, ['id' => 'cus_1', 'name' => 'Ana']);
        $customer = Factory::sdk($this->client, $this->sleeper)->customers->get('cus_1');

        $req = $this->client->requests[0];
        self::assertSame('cus_1', $customer->id);
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api-sandbox.asaas.com/v3/customers/cus_1', (string) $req->getUri());
        self::assertSame('secret-key', $req->getHeaderLine('access_token'));
        self::assertNotSame('', $req->getHeaderLine('User-Agent'));
        self::assertSame('', (string) $req->getBody());
    }

    public function testPostSerializesBodyDroppingNulls(): void
    {
        $this->client->json(200, ['id' => 'pay_1', 'status' => 'PENDING', 'billingType' => 'PIX']);
        $payment = Factory::sdk($this->client, $this->sleeper)->payments->create(
            new CreatePayment('cus_1', BillingType::Pix, 10.5, new \DateTimeImmutable('2030-01-31')),
        );

        $req = $this->client->requests[0];
        self::assertSame(
            ['customer' => 'cus_1', 'billingType' => 'PIX', 'value' => 10.5, 'dueDate' => '2030-01-31'],
            json_decode((string) $req->getBody(), true),
        );
        self::assertSame('application/json', $req->getHeaderLine('Content-Type'));
        self::assertSame('pay_1', $payment->id);
    }

    public function testQueryNormalization(): void
    {
        $this->client->json(200, ['data' => [], 'hasMore' => false, 'totalCount' => 0, 'limit' => 5, 'offset' => 0]);
        Factory::sdk($this->client, $this->sleeper)->payments->list(
            ['status' => \Kongkodesoft\Asaas\Enum\PaymentStatus::Pending, 'dueDate[ge]' => new \DateTimeImmutable('2030-01-01'), 'x' => null],
            0,
            5,
        );
        $query = (string) $this->client->requests[0]->getUri()->getQuery();
        self::assertStringContainsString('status=PENDING', $query);
        self::assertStringContainsString('dueDate%5Bge%5D=2030-01-01', $query);
        self::assertStringContainsString('limit=5', $query);
        self::assertStringNotContainsString('x=', $query);
    }

    public function testErrorMapping(): void
    {
        $sdk = Factory::sdk($this->client, $this->sleeper, 1);
        $err = ['errors' => [['code' => 'invalid_action', 'description' => 'Boom']]];

        foreach ([[400, ValidationException::class], [401, AuthenticationException::class], [404, NotFoundException::class], [500, ServerException::class]] as [$status, $class]) {
            $this->client->json($status, $err);
            try {
                $sdk->customers->get('x');
                self::fail('exceção esperada');
            } catch (\Kongkodesoft\Asaas\Exception\ApiException $e) {
                self::assertInstanceOf($class, $e);
                self::assertSame('Boom', $e->getMessage());
                self::assertSame(['invalid_action'], $e->errorCodes());
            }
        }
    }

    public function testRetriesRateLimitUsingResetHeaderEvenOnPost(): void
    {
        $this->client->json(429, [], ['RateLimit-Reset' => '3'])->json(200, ['id' => 'pay_1']);
        $sdk = Factory::sdk($this->client, $this->sleeper);
        $sdk->payments->create(new CreatePayment('c', BillingType::Pix, 1, new \DateTimeImmutable('2030-01-01')));

        self::assertCount(2, $this->client->requests);
        self::assertSame([3.0], $this->sleeper->delays);
    }

    public function testRateLimitExhaustedThrows(): void
    {
        $this->client->json(429, [], ['RateLimit-Reset' => '7']);
        try {
            Factory::sdk($this->client, $this->sleeper, 1)->customers->get('x');
            self::fail();
        } catch (RateLimitException $e) {
            self::assertSame(7, $e->resetSeconds);
        }
    }

    public function testGetRetriesOnServerErrorAndNetwork(): void
    {
        $this->client->json(503)->fail(new NetworkError('x'))->json(200, ['id' => 'cus_1']);
        $customer = Factory::sdk($this->client, $this->sleeper)->customers->get('cus_1');

        self::assertSame('cus_1', $customer->id);
        self::assertCount(3, $this->client->requests);
        self::assertCount(2, $this->sleeper->delays);
    }

    public function testPostIsNeverRetriedOnServerOrNetworkFailure(): void
    {
        $sdk = Factory::sdk($this->client, $this->sleeper);
        $payment = new CreatePayment('c', BillingType::Pix, 1, new \DateTimeImmutable('2030-01-01'));

        $this->client->json(500);
        try {
            $sdk->payments->create($payment);
            self::fail();
        } catch (ServerException) {
        }
        $this->client->fail(new NetworkError('down'));
        try {
            $sdk->payments->create($payment);
            self::fail();
        } catch (NetworkException) {
        }
        self::assertCount(2, $this->client->requests);
        self::assertSame([], $this->sleeper->delays);
    }

    public function testIteratePaginates(): void
    {
        $this->client
            ->json(200, ['data' => [['id' => 'a'], ['id' => 'b']], 'hasMore' => true, 'totalCount' => 3, 'limit' => 2, 'offset' => 0])
            ->json(200, ['data' => [['id' => 'c']], 'hasMore' => false, 'totalCount' => 3, 'limit' => 2, 'offset' => 2]);

        $ids = [];
        foreach (Factory::sdk($this->client, $this->sleeper)->customers->iterate([], 2) as $c) {
            $ids[] = $c->id;
        }
        self::assertSame(['a', 'b', 'c'], $ids);
        self::assertStringContainsString('offset=2', $this->client->requests[1]->getUri()->getQuery());
    }

    public function testIdsAreUrlEncoded(): void
    {
        $this->client->json(200, ['id' => 'x']);
        Factory::sdk($this->client, $this->sleeper)->customers->get('a/b');
        self::assertStringEndsWith('customers/a%2Fb', (string) $this->client->requests[0]->getUri());
    }
}
