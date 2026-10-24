# Changelog

This file preserves selected historical project notes. It is not a complete release history. Current contracts are in [SPEC.md](SPEC.md), and approval status is in the [decision register](decisions/README.md).

## 2026-08-28 — Historical updates after the initial delivery sequence

### Authentication and frontend session handling

- The SPA bearer token was changed to in-memory storage.
- Startup removes a legacy token stored in `localStorage`.
- This frontend behavior does not imply global server-side token revocation.

### Interface

- The login layout and operational shell received visual updates.
- Inter was recorded as the global frontend font.
- Shared visual tokens, responsive navigation, concise operational actions, and stronger text contrast were recorded.

### Payments

- `payment_method` was consolidated as the payment-method field.
- `CASH` was recorded as supported for partial and full payments.

These are historical notes and should be rechecked against current source before being used as implementation guarantees. SimplyFood is the legacy product name and remains in technical identifiers and historical records; SimplyCore is the current documentation name.
