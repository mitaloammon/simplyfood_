# Tables and Commands

The API declares read routes for `ADMIN`, `MANAGER`, `CASHIER`, and `WAITER`. Mutations are declared for `ADMIN`, `MANAGER`, and `WAITER`.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller as Table/Command controller
    participant Service as Table/Command service
    participant DB as Database
    User->>API: Submit table or command operation
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Validate and execute operation
    Service->>DB: Read or update operational records
    DB-->>Service: Result
    Service-->>Controller: Result
    Controller-->>User: JSON response
```

Check the relevant services and schema for exact state transitions and relationship rules. No cross-tenant database guarantee is inferred from route middleware alone.
