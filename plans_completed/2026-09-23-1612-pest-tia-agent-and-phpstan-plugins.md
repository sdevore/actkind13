# Pest 5 tooling: Tia Engine, Agent plugin, PHPStan plugin

**Completed:** 2026-09-23 · Branch `feature/2026-09-pest-tooling` (stacked on `feature/2026-09-admin-moderation-tests`)

## Context
The user asked for Pest 5's Tia Engine (test impact analysis) to be set up so it works with everyday commands on Laravel Herd, but **never in CI**. They also asked to add `pestphp/pest-plugin-agent` and `pestphp/pest-plugin-phpstan`.

## What changed
- **⚡️ TIA locally:** `pest()->tia()->locally()` in `tests/Pest.php`.
- **🔧 `composer test`** now runs `herd debug -d xdebug.mode=coverage vendor/bin/pest`. TIA needs a coverage driver to record which tests touch which files, and Herd only loads Xdebug on demand. It calls `vendor/bin/pest` directly because `php artisan test` runs Pest in a child process that doesn't inherit Herd's `-d` flags. Through artisan, TIA can only replay and falls back to a full run whenever it needs to re-record.
- **👷 CI:** `tests.yml` runs `./vendor/bin/pest --ci`. This matters: with `locally()`, a run with `CI=true` alone still replayed from cache in testing. Only `--ci` forces the full suite.
- **➕ `pest-plugin-agent` (v5.0.0)** adds `pest --agent='<snippet>'` probes. Its Boost guidelines and skill are installed through `boost.json` and `boost:update` (the `.claude`, `.github` and `.junie` skills, plus `AGENTS.md`).
- **➕ `pest-plugin-phpstan` (v5.2.1):** PHPStan now analyses `tests/` as well as `app/`. That surfaced 129 errors, fixed at the source:
  - `@use HasFactory<XFactory>` and `@extends Factory<X>` on 6 models and 5 factories, following the existing `User` and `ContactUs` convention. This cleared about 120 of them.
  - `Act::$type` documented as `ActType`, the enum it's cast to.
  - `flag()` return types narrowed from `Flag|Model|bool` to `Flag|false`.
  - Two redundant assertions removed. The placeholder `tests/Unit/ExampleTest.php` was deleted, as the user approved.
- **📝 Docs:** `AGENTS.md` and `.ai/rules/app.md` now describe PHPStan covering tests and the factory-generics trap. A new `AGENTS.md` section explains running tests through Herd's Xdebug for TIA.

## Verification
- Recording a baseline takes about 2.5 minutes (Xdebug coverage). With nothing changed, a replay runs all 205 tests in about 0.4s, and `composer test` finishes in about 4s end to end.
- After a real PHP change, a run without a coverage driver warns and runs the full suite (safe fallback).
- `pest --agent` works: an authenticated `/acts/mine` probe passes, a guest probe fails, and the temporary test file is cleaned up.
- PHPStan: 0 errors across `app/` and `tests/`. Full suite: 205/205 (206 minus the placeholder).

## Follow-ups
- Optionally, `pestphp/pest-plugin-browser` (needs Node and Playwright) would let `--agent` probes drive a real browser. It's not installed; the plugin's guidance says to ask first.
- The TIA graph is stored per machine in `~/.pest/tia/`. Each developer records their own baseline on first run, since a shared CI baseline was ruled out.
- `CLAUDE.md` and `GEMINI.md` still carry a duplicated Boost block (see `.ai/rules/general.md`), so the new agent-plugin rules reach them only through the `@AGENTS.md` pointer.
