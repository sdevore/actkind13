# Housekeeping: policy stubs and CLAUDE.md pointer

**Completed:** 2026-10-17 · Branch `feature/2026-10-housekeeping` (first in the October 2026 stack) · Linear SCI-84, SCI-86

## Context
Two leftover follow-ups from the September stack. Generator `//` stub comments were still in the policies (`2026-09-23-1451-controller-cleanup-and-api-split.md`). `CLAUDE.md` also carried a stale copy of the Boost guidelines block next to its `@AGENTS.md` pointer (`2026-09-23-1612-pest-tia-agent-and-phpstan-plugins.md`).

## What changed
- **Policy stubs (SCI-84):** removed the empty `//` lines from `ActPolicy` (8), `InvitationPolicy` (1), `CommentPolicy` (1) and `ContactUsPolicy` (6). The last two weren't in the issue but had the same stubs. Behaviour is unchanged.
- **CLAUDE.md (SCI-86):** cut down to the one-line `See @AGENTS.md …` pointer, matching `GEMINI.md`.

## Files touched
`app/Policies/{ActPolicy,InvitationPolicy,CommentPolicy,ContactUsPolicy}.php`, `CLAUDE.md`.

## Verification
- Pint passed. PHPStan: 0 errors. `tests/Feature/Policies`: 15/15.

## Follow-ups
- `php artisan boost:install`/`boost:update` adds the full guidelines block back to `CLAUDE.md` (see `.ai/rules/general.md`). Re-apply the pointer after any Boost sync.
