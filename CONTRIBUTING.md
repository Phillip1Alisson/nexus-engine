# Guia de Estilo, Boas Práticas e Governança de Código

Este guia estabelece os limites arquiteturais e as restrições rígidas de codificação para os microsserviços do **NexusEngine**. Todas as implementações (manuais ou geradas por IA) devem seguir estas regras sem exceção.

---

## 🛑 1. Restrições Rígidas de Escrita e Estrutura

### 📐 Limite de Tamanho de Arquivo (Máximo 200 Linhas)
*   **Regra:** Nenhum arquivo de código-fonte (`.php`, `.ts`, `.js`) pode ultrapassar **200 linhas de código executável** (excluindo cabeçalhos de documentação docblock longos).
*   **Por quê?** Arquivos gigantescos violam o princípio de responsabilidade única. Se um arquivo passou de 200 linhas, a classe ou componente está fazendo mais do que deveria e precisa ser refatorado/estendido em novas classes, hooks, ou serviços menores.

### 🪆 Proibição de Estruturas Condicionais Aninhadas (Nested Ifs)
*   **Regra:** É estritamente **proibido** o aninhamento de estruturas condicionais (`if/else`) além do primeiro nível. Condicionais com 3 ou mais profundidades (`if { if { if } }`) serão rejeitadas no code review.
*   **Como resolver?** Utilize as técnicas de **Cláusulas de Guarda (Guard Clauses)**, retorno precoce (*Early Return*) e extração de lógica para pequenos métodos de validação.
*   *Exemplo Incorreto:*
    ```typescript
    if (user) {
        if (user.isActive) {
            if (user.hasPermission) { /* lógica... */ }
        }
    }
    ```
*   *Exemplo Correto (Guard Clauses):*
    ```typescript
    if (!user || !user.isActive || !user.hasPermission) return;
    // lógica limpa no escopo principal...
    ```

---

## 🧩 2. Princípios SOLID Aplicados

### S – Single Responsibility Principle (Responsabilidade Única)
*   Uma classe deve ter um, e apenas um, motivo para mudar. Controllers apenas recebem a requisição e chamam um Caso de Uso. Repositórios apenas realizam persistência. Regras contábeis e matemáticas de negócio residem exclusivamente na camada de `Domain`.

### O – Open/Closed Principle (Aberto/Fechado)
*   Artefatos de software devem estar abertos para extensão, mas fechados para modificação. Motores de cálculo (como a troca de PEPS para CMP) devem ser desacoplados através de abstrações, interfaces e o padrão *Strategy*, permitindo adicionar novos comportamentos sem alterar o código existente.

### L – Liskov Substitution Principle (Substituição de Liskov)
*   Subclasses ou implementações de interfaces devem ser capazes de substituir suas classes base/contratos sem quebrar o comportamento do sistema ou lançar exceções inesperadas que o contrato original não preveja.

### I – Interface Segregation Principle (Segregação de Interfaces)
*   Crie interfaces pequenas, coesas e altamente específicas em vez de interfaces genéricas "monolíticas". Classes não devem ser forçadas a implementar métodos de contratos que elas não utilizam.

### D – Dependency Inversion Principle (Inversão de Dependência)
*   Módulos de alto nível (Casos de Uso) não devem depender de módulos de baixo nível (Framework, ORM, Bibliotecas de Terceiros). Ambos devem depender de abstrações (Interfaces). O framework serve apenas para injetar as dependências concretas na inicialização do serviço.

---

## ✨ 3. Padrões de Clean Code (Código Limpo)

*   **Nomes Significativos e Reveladores de Intenção:** Evite variáveis de uma única letra (como `$x`, `$i`) ou abreviações obscuras. Use nomes descritivos em inglês, como `availableStockBalance` ou `oldestInventoryBatch`.
*   **Funções Pequenas e Monotarefas:** Funções devem fazer apenas uma coisa e fazê-la bem. O tamanho ideal de uma função/método é de até **15 linhas**. Se houver etapas consecutivas complexas dentro da função, extraia-as para métodos privados auxiliares descritivos.
*   **Ausência de Efeitos Colaterais (Side Effects):** Uma função projetada para calcular um valor ou validar um payload não deve alterar estados globais do sistema ou realizar mutações ocultas em banco de dados fora de seu escopo explícito.
*   **Tratamento de Erros Limpo:** Utilize blocos `try/catch` para capturar exceções de infraestrutura nas bordas da aplicação. No domínio do software, prefira falhas limpas através de exceções de negócio tipadas explicitamente (ex: `InsufficientStockException`).

---

## 🛠️ 4. Convenção de Controle de Versão (GitFlow & Commits)

*   **Branches:** Parta sempre de `develop`. Crie branches nomeadas exclusivamente em inglês no formato kebab-case: `feature/<descricao>`, `bugfix/<descricao>`, `hotfix/<descricao>`.
*   **Mensagens de Commit:** Adote estritamente a convenção de **Conventional Commits** em inglês no modo imperativo:
    *   `feat(...)`: Adição de nova funcionalidade técnica.
    *   `fix(...)`: Correção de bug de código.
    *   `test(...)`: Adição ou modificação de cobertura de testes automatizados.
    *   `docs(...)`: Modificações estritas na documentação técnica.
    *   `chore(...)`: Ajustes estruturais, configuração de ferramentas ou lints.
