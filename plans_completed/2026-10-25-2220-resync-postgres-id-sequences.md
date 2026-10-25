# Resync Postgres id sequences (SCI-112)

## Context

Nightwatch issue #12 (production): creating a user at `/admin/users/create` returned a 500 with `UniqueConstraintViolationException` on `users_pkey`: `Key (id)=(3) already exists`. The production database was carried over from the previous app with rows at their original ids, but the `*_id_seq` sequences were never advanced, so Postgres hands out ids that are already taken. The same applies to every table whose rows were carried over, not just `users`.

## Linear and branch

- SCI-112 (Admin, Bug), branch `sdevore/sci-112-creating-a-user-in-admin-fails-postgres-id-sequences-behind`.

## What changed

- New migration `database/migrations/2026_10_25_221517_resync_postgres_id_sequences.php`. On Postgres only, it reads every sequence owned by a column (serial or identity) in the current schema from `pg_depend`, and sets each to `max(column) + 1` with `is_called = false` (1 for empty tables). It's idempotent and does nothing on SQLite or MySQL.

## Verification

- Pint, PHPStan (0 errors) and the full Pest suite (304 passed) on SQLite, where the migration returns early.
- The Postgres path has no local coverage (no Postgres in Herd). Verify on staging after deploy: create a user in admin, then for a few tables check that `select last_value from users_id_seq` is at or above `max(id)`.

## Follow-ups

- After the release ships, resolve Nightwatch issue #12.
