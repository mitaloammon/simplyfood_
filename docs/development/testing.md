# Testing and Verification

## Evidence available from static inspection

- Backend feature tests exist for authentication, category deletion, and health checks.
- `backend/phpunit.xml` configures SQLite in-memory, while the migration path executes MySQL-oriented schema and trigger SQL. Compatibility has not been verified.
- Vitest is declared in the frontend manifest; no frontend test files were found in the inspected source tree.

No tests, builds, migrations, or database operations were run for this documentation task.

## Verification gaps

Before relying on the suite as release evidence, establish a safe isolated test database and verify its schema matches the intended engine. Minimum missing coverage includes route-level role authorization, cross-establishment access, account deactivation after token issue, shared readiness, order/payment/cash regressions, tenant-aware relational integrity, concurrent operations, and migration partial-failure recovery. RETAIL-specific acceptance must be defined and tested before RETAIL release.

## Safe test execution

Confirm the test configuration, database target, migration effects, and isolation before executing a test command. Do not point destructive test setup at a shared or production database. Test execution and runtime claims must be recorded separately from static source inspection.
