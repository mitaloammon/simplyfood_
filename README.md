# SimplyCore

SimplyCore is a modular, multi-tenant SaaS ERP platform designed to help small and medium-sized businesses manage operations through a shared application foundation and independently controlled business modules.

The repository currently contains a FOOD_SERVICE operational application. The broader ERP platform, RETAIL workflows, and several establishment lifecycle controls are architectural targets, not completed features.

## Current implementation

- Laravel 12 API with Sanctum bearer-token authentication.
- Vue 3, TypeScript, Pinia, Vue Router, and Vite single-page application.
- FOOD_SERVICE workflows for catalog, customers, tables and commands, orders, payments, cash shifts, dashboard metrics, and ingredient stock deduction when an eligible order is closed.
- MySQL-oriented schema and triggers, with Redis and Docker Compose present in the development setup.
- Role middleware and establishment-scoped application queries in the current code.

Implementation details and limits are documented in [Current implementation](docs/implementation/current-state.md). The database schema and runtime environment have not been verified against an active deployment as part of this documentation update.

## Architecture and decisions

- [Product scope and roadmap](docs/specs/01-produto.md)
- [Architecture overview](docs/specs/03-arquitetura.md)
- [Decision status register](docs/decisions/README.md)
- [Authentication and authorization](docs/specs/03-arquitetura.md#authentication-and-authorization)
- [Provisioning and readiness](docs/specs/03-arquitetura.md#establishment-provisioning-and-readiness)
- [Known limitations](docs/implementation/known-limitations.md)

The approved direction is shared-database multi-tenancy with one establishment per tenant, using `establishment_id` as the canonical tenant identifier candidate. The principal manager must be explicitly associated; it is never inferred from a user role or incidental data. Commercial profile and module enablement are separate controls.

## Technology and development

The backend declares PHP `^8.2`, Laravel `^12.0`, and Sanctum `^4.0`. The frontend manifest declares Vue 3, TypeScript, Vite, and Vitest. The Compose file specifies MySQL 8.4, Redis 7, and Node 20. The local PHP Dockerfile has an existing uncommitted change to PHP 8.4, so the intended runtime baseline needs confirmation.

- [Development setup](docs/development/setup.md)
- [Testing and verification](docs/development/testing.md)
- [Contribution guidance](docs/AGENTS-backend.md)
- [API routes](docs/specs/04-api.md)
- [Integrity review](docs/backend/integrity.md)

## Documentation map

Start at [the documentation index](docs/README.md). The product specification entry point is [docs/SPEC.md](docs/SPEC.md); detailed operational contracts live under `docs/specs/`.

## Historical material

The original product name, SimplyFood, remains in source identifiers and in historical project artifacts. The untracked Fase 0 and Fase 1 planning reports were present before this task and are preserved unchanged; they are working records, not the canonical decision register. See [decision status](docs/decisions/README.md) for the current status summary.
