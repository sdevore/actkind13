# Plan: work through the ActKind backlog (October 2026)

**Status (2026-10-18): done.** Everything planned is shipped to production in releases `v2026.10.17`, `v2026.10.17.1` and `v2026.10.17.2`. SCI-77 (Cloudflare dashboard, user action) is still open, and SCI-89 is waiting on its follow-up check.

## Context
The September stack (PRs #3–#16) was merged and deployed to production. Its follow-ups were filed in Linear as SCI-76…SCI-86, plus an older Nightwatch issue, SCI-48. This plan grouped them into a production-verification pass and then a new stack of branches.

Decisions made: production was already deployed, so the production checks went first. SCI-78: **yes**, `Appreciate` gets `SoftDeletes` and is restored with its act or comment.

## Group 0: Production verification
| Item | Outcome |
|---|---|
| SCI-76 XSS content scan | ✅ **Done.** Read-only scan on 2026-10-18: 4 acts, 0 comments and 9 invitations, no matches. Closed. |
| SCI-77 Cloudflare HIT / httponly cookie | ⚠️ **Open (user action).** The origin is correct (`public, s-maxage=300`, and the mystery cookie is gone), but Cloudflare still reports `DYNAMIC`. The old `/` 31-day rule is active and the "Cache guest pages" rule is missing. Steps are in a Linear comment. |
| SCI-48 `ray()` undefined | ✅ **Done.** Nightwatch shows it was last seen 134 days ago, with 0 occurrences in 14 days. The call was removed from the contact form. Closed. |

## Stack (merged and released)
| Branch / PR | Issues | Outcome |
|---|---|---|
| A. housekeeping, #17 | SCI-84, SCI-86 | ✅ Policy stubs removed (also `CommentPolicy` and `ContactUsPolicy`). `CLAUDE.md` is now a pointer. |
| B. admin-email, #18 | SCI-79, SCI-81, SCI-80 | ✅ `UserPolicy::emailAny` plus `->authorize()`. Bulk email is queued. Invitations BCC only `MAIL_INVITATIONS_BCC`. A production bulk email was received. |
| C. appreciate-soft-deletes, #19 | SCI-78 | ✅ Migration added. Act and comment restores cascade to comments, appreciations and flags deleted at the same time. API un-appreciate force-deletes. |
| D. appreciation-sparkline, #20 | SCI-83 | ✅ 14-day daily sparkline built with `flowframe/laravel-trend` (the plan had wrongly said no trend helper existed). |

Merge order used: #21 → #17 → #18 → #19 → #20. No merge deployed; shipped as **`v2026.10.17`**. The full suite passed (275 tests at release, 278 after the follow-ups), and PHPStan reports 0 errors.

## Work added during execution
- **#21 release-only production deploys:** `main_pipeline.yml` deploys only on a published, non-pre-release release targeting `main`. Cloud push-to-deploy is off for `main` (changed by the user in the dashboard). #21 was merged first so the stack's merges didn't deploy.
- **Queue workers:** neither environment had one, and invitations were already queued. A `queue:work database --tries=3 --backoff=30` worker was added to staging, then to production at release. Both were verified with a queued `cache:forget` job.
- **Cloud build and deploy commands (both environments):** `optimize` and `filament:optimize` moved to the build step. The deploy step now only runs `migrate --force`. Config, routes, views and events are now cached in production.
- **Staging environment:** `APP_ENV=staging`, its own `NIGHTWATCH_TOKEN`, and `NIGHTWATCH_SERVER=develop`. Production has `NIGHTWATCH_SERVER=main`.
- **#22 Telescope (`v2026.10.17.1`):** removed the admin link, which a double `->visible()` call had made visible in production. Dropped the three empty `telescope_*` tables (new migration; the old one was deleted). Also cleaned up locally.
- **#23 Acts chart GROUP BY (`v2026.10.17.2`, user's branch, SCI-90):** verified on production Postgres: the old query shape fails and the fixed one works.
- **Linear:** SCI-88 (a test-job error I caused on staging) was canceled. SCI-89 was created for separate staging Nightwatch.

## Files touched
Per branch, see the individual records:
- `2026-10-17-1625-housekeeping-policy-stubs-and-claude-md.md`
- `2026-10-17-1650-admin-email-authorize-queue-and-invitation-copies.md`
- `2026-10-17-1715-appreciate-soft-deletes-and-restore-cascade.md`
- `2026-10-17-1740-appreciation-sparkline.md`
- `2026-10-17-1815-production-deploys-on-release.md`
- `2026-10-17-1945-remove-telescope-link-and-tables.md`
- `2026-10-17-1747-acts-chart-grouping-error.md`

Infrastructure changes made outside the repo (Laravel Cloud, both environments): build and deploy commands, queue workers, `APP_ENV` (staging), `NIGHTWATCH_TOKEN` (staging) and `NIGHTWATCH_SERVER`.

## Verification
- CI passed on every PR and on each release run. The full local suite passed (278/278, `--no-tia`) and PHPStan reported 0 errors.
- After each release, production was checked:
  - `migrate:status` shows the October migrations as run;
  - config, routes, views and events are cached;
  - one `queue:work` process is running, with no failed jobs;
  - the public pages and the admin login return 200.
- Production checks:
  - an admin bulk email to the user's own account was queued, processed and received;
  - the Acts chart query fails on Postgres without the fix and succeeds with it;
  - the XSS scan found no matches.

## Still open
- **SCI-77:** Cloudflare dashboard changes (user).
- **SCI-89:** staging requests aren't appearing in Nightwatch Develop yet, although commands are. A one-off check is scheduled for 2026-10-18 at 9:07am, in this session only.
- **Deferred as planned:** SCI-85 (Playwright in CI, with the first permanent browser test) and SCI-82 (v2 API).

## Housekeeping
- Local branches were renamed `feature/2026-10-*` → `merged/2026-10-*`. Remote branches were left alone, at the user's request.
