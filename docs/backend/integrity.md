# Database Integrity Review

This document records static observations from versioned SQL and application code. It does not describe an active database: the target server and schema have not been inspected. The SQL files under `backend/database/sql/` and their migration runner are the relevant artifacts; compare them with any deployed schema before planning changes.

## Observed schema characteristics

The versioned schema defines UUID-keyed establishment-scoped records, foreign keys, unique constraints for selected business keys, and MySQL-specific enums/checks/triggers. `product_ingredients` uses a composite key. The details must be verified against the current SQL files because this document is not a generated schema inventory.

The inspected DDL does not consistently encode same-establishment membership in relationships as composite foreign keys. Application-level establishment filters therefore remain important, while database-level cross-tenant relational integrity is not universally enforced by the versioned schema. Whether a particular relationship permits cross-establishment references must be confirmed against its exact DDL and current database.

Some operational references have no database foreign key in the versioned SQL; soft deletion also does not by itself prevent new references to a soft-deleted row. These conditions need explicit domain policy and schema review before introducing new workflows.

## Risks requiring design and verification

- Cross-establishment relationships can undermine logical isolation if a write path validates each ID independently or omits tenant scoping.
- Concurrent operations require transaction boundaries and consistent row-lock ordering; uniqueness constraints alone do not define business outcomes.
- Financial, stock, order, and audit records require retention and deletion rules that preserve history.
- MySQL DDL and trigger behavior may not be transactional. The current migration runner's partial-application recovery must be tested on an isolated restored database.
- The active engine/version, SQL mode, installed triggers, constraints, and migration history are unknown until inspected with authorization.

## Review proposals, not applied migrations

Tenant-aware composite foreign keys (for example, a unique parent key on `(id, establishment_id)` paired with a child foreign key on `(parent_id, establishment_id)`) are a candidate defense in depth. Before proposing an ALTER, inventory existing rows for violations, index/constraint compatibility, nullable relationships, migration ordering, lock/downtime requirements, and rollback/recovery behavior.

Do not apply schema changes by copying examples from historical documentation. No ALTER statements are prescribed here. Reconcile any proposal with DEC-01 and obtain approval before implementation.

## Application-level invariants

State transitions, authorization, payment sufficiency, cash closure, stock non-negativity, and soft-delete policy generally require application/service logic and transactions, potentially together with constraints or triggers. Verify the implemented behavior in services, SQL, and tests; this page does not claim complete enforcement.

## Evidence and next verification

- Versioned schema: `backend/database/sql/simplyfood-db.sql`
- Versioned triggers: `backend/database/sql/simplyfood-triggers.sql`
- Migration wrappers and ordering: `backend/database/migrations/`
- Current route and service behavior: `backend/routes/api.php` and `backend/app/Application/`

The authorized Phase 0 runbook must inspect the effective server/schema, privileges, constraints, triggers, and migration history, then compare those results with versioned SQL. Backup and isolated restoration require separate authorization.
