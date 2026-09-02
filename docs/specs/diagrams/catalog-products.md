# Consultar, cadastrar e excluir catálogo

## Ator e papéis permitidos / 403

Consulta: `ADMIN`, `MANAGER`, `CASHIER`, `WAITER`. Cadastro: `ADMIN`, `MANAGER`. `CASHIER`, `WAITER` e `KITCHEN` recebem 403 no cadastro; `KITCHEN` também recebe 403 na consulta.

## Pré-condições

Bearer Sanctum válido. No produto, `category_id` deve pertencer à loja; nome e preço são obrigatórios. Toda leitura e gravação usa o `establishment_id` do usuário.

## Rotas canônicas

- `GET /api/categories`
- `POST /api/categories`
- `DELETE /api/categories/{id}`
- `GET /api/products`
- `POST /api/products`
- `DELETE /api/products/{id}`

## Sequência

```mermaid
sequenceDiagram
    actor A as SPA ou curl
    participant R as Rota de catálogo
    participant C as CategoryController / ProductController
    participant S as CategoryService / ProductService
    participant DB as MySQL
    A->>R: GET ou POST /api/categories ou /api/products + Bearer
    R->>C: index(request) ou store(request)
    C->>S: paginate(user, filtros) ou create(user, dados validados)
    S->>DB: SELECT ou INSERT WHERE establishment_id = user.establishment_id
    DB-->>S: categorias/produtos do tenant
    S-->>C: model ou paginação
    C-->>A: envelope JSON (200 ou 201)
```

## Pós-condição

Na consulta, somente registros da loja são listados. No cadastro, categoria ou produto é criado com o `establishment_id` autenticado. Uma categoria só pode ser excluída quando não existir produto não excluído da mesma loja vinculado a ela. Produtos excluídos não bloqueiam a categoria, e a exclusão de produto continua permitida quando ele possui categoria.

## Erros existentes

- 401: Bearer ausente ou inválido.
- 403: papel fora do grupo permitido.
- 422: campos inválidos, categoria fora da loja/inexistente, ou `sku` repetido na loja.
- 409: tentativa de excluir categoria com produto não excluído da mesma loja vinculado.
