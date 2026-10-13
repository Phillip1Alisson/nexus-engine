
Status do Desenvolvimento: nexus-core-php

Este documento consolida tudo o que foi implementado, validado e testado até o momento no microsserviço Core PHP, além de mapear as pendências de infraestrutura necessárias para a conclusão total do serviço.

✅ 1. O que já foi FEITO (Entregue e Commitado)

A fundação arquitetural seguindo os princípios de Clean Architecture, SOLID e as restrições de qualidade do CONTRIBUTING.md está 100% concluída nas seguintes camadas:

🛡️ Camada de Domínio Pura (Isolada de Framework)

• Modo de Tipagem Estrita: Todos os arquivos configurados nativamente com declare(strict_types=1); para evitar coerção de dados financeiros.
• Entidade InventoryBatch: Modelagem imutável de lotes de entrada de mercadorias, controlando saldos e isolamento lógico por tenantId.
• Estratégia PepsCostingStrategy: Implementação pura do algoritmo de custeio PEPS (FIFO), utilizando Cláusulas de Guarda para eliminar condicionais aninhadas (ifs) e respeitando o teto de tamanho.
• Exceção de Negócio InsufficientStockException: Tratamento tipado e explícito para falhas de saldo em estoque.
• Contrato InventoryRepositoryInterface: Abstração de persistência usando Inversão de Dependência (DIP) para manter o domínio blindado contra acoplamentos de banco de dados ou ORM.

🧪 Camada de Testes Automatizados (Qualidade)

• Suíte com PHPUnit 11: Cobertura total de testes unitários para a estratégia contábil do PEPS em memória.
• Modernização para PHP 8: Refatoração de comentários legados para os novos Attributes nativos #[Test], zerando todas as depreciações do console (PHPUnit Deprecations: 0).

📦 Camada de Aplicação (Orquestração de Fluxo)

• Caso de Uso DeductStockUseCase: Componente responsável por gerenciar a transação de baixa de estoque. Ele recebe o contexto, busca os lotes através da interface do repositório, delega a matemática para o algoritmo PEPS e salva os saldos atualizados de forma atômica.

⏳ 2. O que falta FAZER (Próximos Passos na Infraestrutura PHP)

A lógica de negócio está pronta, mas para o microsserviço PHP ser considerado completo e executável em produção, precisamos implementar a camada externa de Infrastructure (Detalhes Técnicos):
1. Implementação do Repositório Concreto:
	• Criar a classe EloquentInventoryRepository ou usar um Query Builder (como Prisma/Eloquent nativo) que implemente a InventoryRepositoryInterface, realizando a busca real das tabelas no PostgreSQL/MySQL.
2. Migrations do Banco de Dados:
	• Escrever a estrutura das tabelas relacionais de lotes (inventory_batches) garantindo índices compostos chaveados por (tenant_id, product_id, created_at) para garantir a velocidade máxima de leitura cronológica exigida pelo algoritmo FIFO.
3. Consumidor de Filas (Queue Consumer / Worker):
	• Criar um Laravel Job/Worker configurado para escutar a fila do Redis. Esse componente será disparado de forma assíncrona assim que o Gateway Node.js injetar uma nova mensagem de baixa, extraindo o payload e disparando o nosso DeductStockUseCase.
4. Middleware de Isolamento Multi-Tenant:
	• Implementar o TenantContextMiddleware para capturar a identificação do inquilino vinda da mensagem ou do cabeçalho da requisição e travar o escopo global do banco de dados, blindando o sistema contra vazamento de dados (Data Leakage).
