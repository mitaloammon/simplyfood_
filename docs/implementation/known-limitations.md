# Known Limitations and Verification Gaps

These items are based on static repository inspection and the decision context supplied by the product owner. They are not claims about a production incident.

## Architecture and security

- The shared readiness decision DEC-06.1.1 is approved, but the inspected source duplicates a limited `installation_ready` check between middleware and `AuthController::userData`.
- Current readiness only considers active account and active establishment; approval, principal-manager association, commercial profile, module entitlement, and provisioning conflicts are not evaluated there.
- Account inactivity blocks protected requests, but the account-active middleware does not revoke every existing token. Logout attempts to delete the current token only.
- The current schema has `users.establishment_id` and fixed roles but no explicit principal-manager association in the inspected DDL.
- Tenant scoping appears in application services and some triggers. Static inspection does not prove complete cross-tenant isolation, and active schema/triggers were not inspected.
- Role checks do not establish institutional authority to approve or transfer an establishment.

## Data and operational workflows

- Establishment approval, provisioning, principal association, transfer, suspension, audit policy, and recovery are not implemented as the approved lifecycle workflow in the inspected source.
- Commercial profile and module enablement are not represented in the inspected versioned establishment schema.
- Ingredient stock deduction exists in the versioned FOOD_SERVICE trigger path. A general inventory-management API was not found in the current API routes.
- RETAIL workflows and RETAIL payments are not implemented; RETAIL payments remain reserved for the final planned phase.
- The versioned migration runner applies MySQL-oriented SQL and triggers statement by statement; MySQL DDL may not be transactional. Migration recovery has not been validated against an isolated restore.

## Tests and environment

- `backend/phpunit.xml` configures SQLite `:memory:`, while the initial migration executes MySQL-oriented schema and trigger files. Compatibility is unverified; tests were not run as part of this documentation task.
- Existing backend tests do not cover all API routes, cross-tenant access, concurrent workflows, full order/payment/cash behavior, principal provisioning, or RETAIL.
- Vitest is declared in the frontend package manifest, but no frontend test files were found in the inspected source tree.
- The active MySQL server, schema, privileges, triggers, backups, and restoration target were not inspected.
- The local working tree contains pre-existing changes, including a modified PHP Dockerfile specifying PHP 8.4. The repository's intended deployment runtime was not established by this documentation pass.
