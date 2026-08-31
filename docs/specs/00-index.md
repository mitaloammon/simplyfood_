# Ordem de leitura

Este diretório compõe o contrato canônico do SimplyFood. [SPEC.md](../SPEC.md) é a porta de entrada.

Cada regra tem um único arquivo responsável. Os demais arquivos apontam para ele.

## Para pessoas

1. [Produto](01-produto.md): público, escopo e papéis.
2. [Regras](02-regras.md): permissões, tenant, operação e integridade de dados.
3. [Arquitetura](03-arquitetura.md): stack, segurança e convenções técnicas.
4. [API](04-api.md): contrato HTTP por módulo.
5. [Design system](05-design-system.md): interface do PDV.
6. [Testes](06-testes.md): critérios de aceite.
7. [Sprints](07-sprints.md): histórico de implementação.
8. [Skills](08-skills.md): ferramentas de apoio para trabalho visual e texto.
9. [Fluxos de casos de uso](diagrams/00-index.md): sequências reais da API e da SPA.

## Para agentes

Leia `01` a `06` antes de alterar comportamento. Consulte `07` para contexto histórico e `08` antes de redesenhar a SPA.

Em conflito com o código, registre **a verificar no código**. Não crie uma regra para preencher lacunas.
