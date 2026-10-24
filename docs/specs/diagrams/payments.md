# FOOD_SERVICE Payment Registration

The inspected route declares `POST /orders/{order}/payments` for `ADMIN`, `MANAGER`, and `CASHIER`. This is the existing manual FOOD_SERVICE payment path, not a gateway integration or RETAIL payment feature.

```mermaid
sequenceDiagram
    actor User
    participant API
    participant Controller as PaymentController
    participant Service as PaymentService
    participant DB as Database
    User->>API: Submit payment request with bearer token
    API->>Controller: Authenticate and apply route guards
    Controller->>Service: Validate and execute payment operation
    Service->>DB: Read order/shift and persist payment data
    DB-->>Service: Result
    Service-->>Controller: Payment result
    Controller-->>User: JSON response
```

Payment eligibility, transactionality, cash accounting, and order completion semantics must be verified in the service and SQL. This diagram does not imply a payment gateway or define a new financial contract.
