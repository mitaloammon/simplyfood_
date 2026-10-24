# Order Operations

The API declares order list, create, show, item addition/removal, and status update routes for `ADMIN`, `MANAGER`, `CASHIER`, and `WAITER`.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller as OrderController
    participant Service as OrderService
    participant DB as Database
    User->>API: Submit order operation with bearer token
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Validate and execute operation
    Service->>DB: Read or update order and related records
    DB-->>Service: Result
    Service-->>Controller: Order result
    Controller-->>User: JSON response
```

Order state transitions, snapshots, transaction boundaries, and ingredient deduction are defined across application code and versioned MySQL triggers. Consult those sources; the sequence does not define additional guarantees.
