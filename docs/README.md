# SimplyCore Documentation

This index points to the current documentation sources. The code, tests, and versioned SQL remain the evidence for implemented behavior; architectural decisions describe intended constraints and do not prove implementation.

## Product and architecture

- [Canonical specification](SPEC.md)
- [Product scope and roadmap](specs/01-produto.md)
- [Business rules and current authorization](specs/02-regras.md)
- [Architecture and security boundaries](specs/03-arquitetura.md)
- [API route inventory](specs/04-api.md)
- [Decision status register](decisions/README.md)

## Implementation and development

- [Current implementation](implementation/current-state.md)
- [Known limitations](implementation/known-limitations.md)
- [Development setup](development/setup.md)
- [Testing and verification](development/testing.md)
- [Backend contribution guidance](AGENTS-backend.md)
- [Database integrity review](backend/integrity.md)
- [Use-case diagrams](specs/diagrams/00-index.md)

## Historical and supporting records

- [Changelog](CHANGELOG.md)
- [Fictional log examples](examples/README.md)
- [Design system reference](specs/05-design-system.md)
- [Historical implementation stages](specs/07-sprints.md)
- [Design and writing references](specs/08-skills.md)
- [Fase 0 audit report](auditoria-fase-0.md) and [Fase 1 planning report](fase-1-contratos-e-plano-tecnico.md) are preserved working artifacts. They contain historical proposals and are not approval records or sources of current implementation truth.

## Status conventions

- **Approved decision:** a project constraint confirmed by the owner.
- **Proposed / pending:** a recommendation awaiting explicit approval.
- **Implemented:** supported by current source code or tests; runtime behavior is not implied unless executed and recorded.
- **Unverified:** cannot be established from the permitted static evidence.
