# Current Implementation Inventory

This inventory is based on static inspection of the repository. It does not establish behavior in a running environment or prove that the active database matches versioned SQL.

## Implemented in the inspected source

| Area | Observed implementation | Evidence |
|---|---|---|
| Authentication | Email/password login checks `users.status = ACTIVE` and creates a Sanctum personal access token. Logout attempts to delete the current access token. `/auth/me` returns current user data. | `backend/app/Http/Controllers/Api/AuthController.php`; `backend/app/Models/User.php` |
| Account guard | `EnsureAccountActive` rejects inactive or missing authenticated accounts with `401 ACCOUNT_INACTIVE`. It is registered as `account.active`. | `backend/app/Http/Middleware/EnsureAccountActive.php`; `backend/bootstrap/app.php` |
| Establishment guard | `EnsureInstallationReady` checks that the authenticated user's establishment exists and is `ACTIVE`; otherwise it returns `409 ESTABLISHMENT_SETUP_REQUIRED`. | `backend/app/Http/Middleware/EnsureInstallationReady.php` |
| Routes and role checks | Current commercial API routes use Sanctum, account-active, and installation-ready middleware, then role groups. `/health` and login are public; logout uses Sanctum only; `/auth/me` uses Sanctum and account-active. | `backend/routes/api.php`; `backend/app/Http/Middleware/EnsureRole.php` |
| FOOD_SERVICE catalog/customers/tables/commands | API controllers, request validation, application services, models, and Vue pages are present. Services commonly derive `establishment_id` from the authenticated user. | `backend/app/Application/`; `backend/app/Http/Requests/`; `frontend/src/modules/` |
| Orders and ingredients | Order service manages existing order transitions and snapshots product price into order items. Versioned MySQL triggers validate transitions and deduct recipe ingredients when an order closes. | `backend/app/Application/Orders/OrderService.php`; `backend/database/sql/simplyfood-triggers.sql` |
| Payments and cash | Manual payment registration and cash-shift operations are implemented in application services. This is not evidence of a payment gateway or a complete ledger/audit subsystem. | `backend/app/Application/Payments/PaymentService.php`; `backend/app/Application/Cash/CashService.php` |
| Schema and token storage | Versioned SQL defines establishment-scoped operational tables; migrations execute SQL files, including trigger definitions. A separate migration creates Sanctum personal access tokens. | `backend/database/sql/`; `backend/database/migrations/` |
| Frontend session handling | The SPA keeps its bearer token in module memory and removes a legacy `localStorage.token` at startup. Reloading loses in-memory auth state. | `frontend/src/main.ts`; `frontend/src/shared/api/token.ts`; `frontend/src/shared/api/client.ts` |

## Partially implemented or not established

- The current readiness predicate is not the full establishment lifecycle/readiness model in DEC-01 through DEC-06. It checks account and establishment activity only.
- `EnsureInstallationReady` and `AuthController::userData` calculate establishment readiness separately. Therefore, the approved shared evaluation is not fully satisfied by the inspected source.
- Role middleware is a fixed role allowlist; no principal-manager relationship or policy-based capability model was found in the inspected model/schema/routes.
- The SQL and code demonstrate establishment filters in many paths, but do not prove complete isolation across all current and future query paths. Composite tenant-aware foreign-key coverage is not universal in the inspected DDL.
- Inventory tables and ingredient-deduction triggers exist in versioned SQL, but no inventory-management API routes were found in `backend/routes/api.php`.
- The tests present are a small set of backend feature tests. No frontend test files were found in `frontend/src` during this inspection.

## Planned, pending, or not implemented in inspected source

- Explicit principal-manager association and controlled transfer workflow.
- Approved establishment provisioning workflow and shared readiness evaluator.
- Commercial-profile storage and independent module-entitlement controls.
- RETAIL sales, inventory ledger, returns, refunds, and RETAIL payments.
- Platform administration and user-management API routes.
- Complete migration/reconciliation controls for legacy principals.

These items are not claimed as implemented merely because they appear in planning documents.
