# Changelog

Este arquivo registra ajustes feitos após a conclusão das etapas 1–10. Os contratos canônicos continuam definidos em [SPEC.md](SPEC.md).

## 2026-08-28 — Ajustes posteriores às etapas 1–10

### Autenticação

- O Bearer token do Sanctum passou a existir somente em memória no frontend.
- A persistência do token em `localStorage` e `sessionStorage` foi proibida.
- A inicialização da SPA remove apenas o token legado salvo anteriormente.

### Interface

- O login passou a usar layout responsivo em duas colunas, com atmosfera visual à esquerda e formulário à direita.
- Inter foi definida como fonte global do frontend.
- O chip “Gestão para restaurantes” e os cards `01`, `100%` e `MVP` foram removidos do login.
- O app shell e as telas operacionais receberam tokens visuais compartilhados, navegação responsiva e ações curtas para o PDV.
- O contraste global foi reforçado: texto principal `#1c1917`, labels e cabeçalhos `#44403c`, peso mínimo 500 e placeholders mais claros.
- A suavização `antialiased` e transforms que poderiam reduzir a nitidez foram removidos das superfícies operacionais.

### Pagamentos

- `payment_method` foi consolidado como o campo canônico para registrar a forma de pagamento.
- O método `CASH` foi validado em pagamentos parciais e totais.
