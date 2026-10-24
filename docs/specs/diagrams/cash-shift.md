# Cash Shift Operations

The API declares open, current, movement, history, and close routes for the `ADMIN`, `MANAGER`, and `CASHIER` role group. This diagram is a high-level request path only; concurrency and accounting guarantees require separate verification.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Cash as CashController
    participant Service as CashService
    participant DB as Database
    User->>API: Cash operation with bearer token
    API->>Cash: Authenticate, check account and establishment guards, check role
    Cash->>Service: Execute validated operation
    Service->>DB: Read or update establishment-scoped shift data
    DB-->>Service: Operation result
    Service-->>Cash: Result
    Cash-->>User: JSON response
```

For implementation details, inspect `backend/app/Application/Cash/CashService.php` and related SQL. No live database behavior is asserted here.
