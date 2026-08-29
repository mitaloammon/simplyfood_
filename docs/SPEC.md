# SimplyFood — Especificação Canônica

> Entrada oficial do contrato do sistema.  
> As specs satélite abaixo compõem uma única fonte de verdade.  
> Versão: 1.0.0 · Base original: 2026-08-18

## Produto

SimplyFood é um PDV para a operação presencial de um estabelecimento de food service. Cobre atendimento, pedidos, caixa, cardápio, clientes, dashboard e estoque básico.

Comece pela [ordem de leitura](specs/00-index.md).

## Contrato por responsabilidade

| Spec | Responsabilidade canônica |
| --- | --- |
| [00 — Ordem de leitura](specs/00-index.md) | Navegação para pessoas e agentes |
| [01 — Produto](specs/01-produto.md) | Público, papéis, escopo e limites do MVP |
| [02 — Regras](specs/02-regras.md) | Permissões, tenant, regras operacionais, dados, DDL e triggers |
| [03 — Arquitetura](specs/03-arquitetura.md) | Stack, autenticação, envelope e requisitos técnicos |
| [04 — API](specs/04-api.md) | Rotas e exemplos HTTP por módulo |
| [05 — Design system](specs/05-design-system.md) | Tokens, login, shell e rotas Vue |
| [06 — Testes](specs/06-testes.md) | Critérios de aceite e smoke |
| [07 — Sprints](specs/07-sprints.md) | Histórico das etapas e ajustes posteriores |
| [08 — Skills](specs/08-skills.md) | Referências de apoio para interface e texto |

## Fora do MVP

A definição normativa está em [Produto](specs/01-produto.md#fora-do-mvp).

Em resumo: WhatsApp, gateway de pagamento, delivery com geolocalização, KDS, adicionais, promoções, receitas separadas, relatórios avançados, multi-estabelecimento por usuário e super-admin de plataforma ficam fora do MVP.

## Ponte do documento anterior

Nenhum conteúdo do SPEC monolítico foi descartado. Ele foi movido assim:

- objetivo, escopo e papéis: [Produto](specs/01-produto.md);
- tenant, regras, modelo de dados, DDL e triggers: [Regras](specs/02-regras.md);
- decisões técnicas e requisitos não funcionais: [Arquitetura](specs/03-arquitetura.md);
- contratos HTTP e status: [API](specs/04-api.md);
- frontend e rotas da SPA: [Design system](specs/05-design-system.md);
- qualidade e aceite: [Testes](specs/06-testes.md);
- ordem de implementação e mudanças posteriores: [Sprints](specs/07-sprints.md).

Mudanças datadas continuam em [CHANGELOG.md](CHANGELOG.md).
