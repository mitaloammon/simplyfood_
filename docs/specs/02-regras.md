# Business Rules and Current Authorization

This page separates approved architectural constraints from behavior found in the current repository. It is not a substitute for database inspection; the active schema has not been confirmed.

## Approved business constraints

- **Tenant boundary:** one establishment per tenant, with shared-database logical isolation and `establishment_id` as the canonical identifier candidate. See [DEC-01](../decisions/README.md#approved).
- **Principal manager:** exactly one valid and active principal manager for each operational establishment, explicitly associated and never inferred. Transfers and replacements must preserve accountable history. Detailed authority and evidence policy remain unresolved.
- **Establishment lifecycle:** approval precedes operational release. Creating a new establishment differs from claiming an existing one. Provisioning is idempotent and auditable; equivalent retries are consistent and divergent payloads for the same key conflict.
- **Commercial profile:** one permitted profile at a time (`FOOD_SERVICE` or `RETAIL`); hybrid operation is excluded. Profile classification does not implicitly enable modules.
- **Legacy association:** fail closed; association requires an explicitly approved and recorded decision for the exact establishment and candidate. Preserve decision history append-only.

These are approved directions, not evidence that the corresponding workflows, schema constraints, or controls are implemented.

## Observed role and route checks

The API uses `EnsureRole` allowlists in `backend/routes/api.php`. The inspected role names are `ADMIN`, `MANAGER`, `CASHIER`, `WAITER`, and `KITCHEN`. `KITCHEN` is accepted by the middleware but is not included in the inspected commercial route groups. No user-administration route or principal-manager capability model was found.

The route grouping observed in source is summarized in [the API inventory](04-api.md). This is a route-level allowlist, not a comprehensive business-capability policy. Do not infer authority beyond the declared routes and middleware.

## Tenant scoping

Many application services derive the establishment from the authenticated user, and tenant-scoped schema fields exist in versioned SQL. Static inspection does not establish complete isolation: every query path, nested relationship, trigger, and foreign key must be checked. Composite tenant-aware relational constraints are not universal in the inspected DDL. See [known limitations](../implementation/known-limitations.md) and [integrity review](../backend/integrity.md).

## Existing FOOD_SERVICE behavior

The current application has workflows for orders, manual payments, shifts, cash movements, and ingredient deduction during eligible order closure. Their exact transition and accounting semantics should be read from application services and versioned MySQL triggers; this page does not elevate them into a new contract. There is no inspected RETAIL inventory or return workflow.

## Authorization boundary

Authentication, account activity, establishment readiness, and role allowlists are separate checks in the current route stack. They do not implement the full approved provisioning and readiness model. The readiness requirement shared by blocking and diagnostic consumers is approved (DEC-06.1.1) but not fully met by the duplicated limited checks currently found. Further readiness decomposition and workflow details remain pending; see [decision status](../decisions/README.md).
