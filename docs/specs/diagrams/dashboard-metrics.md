# Dashboard Metrics

`GET /dashboard/metrics` is declared for the `ADMIN`, `MANAGER`, `CASHIER`, and `WAITER` route group.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller as DashboardController
    participant Service as DashboardMetricsService
    participant DB as Database
    User->>API: GET /dashboard/metrics with bearer token
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Request metrics for authenticated user
    Service->>DB: Aggregate operational data
    DB-->>Service: Aggregate results
    Service-->>Controller: Metrics
    Controller-->>User: JSON response
```

The endpoint and service are present in the inspected source. Exact metric semantics and tenant coverage should be confirmed in `backend/app/Application/` and tests; this diagram does not prove runtime results.
