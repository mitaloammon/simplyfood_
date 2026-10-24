# Testing and Acceptance Evidence

See [Testing and Verification](../development/testing.md) for observed test configuration, coverage gaps, and safe execution guidance.

The repository contains a small set of backend feature tests and declares Vitest for the frontend. The active database was not inspected, tests were not run, and compatibility between SQLite test configuration and the MySQL-oriented schema/triggers is unverified. Historical acceptance targets such as a coverage percentage are not treated as approved release gates unless separately confirmed.

Release criteria must cover tenant isolation, role checks, authentication and account state, shared readiness, FOOD_SERVICE order/payment/cash/ingredient regressions, concurrent updates, and migration recovery in an isolated environment. RETAIL requires separate acceptance contracts before release.
