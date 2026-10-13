# Documento de Arquitetura de Software (SAD) — NexusEngine


## 1. Visão Geral e Conceito do Projeto

O NexusEngine é um ecossistema distribuído de alta performance projetado para resolver duas dores críticas de plataformas corporativas SaaS modernas:

1. Ingestão Massiva de Dados Assíncronos: Absorção e orquestração de webhooks em tempo real vindos de canais digitais externos (ex: APIs de Mensageria, WhatsApp Cloud API, Gateways de Pagamento), sem gerar gargalos de I/O na aplicação principal.
2. Motor Financeiro Multi-Tenant Complexo: Processamento isolado, transacional e rigoroso de movimentações de estoque utilizando o modelo algorítmico PEPS (Primeiro que Entra, Primeiro que Sai) e CMP (Custo Médio Ponderado).
O sistema adota uma abordagem híbrida (Polyglot Backend), utilizando Node.js para a camada de infraestrutura de rede/redirecionamento assíncrono e PHP para a centralização da lógica de domínio de alta complexidade matemática e contábil.

## 2. Abordagem Arquitetural

O sistema é estruturado sob o padrão de Microsserviços Híbridos, comunicando-se de forma assíncrona orientada a eventos através de um barramento de mensageria em cache com alta taxa de vazão (Queue-Driven Design). Internamente, ambos os serviços implementam Clean Architecture e Princípios SOLID para isolar as regras de negócio dos frameworks.
text
  [ Provedor Externo / Webhook ] ──( HTTPS Post )──► [ Node.js Gateway / NestJS ]
                                                           │
                                                   ( Escrita em Fila )
                                                           ▼
                                                    [ Redis Cache ]
                                                           │
                                                   ( Consumo / Worker )
                                                           ▼
  [ Banco Relacional PostgreSQL ] ◄──( Mutação )─── [ Core Engine / PHP Laravel ]
Use o código com cuidado.

Componentes de Software:

• Nexus-Gateway (Node.js/NestJS): Camada de borda (Edge Gateway). Responsável estritamente pela recepção de requests assíncronos, verificação de assinaturas criptográficas baseadas em hash de validação, controle de taxa (Rate Limiting) por Tenant e despacho imediato para filas em memória. Não acessa o banco de dados principal.
• Nexus-Core (PHP 8.3+/Laravel): Camada de processamento pesado. Atua como o cérebro do sistema. Consome as mensagens pré-processadas, injeta o escopo de isolamento do inquilino (Tenant Context Isolation), executa o algoritmo de custeio por fila FIFO (PEPS) e gerencia as transações atômicas no banco de dados relacional.

## 3. Matriz Tecnológica


Camada / Responsabilidade	Tecnologia Escolhida	Justificativa de Engenharia Sênior
Ingestão & Gateway	Node.js v22+ / NestJS 11	Arquitetura Event-Driven orientada a I/O não bloqueante, ideal para lidar com conexões concorrentes massivas de webhooks externos.
Processamento Core	PHP 8.3+ / Laravel 11	Ecossistema maduro para modelagem de domínios corporativos ricos, manipulação estável de filas nativas, segurança transacional nativa e processamento em lote (batch).
Broker de Mensageria	Redis Server	Armazenamento in-memory de latência ultra-baixa para persistência temporária das filas (BullMQ ou Laravel Queues).
Persistência de Dados	PostgreSQL / MySQL	Garantia de propriedades ACID estritas para cálculos contábeis, suporte a chaves estrangeiras complexas e indexação otimizada para consultas financeiras por Tenant.
Isolamento de Infra	Docker & Docker Compose	Padronização dos ambientes de desenvolvimento e produção, garantindo paridade total de rede local, Redis e banco de dados.
Agile IA Tooling	Cursor / OpenCode / Aider	Engenharia assistida contextualmente acoplada a geradores de documentação para aceleração de testes unitários.


## 4. Design de Código e Arquitetura de Pastas (Monorepo)

O projeto adota uma estrutura de diretórios limpa e explícita, separando as responsabilidades de rede das responsabilidades de regra contábil pura.
text
nexus-engine/
│
├── 📂 nexus-gateway-node/         # Microsserviço Borda (NestJS)
│   ├── src/
│   │   ├── 📂 webhooks/           # Controllers de entrada, Validação Criptográfica, DTOs
│   │   ├── 📂 queues/             # Produtores de mensageria (BullMQ/Redis Integration)
│   │   └── 📂 common/             # Middlewares de controle de tráfego de rede (Rate Limiting)
│   └── test/                      # Testes de carga e integração e2e
│
└── 📂 nexus-core-php/             # Microsserviço Core (Laravel 11)
    ├── app/
    │   ├── 📂 Domain/             # Camada Pura: Entidades, Regras de Negócio, Sem Framework
    │   │   ├── 📂 Inventory/
    │   │   │   ├── Entities/      # ItemEstoque, MovimentacaoLote
    │   │   │   └── Strategies/    # PepsStrategy.php, CmpStrategy.php (Strategy Pattern)
    │   │   └── 📂 Shared/         # Interfaces e Contratos Globais
    │   │
    │   ├── 📂 Application/        # Casos de Uso (Use Cases)
    │   │   └── Inventory/         # ProcessarEntradaEstoque.php, ProcessarBaixaPeps.php
    │   │
    │   └── 📂 Infrastructure/     # Detalhes Técnicos e Acoplamentos de Framework
    │       ├── 📂 Tenancy/        # TenantContextManager.php (Isolamento de Escopo)
    │       ├── 📂 Persistence/    # Repositórios Eloquent, Migrations de Tabelas Relacionais
    │       └── 📂 Queue/          # Consumidores de Fila (Jobs de Processamento Assíncrono)
    └── tests/                     # Testes Unitários de Lógica Algorítmica (PHPUnit)
Use o código com cuidado.

## 5. Engenharia de Isolamento: Arquitetura Multi-Tenant

Para mitigar riscos de vazamento de dados de clientes (Data Leakage), o NexusEngine implementa isolamento lógico em nível de aplicação na camada de infraestrutura do PHP:

• Cada payload recebido pelo gateway trafega com um identificador criptografado único (tenant_id) extraído diretamente do cabeçalho de autenticação JWT ou da chave de API do cliente externo.
• Ao entrar no microsserviço PHP, um middleware/interceptor captura este token e injeta um escopo de sessão global imutável.
• Todas as consultas via banco de dados são filtradas dinamicamente com base nesse escopo ativo, impossibilitando que um inquilino consulte registros de estoque ou financeiros de outro.

## 6. O Algoritmo de Custeio PEPS (FIFO) no Domínio

A lógica do PEPS exige precisão cirúrgica de dados de fila para manipulação de estoques em lotes. Quando ocorre uma saída de estoque, o motor do PHP executa o seguinte fluxo puramente em memória e persiste em bloco transacional:

1. Busca todos os lotes de entrada do produto que ainda possuem saldo em aberto para o tenant_id ativo, ordenados de forma ascendente pela data de criação (created_at ASC).
2. Deduz a quantidade vendida a partir do lote mais antigo ativo.
3. Se a quantidade vendida for maior que o saldo do lote mais antigo, o motor consome totalmente o saldo desse lote, calcula o custo proporcional e passa automaticamente para o lote subsequente até zerar a demanda de saída.
4. Gera o relatório do Custo de Mercadoria Vendida (CMV) com base nos valores históricos reais de entrada de cada lote descarregado, salvando o estado contábil final de forma atômica no PostgreSQL/MySQL.
