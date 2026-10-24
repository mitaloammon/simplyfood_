# Product Scope and Roadmap

## Product direction

SimplyCore is a modular, multi-tenant SaaS ERP platform designed to help small and medium-sized businesses manage operations through a shared application foundation and independently controlled business modules. This is the intended product direction, not a claim that every platform capability is implemented.

The repository currently contains an operational application for `FOOD_SERVICE`. The original SimplyFood name remains in legacy code identifiers, SQL filenames, and historical artifacts.

## Current operational scope

The inspected application includes authentication, categories and products, customers, tables and commands, orders, manual payments, cash shifts and movements, dashboard metrics, and ingredient deduction associated with qualifying order closure. See the [implementation inventory](../implementation/current-state.md) and [route inventory](04-api.md).

## Target platform constraints

- Shared-database logical multi-tenancy; one establishment represents one tenant.
- `establishment_id` is the canonical tenant identifier candidate. Ordinary tenant switching is excluded from the approved model.
- Each operational establishment has exactly one explicitly associated, valid, active principal manager. That identity must never be inferred from role or incidental data.
- Establishment creation, claiming, approval, and operational release are distinct concerns.
- Initial commercial profiles are exclusive `FOOD_SERVICE` and `RETAIL`. Commercial classification is independent from explicit module enablement.
- `RETAIL` payments are reserved for the final planned implementation phase.

These constraints are recorded in [the decision register](../decisions/README.md). Their implementation is incomplete or absent in the inspected source.

## Roadmap status

The existing [delivery notes](07-sprints.md) are historical records, not a commitment or proof of completion. RETAIL sales, inventory ledger, returns, refunds, RETAIL payments, platform administration, principal-manager workflows, and full provisioning/readiness remain unimplemented or unverified in the inspected code.

## Out of current implementation scope

No public user registration, delivery/geolocation, kitchen display system, payment gateway, platform super-admin workflow, or RETAIL checkout was found in the inspected API. Their future inclusion requires product and architecture decisions where not already approved.
