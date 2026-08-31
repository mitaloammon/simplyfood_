# Autenticar usuário

## Ator e papéis permitidos / 403

Login: usuário com cadastro `ACTIVE`, sem Bearer prévio. `me` e logout: `ADMIN`, `MANAGER`, `CASHIER`, `WAITER` e `KITCHEN` autenticados. Estas rotas não aplicam middleware de papel; não há 403 por papel.

## Pré-condições

- Login recebe `email` e `password`.
- `me` e logout recebem `Authorization: Bearer {token}`.
- A SPA mantém o token Sanctum somente em memória. `localStorage` e `sessionStorage` são proibidos; recarregar a página encerra a sessão local.

## Rotas canônicas

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

## Sequência

```mermaid
sequenceDiagram
    actor U as Usuário
    participant SPA
    participant R as Rota auth
    participant C as AuthController
    participant DB as MySQL
    U->>SPA: Informa email e password
    SPA->>R: POST /api/auth/login
    R->>C: login(request)
    C->>DB: Busca user por email e valida status/password
    DB-->>C: user
    C->>DB: Cria token Sanctum "spa"
    DB-->>C: plainTextToken
    C-->>SPA: envelope JSON com token, token_type e user
    Note over SPA: Token mantido somente em memória
    SPA->>R: GET /api/auth/me + Bearer
    R->>C: me(request)
    C-->>SPA: envelope JSON com user
    SPA->>R: POST /api/auth/logout + Bearer
    R->>C: logout(request)
    C->>DB: Exclui currentAccessToken
    DB-->>C: concluído
    C-->>SPA: envelope JSON, data null
```

`AuthController` executa este fluxo diretamente; não existe Service de autenticação.

## Pós-condição

No login, um token Sanctum é criado e fica em memória na SPA. No logout, o token atual é revogado e removido da memória.

## Erros existentes

- 401: credenciais inválidas, usuário inativo ou Bearer ausente/inválido nas rotas autenticadas.
- 422: `email` ou `password` ausente/inválido.
- 403 e 409: não são produzidos pelas regras deste caso de uso.
