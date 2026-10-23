# Asaas SDK para PHP

SDK para a [API v3 do Asaas](https://docs.asaas.com/reference/about-this-doc), com foco em **recorrência** (assinaturas com cartão de crédito e **Pix Automático**) e **pagamentos instantâneos** (Pix e cartão). PHP 8.5+, PSR-18/PSR-17, **seguro para coroutines Swoole**.

> **Versão 2.0 — reescrita com quebra de compatibilidade.** O namespace continua `Kongkodesoft\Asaas`, mas a API é nova (veja [Migração](#migração-da-v1)).

## Instalação

```bash
composer require kongkodesoft/asaas-sdk guzzlehttp/guzzle
```

O SDK não impõe cliente HTTP: qualquer implementação PSR-18 + PSR-17 serve (Guzzle é apenas a sugestão).

## Configuração

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Kongkodesoft\Asaas\{Asaas, Config, Environment};

$asaas = Asaas::create(
    new Config(getenv('ASAAS_API_KEY'), Environment::Sandbox), // ou Environment::Production
    new Client(['http_errors' => false, 'timeout' => 30]),
    new HttpFactory(),
);
```

- Use `http_errors => false` no Guzzle: o SDK converte os status HTTP em exceções tipadas.
- O `User-Agent` é enviado automaticamente (obrigatório para contas criadas desde 13/06/2024); personalize via `Config::$userAgent`.
- Chaves de API nunca aparecem em `var_dump`/logs (`__debugInfo` e `#[\SensitiveParameter]`).

## Clientes

```php
use Kongkodesoft\Asaas\Request\CreateCustomer;

$customer = $asaas->customers->create(new CreateCustomer('Maria Silva', '24971563792', email: 'maria@example.com'));
$asaas->customers->update($customer->id, ['phone' => '4738010919']);
```

## Pagamentos instantâneos

### Pix

```php
use Kongkodesoft\Asaas\Enum\BillingType;
use Kongkodesoft\Asaas\Request\CreatePayment;

$payment = $asaas->payments->create(
    new CreatePayment($customer->id, BillingType::Pix, 49.90, new DateTimeImmutable('+1 day')),
);
$qr = $asaas->payments->pixQrCode($payment->id);
$qr->payload;      // Pix copia e cola
$qr->encodedImage; // QR Code em base64
```

### Cartão de crédito (captura imediata)

```php
use Kongkodesoft\Asaas\Request\{CreatePayment, CreditCard, CreditCardHolderInfo};

$payment = $asaas->payments->create(new CreatePayment(
    customer: $customer->id,
    billingType: BillingType::CreditCard,
    value: 100.00,
    dueDate: new DateTimeImmutable('today'),
    creditCard: new CreditCard('Maria Silva', '4111111111111111', '12', '2030', '123'),
    creditCardHolderInfo: new CreditCardHolderInfo('Maria Silva', 'maria@example.com', '24971563792', '89223005', '277', '4738010919'),
    remoteIp: $ip, // IP real do comprador (obrigatório)
));
$payment->isPaid();
```

Outras operações: `authorizeOnly: true` + `captureAuthorized()`, `payWithCreditCard()`, `refund()`, `status()`, `delete()`/`restore()`, 3DS via `threeDSecure`/`deviceInfo`.

## Recorrência

### Assinatura com cartão de crédito

```php
use Kongkodesoft\Asaas\Enum\SubscriptionCycle;
use Kongkodesoft\Asaas\Request\CreateSubscription;

$sub = $asaas->subscriptions->create(new CreateSubscription(
    customer: $customer->id,
    billingType: BillingType::CreditCard,
    value: 29.90,
    nextDueDate: new DateTimeImmutable('+1 day'),
    cycle: SubscriptionCycle::Monthly,
    creditCard: $card,
    creditCardHolderInfo: $holder,
    remoteIp: $ip,
));

$token = $sub->creditCardToken; // reutilize em novas cobranças/assinaturas

$asaas->subscriptions->updateCreditCard($sub->id, creditCardToken: $novoToken, remoteIp: $ip);
$asaas->subscriptions->update($sub->id, ['value' => 39.90, 'updatePendingPayments' => true]);
$asaas->subscriptions->payments($sub->id);
$asaas->subscriptions->delete($sub->id);
```

Para tokenizar sem cobrar: `$asaas->creditCards->tokenize($customerId, $card, $holder, $ip)`.

> `CreateSubscription` aceita somente `CREDIT_CARD` e `PIX`. Assinatura com `PIX` gera cobranças Pix a cada ciclo (o pagador paga cada QR Code); para débito automático real use o Pix Automático.

### Pix Automático

O pagador autoriza uma vez (QR Code de autorização) e as cobranças seguintes são debitadas automaticamente.

```php
use Kongkodesoft\Asaas\Enum\PixAutomaticFrequency;
use Kongkodesoft\Asaas\Request\{CreatePixAutomaticAuthorization, ImmediateQrCode};

$auth = $asaas->pixAutomatic->createAuthorization(new CreatePixAutomaticAuthorization(
    frequency: PixAutomaticFrequency::Monthly,
    contractId: 'contrato-123',
    startDate: new DateTimeImmutable('+1 day'),
    customerId: $customer->id,
    immediateQrCode: new ImmediateQrCode(expirationSeconds: 3600, originalValue: 49.90),
    value: 49.90,
));
$auth->payload; // Pix copia e cola de autorização

$asaas->pixAutomatic->getAuthorization($auth->id);
$asaas->pixAutomatic->listInstructions(authorizationId: $auth->id);
$asaas->pixAutomatic->retryInstruction($instructionId, new DateTimeImmutable('+3 days'));
$asaas->pixAutomatic->cancelAuthorization($auth->id);
```

## Webhooks

```php
use Kongkodesoft\Asaas\Webhook\WebhookReceiver;

$receiver = new WebhookReceiver(getenv('ASAAS_WEBHOOK_TOKEN'));
$event = $receiver->receive($rawBody, $headers['asaas-access-token'] ?? null); // WebhookException se inválido

// Entrega at-least-once: deduplique por $event->id e responda 200 rapidamente.
if ($event->is('PAYMENT_RECEIVED')) { /* $event->payment */ }
if ($event->isPixAutomatic())       { /* $event->raw */ }
```

## Paginação

```php
$page = $asaas->payments->list(['status' => PaymentStatus::Pending], offset: 0, limit: 50); // Page
foreach ($asaas->payments->iterate(['customer' => $id]) as $payment) { /* lazy, todas as páginas */ }
```

Filtros de data usam a sintaxe da API: `['dueDate[ge]' => new DateTimeImmutable('2030-01-01')]`.

## Erros e retry

| Exceção | Quando |
|---|---|
| `ValidationException` (400) | dados inválidos; veja `$e->errors` |
| `AuthenticationException` (401) / `ForbiddenException` (403) / `NotFoundException` (404) | |
| `RateLimitException` (429) | `$e->resetSeconds` (header `RateLimit-Reset`) |
| `ServerException` (5xx), `NetworkException` | falha do Asaas / transporte |

Retentativas (`Config::$retry`, padrão 3 tentativas, backoff exponencial com jitter):

- **429**: todos os métodos (a requisição foi rejeitada), respeitando `RateLimit-Reset`.
- **5xx e falhas de rede**: **somente GET**. POST nunca é repetido automaticamente, pois a API não documenta `Idempotency-Key` e repetir poderia duplicar cobranças. Em resultado incerto, consulte por `externalReference`.

## Swoole / coroutines

- `Asaas`, `Config`, resources, transporte e DTOs são `readonly` e sem estado estático; **uma única instância pode ser compartilhada** entre coroutines (um teste automatizado garante a ausência de estado estático/mutável).
- Habilite os hooks: `Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL)`, de modo que o cliente HTTP e `usleep` cedam a coroutine. Com Guzzle, prefira o `StreamHandler`; evite compartilhar um handler cURL sem pool.
- Alternativamente implemente `Sleeper` com `Swoole\Coroutine::sleep()` e passe em `Asaas::create(..., sleeper: $sleeper)`.
- Limites da API: 50 GETs simultâneos e 25.000 requisições/12h — limite a concorrência (ex.: `Swoole\Coroutine\Channel` como semáforo).

## Migração da v1

- Namespace único `Kongkodesoft\Asaas`; `Asaas::create()` exige cliente PSR-18/PSR-17.
- Entidades antigas (`Connection`, `Client`, `LinkPayment`...) foram substituídas por `customers`, `payments`, `subscriptions`, `creditCards`, `pixAutomatic`.
- Respostas são DTOs tipados (com `->raw` para campos não mapeados); erros são exceções tipadas.
- PHP mínimo: 8.5.

## Desenvolvimento

Veja [CONTRIBUTING.md](CONTRIBUTING.md). `composer check` roda estilo, PHPStan e testes; sandbox: `ASAAS_SANDBOX_KEY=... composer test:sandbox`.

## Licença

MIT.
