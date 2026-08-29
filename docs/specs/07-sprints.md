# Sprints

Este arquivo registra a ordem histórica. Ele não cria regra de produto.

## Etapas 1–10

| Etapa | Entrega |
| --- | --- |
| 1 | Schema, triggers e seed |
| 2 | Autenticação Sanctum |
| 3 | Categorias e produtos |
| 4 | Clientes |
| 5 | Caixa: abrir, consultar, movimentar, histórico e fechar |
| 6 | Mesas e comandas |
| 7 | Pedidos, itens e status |
| 8 | Pagamentos, fechamento do pedido e estoque |
| 9 | Dashboard operacional |
| 10 | SPA Vue nas rotas do MVP |

## Ajustes fora do plano inicial

- O Bearer token passou a ficar apenas em memória. A SPA remove o token legado do `localStorage` na inicialização.
- Inter passou a ser a fonte global.
- O login recebeu layout em duas colunas e perdeu o chip e os cards promocionais.
- A sprint visual aplicou tokens ao shell e às telas operacionais.
- O contraste passou a usar `#1c1917` no texto e `#44403c` em labels e cabeçalhos.
- `payment_method` foi consolidado como nome do campo de pagamento.
- O README passou a registrar instalação no WSL, subida do Vite e smoke da API.
- A documentação foi dividida em specs satélite, com [SPEC.md](../SPEC.md) como entrada canônica.

Detalhes datados ficam em [CHANGELOG.md](../CHANGELOG.md).
