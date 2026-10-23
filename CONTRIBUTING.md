# Contribuindo

Contribuições são bem-vindas via Pull Request em <https://github.com/kongkodesoft/asaas-sdk>.

## Requisitos

- PHP 8.5+, `declare(strict_types=1)` em todos os arquivos, padrão **PSR-12**.
- Não invente endpoints: toda chamada deve vir da [documentação oficial](https://docs.asaas.com/reference/about-this-doc).
- **Coroutine-safe**: sem propriedades estáticas, sem estado mutável compartilhado; classes `readonly`. Há um teste que garante isso.
- Nunca registre/exponha chaves de API, dados de cartão ou tokens (use `#[\SensitiveParameter]`).

## Verificações

```bash
composer cs      # PSR-12 (php-cs-fixer, dry-run); composer cs:fix para corrigir
composer stan    # PHPStan nível max
composer test    # PHPUnit (unitários)
ASAAS_SANDBOX_KEY=... composer test:sandbox   # integração no sandbox (opcional)
```

## Pull Requests

- Um PR por funcionalidade, em um branch dedicado.
- Inclua testes e atualize o README quando o comportamento mudar.
- Mantenha um histórico coerente (squash de commits intermediários).
