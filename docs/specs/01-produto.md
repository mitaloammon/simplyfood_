# Produto

## Objetivo e público

SimplyFood é um sistema de gestão operacional para um estabelecimento de food service. Atende a operação presencial de gestores, caixa e salão.

O trabalho principal é atender mesas, registrar pedidos, receber pagamentos e operar o caixa com rapidez.

## Módulos do MVP

- Autenticação e contexto do estabelecimento
- Clientes
- Categorias e produtos
- Mesas e comandas
- Pedidos e itens
- Caixa, movimentações e pagamentos
- Dashboard operacional
- Baixa de estoque no fechamento do pedido, quando houver ficha

Tabelas de cozinha, promoções, notificações e auditoria podem existir no schema futuro. Elas não entram em migration ou API do MVP.

## Papéis

| Papel | Responsabilidade |
| --- | --- |
| `ADMIN` | Dono ou gestor máximo do estabelecimento |
| `MANAGER` | Operação, catálogo, usuários, exceto ADMIN, e caixa |
| `CASHIER` | Caixa, pagamentos e pedidos |
| `WAITER` | Mesas, comandas, pedidos e clientes |
| `KITCHEN` | Papel reservado. Não há telas de cozinha no MVP. |

A matriz de autorização pertence a [Regras](02-regras.md#matriz-de-permissões).
Os papéis válidos são apenas os cinco acima. O papel `OPERATOR` não existe.


## Fora do MVP

- Cadastro público de usuário
- WhatsApp
- Gateway de pagamento
- Delivery com geolocalização
- KDS e telas de cozinha
- Grupos de opções de produto e adicionais
- Promoções
- Módulo separado de receitas além de `product_ingredients`
- Relatórios gerenciais avançados
- Multi-estabelecimento por usuário
- Super-admin de plataforma
- Inertia
- `order_type = DELIVERY`

Realtime e Reverb podem ser avaliados depois. REST continua como fonte de verdade.
