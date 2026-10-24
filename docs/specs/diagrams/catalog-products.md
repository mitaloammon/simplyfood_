# Catalog Operations

Catalog read routes are available to `ADMIN`, `MANAGER`, `CASHIER`, and `WAITER`; catalog mutations are declared for `ADMIN` and `MANAGER`.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller
    participant Service
    participant DB as Database
    User->>API: Read or mutate category/product
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Validate and invoke operation
    Service->>DB: Query or persist catalog records
    DB-->>Service: Result
    Service-->>Controller: Result
    Controller-->>User: JSON response
```

Tenant scoping and product/category relationship validation must be confirmed in the relevant request, service, and model code. The diagram does not claim database-level tenant integrity.
