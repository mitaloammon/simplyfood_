# Architecture, Authentication, and Readiness

## Product and implementation boundary

SimplyCore is the intended modular, multi-tenant ERP platform. The repository currently implements a FOOD_SERVICE application, not the complete target platform. The [decision register](../decisions/README.md) records approved constraints and pending proposals; the [implementation inventory](../implementation/current-state.md) records observed source behavior.

## Technology observed in repository declarations

- Backend manifests declare PHP `^8.2`, Laravel `^12.0`, and Sanctum `^4.0`.
- Frontend manifests declare Vue 3, TypeScript, Pinia, Vue Router, Vite, and Vitest.
- Docker Compose declares MySQL 8.4, Redis 7, and Node 20.
- The working tree contains a pre-existing uncommitted PHP Dockerfile change specifying PHP 8.4. The intended image/runtime baseline is therefore not established by this documentation.

These declarations do not establish deployment configuration or the identity/schema of any active database.

## Multi-tenancy

**Approved direction (DEC-01):** shared-database logical isolation, exactly one establishment per tenant, with `establishment_id` as the canonical tenant identifier candidate and no ordinary tenant switching. A generic `tenants` table or parallel `tenant_id` is not part of the approved direction absent an explicitly approved architectural deviation.

**Observed implementation:** users and operational data use establishment identifiers in the inspected code and versioned SQL. Comprehensive isolation, relational enforcement, and the active database state have not been proven. See [integrity review](../backend/integrity.md).

## Authentication and authorization

The inspected API authenticates bearer tokens with Laravel Sanctum. Login checks account status and password before issuing a token. The `account.active` middleware is registered and applied to `/auth/me` and the commercial route group. `installation.ready` is then applied to the commercial group, followed by rate limiting and role allowlists. Actual route groups are listed in [API routes](04-api.md).

`EnsureAccountActive` blocks protected requests for an inactive account. It does not revoke every token. Logout is authenticated but is outside the account/readiness guards; the controller attempts to delete only `currentAccessToken()`. Whether a prior token can be reused after account reactivation depends on token validity and revocation state. Global revocation and session invalidation are not implemented by the inspected code.

The frontend token helper stores the bearer token in module memory and removes a legacy `localStorage.token` during startup. Static inspection does not establish browser/runtime security behavior.

## Establishment readiness

**Approved DEC-06.1.1:** blocking and diagnostic consumers must use one shared readiness evaluation, distinct from authorization to perform an action.

**Observed source:** `EnsureInstallationReady` checks that the authenticated user has an establishment whose status is `ACTIVE`. `AuthController::userData` independently calculates `installation_ready` with a similar query. These checks are duplicated and limited; they do not evaluate establishment approval, explicit principal-manager association, commercial profile, module entitlement, or provisioning conflicts. The approved shared evaluation is therefore not fully implemented.

## Principal manager, profiles, modules, and provisioning

DEC-02 through DEC-05 require explicit principal association, approved establishment lifecycle and provisioning, exclusive commercial classification, independent module enablement, and controlled fail-closed legacy migration. The inspected application schema and routes do not implement these full workflows. RETAIL payments remain deferred to the final planned phase.

## Pending readiness/workflow decisions

DEC-06.1.2 and DEC-06.2 remain pending. Proposed readiness layers, workflow state machines, audit policy, authority, evidence rules, partial-failure behavior, and recovery must not be treated as approved implementation contracts.

There is an unresolved specification conflict: the older architecture document described detailed audit as outside the MVP, while approved DEC-02, DEC-03, and DEC-05 require critical associations, provisioning, transfers, and migration decisions to be auditable. The scope and minimum audit guarantees require explicit reconciliation; this document does not resolve the conflict.

## HTTP envelope and operational assumptions

Routes return JSON in the inspected controllers, but there is no evidence here that every endpoint follows one uniform envelope. Rate limits and authentication middleware should be read from route declarations. Pagination, latency targets, CORS policy, password hashing algorithm, queue behavior, and realtime guarantees are not asserted unless verified in code/configuration. REST is the current API shape; no realtime dependency is documented as implemented.
