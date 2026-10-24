# Customer Operations

Customer reads are declared for `ADMIN`, `MANAGER`, `CASHIER`, and `WAITER`; create, update, and delete routes are declared for `ADMIN`, `MANAGER`, and `WAITER`.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller as CustomerController
    participant Service as CustomerService
    participant DB as Database
    User->>API: Customer request with bearer token
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Validate and execute operation
    Service->>DB: Read or persist customer data
    DB-->>Service: Result
    Service-->>Controller: Result
    Controller-->>User: JSON response
```

Refer to the current service and request validation for exact tenant scoping and soft-delete behavior. Runtime behavior has not been exercised for this documentation update.
