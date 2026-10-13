# NexusEngine 🚀

Ecossistema distribuído de alta performance projetado sob a arquitetura de microserviços híbridos (*Polyglot Backend*), integrando **Node.js (NestJS)** na camada de ingestão de rede e **PHP 8.3+ (Laravel)** como motor core financeiro multi-tenant.

O projeto resolve o desafio de absorver webhooks de alta concorrência em tempo real e processar baixas de estoque transacionais utilizando o modelo algorítmico **PEPS (Primeiro que Entra, Primeiro que Sai)** de forma assíncrona e isolada por inquilino.

---

## 🏗️ Estrutura do Monorepo e Entregas Técnicas

O repositório é gerenciado sob o modelo de monorepo unificado, dividindo as responsabilidades de infraestrutura de rede e lógica contábil:

*   **`📂 nexus-gateway-node/`**: Gateway de borda (NestJS 11 + Fastify) responsável por receber webhooks, aplicar validação rígida de contratos de dados (DTOs com `class-validator`), barrar acessos maliciosos via `WebhookGuard` criptográfico e despachar os payloads assincronamente para o Redis.
*   **`📂 nexus-core-php/`**: Core ERP (Laravel 11) focado no processamento transacional pesado. Implementa a estratégia pura de domínio do algoritmo **PEPS (FIFO)** sob tipagem estrita (`strict_types=1`), isolamento lógico via `TenantContextMiddleware`, banco de dados indexado para filas cronológicas e um Background Worker (`ProcessStockDeductionJob`) para consumir as mensagens do Redis.

---

## 🛠️ Requisitos Técnicos Mínimos e Infraestrutura

*   Docker e Docker Compose instalados.
*   Node.js v22 (LTS) ou superior.
*   PHP 8.3 ou superior + Composer.

Para subir a infraestrutura comum de desenvolvimento (banco de dados relacional PostgreSQL e broker de mensageria Redis) compartilhada pelos serviços, execute na raiz do repositório:

```bash
docker-compose up -d
```

---

## 🧪 Executando as Suítes de Testes Automatizados

Ambos os projetos foram construídos sob rígidos critérios de qualidade (limite de 200 linhas por arquivo, complexidade ciclomática reduzida e ausência de ifs aninhados). Para validar a integridade dos contratos e algoritmos, execute:

### 🟢 Testes de Integração / E2E no Node.js:
```bash
cd nexus-gateway-node
npm run test
```
*Valida o pipeline de validação de payloads do Fastify e o comportamento de rejeição de contratos HTTP (Vitest).*

### 🟤 Testes Unitários no PHP 8.3:
```bash
cd nexus-core-php
php composer.phar test tests/Unit/Domain/Inventory/Strategies/PepsCostingStrategyTest.php
```
*Valida a precisão matemática e contábil do descarregamento de lotes cronológicos do algoritmo PEPS (PHPUnit 11 usando Attributes nativos).*

---

## 📜 Governança e Qualidade

Todas as diretrizes de contribuição, padrões de código (SOLID, Clean Code) e configurações de Linters automatizados (**ESLint** para Node e **Laravel Pint** para PHP) estão documentados em nosso [CONTRIBUTING.md](./CONTRIBUTING.md).
