# Backend Engineering Guide

Before changing backend behavior, read the [SimplyCore specification](SPEC.md), the [decision register](decisions/README.md), and the relevant source code. The specification distinguishes approved constraints from implementation facts and pending proposals.

## Working principles

1. Derive tenant context from the authenticated actor and verify establishment scoping through the complete query and relationship path.
2. Treat route middleware as one authorization layer; verify policy and resource-level checks where applicable.
3. Keep HTTP controllers focused and domain/application behavior in the existing service structure.
4. Preserve FOOD_SERVICE behavior unless an approved change explicitly calls for a change and regression coverage is provided.
5. Do not infer principal-manager identity, commercial profile, module entitlement, or readiness from incidental fields.
6. Do not describe a proposed contract as approved. Keep architecture decisions and implementation state distinct.
7. Before database work, confirm the target, backup/recovery plan, engine compatibility, and effects of migrations. Never assume the active schema matches versioned SQL.
8. Update canonical documentation when an approved behavior changes; retain historical identifiers such as `SimplyFood` in code unless a separate implementation task authorizes renaming.

## Evidence

Use route declarations, middleware, requests, controllers, services, models, migrations, SQL, and tests as appropriate. Static inspection does not establish runtime behavior or active database state. See [current implementation](implementation/current-state.md) and [testing guidance](development/testing.md).
