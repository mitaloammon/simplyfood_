# Authentication, Session, and Logout

This diagram summarizes the controller and route declarations inspected in source. It is not runtime verification.

```mermaid
sequenceDiagram
    actor User
    participant SPA
    participant API as Laravel API
    participant Auth as AuthController
    participant Store as Token storage
    User->>SPA: Submit credentials
    SPA->>API: POST /auth/login
    API->>Auth: Validate request
    Auth->>Store: Check user and create Sanctum token
    Store-->>Auth: Token result
    Auth-->>SPA: Token and user data or error
    SPA->>API: GET /auth/me with bearer token
    API->>Auth: Authenticate, check account.active
    Auth-->>SPA: Current user data
    SPA->>API: POST /auth/logout with bearer token
    API->>Auth: Authenticate token
    Auth->>Store: Attempt to delete current access token
    Auth-->>SPA: Success response
```

`/auth/me` has `auth:sanctum` and `account.active`. Logout has `auth:sanctum` only. The controller deletes the current token when the authentication context supports it; global token revocation is not implemented by this flow. The frontend keeps the token in module memory according to the inspected helper.
