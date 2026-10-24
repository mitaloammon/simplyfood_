# Architecture Decision Status

This register summarizes the decision status supplied by the product owner and the current project context. It is not a substitute for original decision records. No approval date or approving authority beyond the recorded owner decision is inferred here.

## Approved

| Decision | Approved direction |
|---|---|
| **DEC-01 — Multi-tenancy** | Shared-database SaaS with logical isolation; one establishment per tenant; ordinary operational tenant switching is excluded; `establishment_id` is the canonical tenant identifier candidate. Do not add a generic `tenants` table or parallel `tenant_id` without explicit justification and approval. |
| **DEC-02 — Principal manager** | Each operational establishment has exactly one valid, active, explicitly associated principal manager. Never infer this identity from role, activity, age of record, user count, seed data, or another indirect attribute. Preserve historical authorship. |
| **DEC-03 — Establishment approval and provisioning** | Approval precedes operational release. New establishment creation, claiming an existing establishment, and transfer are distinct processes. Provisioning must be idempotent and auditable; same-key/equivalent-payload retries are consistent; divergent payloads conflict; partial failures never release prematurely. |
| **DEC-04 — Commercial profiles and modules** | Initial profiles are exclusive `FOOD_SERVICE` and `RETAIL`; hybrid operation is excluded. Profile classification is separate from module enablement and cannot enable modules automatically. RETAIL payments remain scheduled for the final phase. |
| **DEC-05 — Controlled legacy migration** | Migration is fail-closed. Automated principal association requires a prior explicit decision recorded for the exact establishment and candidate. Conflicts, insufficient evidence, schema divergence, invalid relationships, audit failures, disputes, or critical technical failures block association or release as applicable. Preserve decision history append-only. |
| **DEC-06.1.1 — Shared readiness evaluation** | Use a shared readiness evaluation for blocking and diagnostic consumers, separate from authorization of each action. The owner explicitly approved this decision in the project conversation. |

## Proposed / pending

| Decision | Status and boundary |
|---|---|
| **DEC-06.1.2 — Global and operation-specific requirements** | **Pending.** A proposal separates establishment readiness, actor/request validity, operation/resource eligibility, and transversal controls. Do not present the proposed classifications or failure policy as approved implementation requirements. |
| **DEC-06.2 — Operational workflow** | **Pending.** Workflow states, authorities, evidence policy, audit events, partial-failure handling, reconciliation, and recovery remain undecided. |

## Open questions

- Which readiness conditions are establishment-wide versus actor-, operation-, or resource-specific?
- Who may approve, provision, verify, suspend, reactivate, transfer, and reconcile an establishment?
- What evidence is acceptable, who can access it, and how is it retained?
- Which events require mandatory audit, and what happens if that audit cannot be recorded?
- What workflow states and transitions are needed for the first release?
- How should account deactivation and principal replacement affect existing sessions?

## Documentation and implementation status

Approved decisions define constraints; they do not prove those constraints are implemented. The [current implementation inventory](../implementation/current-state.md) records observed source behavior. In particular, the current readiness middleware checks account and establishment status; it does not implement the full approved shared-evaluation contract for both blocking and diagnosis.

The historical, untracked planning reports [`auditoria-fase-0.md`](../auditoria-fase-0.md) and [`fase-1-contratos-e-plano-tecnico.md`](../fase-1-contratos-e-plano-tecnico.md) are preserved unchanged. Their recommendations must not be read as approval of DEC-06.1.2 or DEC-06.2.
