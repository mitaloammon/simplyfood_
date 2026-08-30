# Design system

## Direção

A interface é um PDV para caixa e garçom. Priorize leitura imediata, ações curtas e densidade operacional. Não trate as telas como landing page.

Verbos de interface são diretos: “Abrir caixa”, “Adicionar item” e “Fechar conta”.

## Tipografia

Inter é a fonte global:

```css
font-family: 'Inter', system-ui, -apple-system, sans-serif;
```

| Papel | Peso | Cor |
| --- | --- | --- |
| Texto e valor de campo | 500 ou maior | `#1c1917` |
| Label e cabeçalho de tabela | 500 | `#44403c` |
| Ação | 600 | conforme o estado |
| Título | 700 | `#1c1917` |
| Placeholder | 400 | pode ser mais claro |

Não aplique `-webkit-font-smoothing: antialiased`. Cards de formulário e tabelas não usam `transform` ou `scale`.

## Cores

| Token | Hex | Uso |
| --- | --- | --- |
| Navy | `#14213d` | navegação e superfícies escuras |
| Blue | `#2563eb` | ação principal e foco |
| Mist | `#e8f0fe` | seleção e realce |
| Floor | `#f4f6f8` | fundo da aplicação |
| White | `#ffffff` | cards e campos |
| Coral | `#dc5a4b` | assinatura visual e alerta de marca |
| Ink | `#1c1917` | texto principal |
| Label | `#44403c` | labels e cabeçalhos |

A assinatura visual é a linha Blue/Coral nas superfícies operacionais e no item ativo da navegação.

## Login

O login ocupa a altura da tela. No desktop, usa duas colunas: atmosfera visual escura à esquerda e formulário à direita. No mobile, empilha ou oculta a imagem.

O painel direito mantém a marca SimplyFood, título curto, e-mail, senha, botão “Entrar” e os links placeholder “Esqueci a senha” e “Novo usuário”.

Não use os cards `01`, `100%` e `MVP`. Não use o chip “Gestão para restaurantes”.

```text
┌──────────────────────────────┬───────────────────────┐
│ atmosfera escura             │ SimplyFood            │
│                              │                       │
│ título                       │ título curto          │
│ subtítulo                    │ e-mail                │
│                              │ senha                 │
│                              │ [ Entrar ]            │
│                              │                       │
│                              │ Esqueci...  Novo...   │
└──────────────────────────────┴───────────────────────┘
```

## App shell

```text
┌──────────────┬───────────────────────────────────────┐
│ SimplyFood   │ contexto da página        usuário     │
│ Dashboard    ├───────────────────────────────────────┤
│ Clientes     │ título                 [ ação curta ] │
│ Produtos     │ filtros / estado operacional          │
│ Mesas        │                                       │
│ Pedidos      │ tabela ou cards densos                │
│ Caixa        │                                       │
└──────────────┴───────────────────────────────────────┘
```

Rotas Vue registradas:

| Rota | Papéis |
| --- | --- |
| `/login` | pública |
| `/dashboard` | ADMIN, MANAGER, CASHIER, WAITER |
| `/customers` | ADMIN, MANAGER, WAITER |
| `/products` | ADMIN, MANAGER |
| `/tables` | ADMIN, MANAGER, WAITER |
| `/orders` | ADMIN, MANAGER, CASHIER, WAITER |
| `/cash` | ADMIN, MANAGER, CASHIER |

O SPEC anterior listava `/inventory`, mas a rota não está registrada em `frontend/src/app/router.ts`: **a verificar no código**.
