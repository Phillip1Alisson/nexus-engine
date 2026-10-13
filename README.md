# NexusEngine 🚀

Ecossistema distribuído de alta performance projetado sob a arquitetura de microserviços híbridos (*Polyglot Backend*), integrando **Node.js (NestJS)** na camada de ingestão de rede e **PHP 8.3+ (Laravel)** como motor core financeiro multi-tenant.

---

## 🏗️ Estrutura do Monorepo

O repositório é gerenciado sob o modelo de monorepo unificado, dividindo as responsabilidades de infraestrutura de rede e lógica contábil:

*   **`📂 docs/`**: Documentações arquiteturais e manuais de regras de negócio.
*   **`📂 nexus-gateway-node/`**: Gateway de borda (NestJS 11) responsável por receber webhooks, validar payloads de forma assíncrona e despachar para filas em memória via Redis.
*   **`📂 nexus-core-php/`**: Core ERP (Laravel 11) focado no processamento transacional pesado, isolamento de dados de inquilino (*Tenant isolation*) e motor algorítmico de custeio (PEPS/CMP).

---

## ⚡ Requisitos Técnicos Mínimos Local

*   Docker e Docker Compose instalado.
*   Node.js v22 ou superior (para execução local do Gateway).
*   PHP 8.3 ou superior + Composer (para execução local do Core).

---

## 🛠️ Inicialização Rápida da Infraestrutura Comum

Para subir os serviços de cache/mensageria (Redis) e banco de dados relacional (PostgreSQL) compartilhados, execute o comando na raiz do repositório:

```bash
docker-compose up -d
```

---

## 📜 Governança, Padrões e Qualidade de Código

Para garantir a sustentabilidade, escalabilidade e manutenibilidade do código-fonte a longo prazo, este projeto adota limites rigorosos de engenharia de software (como limites de tamanho de arquivos e proibição de aninhamentos complexos).

👉 **Antes de submeter qualquer linha de código ou pull request, leia atentamente as nossas diretrizes técnicas em:** [CONTRIBUTING.md](./CONTRIBUTING.md).
