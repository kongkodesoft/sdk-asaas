# Guardrails

Regras rígidas para o agente:

- **PHP 8.5+**
- PSR‑12
- `declare(strict_types=1)` em todos os arquivos
- Type safety (uso de tipos estritos, `?` e `array{}` onde for pertinente)
- SOLID
- Clean Architecture quando aplicável
- Backward compatibility
- API design consistente
- SDK orientado a domínio
- Tratamento de erros HTTP
- Exceptions tipadas
- Retries / timeouts
- Idempotência
- Paginação
- Autenticação
- Serialização / deserialização
- DTOs / value objects
- Interfaces
- Dependency injection
- Testes unitários/integrados
- PHPStan/Psalm
- Composer
- Documentação PHPDoc apenas quando realmente necessária
- Evitar abstrações prematuras
- Não quebrar API pública existente
- Não introduzir dependências sem justificativa
- Compatibilidade específica com PHP 8.5
- Segurança de tokens/credenciais
- HTTP client desacoplado
