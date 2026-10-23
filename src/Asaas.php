<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas;

use Kongkodesoft\Asaas\Http\HttpTransport;
use Kongkodesoft\Asaas\Http\Sleeper;
use Kongkodesoft\Asaas\Http\Transport;
use Kongkodesoft\Asaas\Http\UsleepSleeper;
use Kongkodesoft\Asaas\Resource\CreditCards;
use Kongkodesoft\Asaas\Resource\Customers;
use Kongkodesoft\Asaas\Resource\Payments;
use Kongkodesoft\Asaas\Resource\PixAutomatic;
use Kongkodesoft\Asaas\Resource\Subscriptions;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Fachada do SDK. Imutável e sem estado: uma instância pode ser compartilhada entre coroutines Swoole.
 */
final readonly class Asaas
{
    public Customers $customers;
    public Payments $payments;
    public Subscriptions $subscriptions;
    public CreditCards $creditCards;
    public PixAutomatic $pixAutomatic;

    public function __construct(Transport $transport)
    {
        $this->customers = new Customers($transport);
        $this->payments = new Payments($transport);
        $this->subscriptions = new Subscriptions($transport);
        $this->creditCards = new CreditCards($transport);
        $this->pixAutomatic = new PixAutomatic($transport);
    }

    /**
     * @param RequestFactoryInterface&StreamFactoryInterface $factory ex.: GuzzleHttp\Psr7\HttpFactory
     */
    public static function create(
        Config $config,
        ClientInterface $client,
        RequestFactoryInterface&StreamFactoryInterface $factory,
        Sleeper $sleeper = new UsleepSleeper(),
    ): self {
        return new self(new HttpTransport($config, $client, $factory, $factory, $sleeper));
    }
}
