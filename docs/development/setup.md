# Development Setup

This page records verified repository entry points without assuming that a local environment or database is authorized or safe to modify.

## Declared stack

The backend manifests declare PHP `^8.2`, Laravel `^12.0`, and Sanctum `^4.0`. The frontend declares Vue 3, TypeScript, Pinia, Vue Router, Vite, and Vitest. Docker Compose declares MySQL 8.4, Redis 7, and Node 20. A pre-existing uncommitted change sets the PHP Dockerfile base to 8.4, so confirm the intended runtime baseline before using that image.

## Before starting services

1. Review the root Compose file and application-specific configuration locally.
2. Confirm the intended target and authorization before connecting to any database. A Compose service name or `.env` file does not establish the identity of the server/schema.
3. Do not run destructive reset commands such as `migrate:fresh` against a database with data to preserve.
4. The active MySQL instance, schema, backup, restoration target, and credentials were not inspected or validated by this documentation task.

No setup command is prescribed here because the safe target and current local configuration have not been confirmed. Use the repository's maintained scripts only after confirming their effects and destination.
