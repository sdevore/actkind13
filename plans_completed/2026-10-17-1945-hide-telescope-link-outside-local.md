# Hide the admin Telescope link outside local

**Completed:** 2026-10-17 · Branch `feature/2026-10-telescope-nav` (based on `main` after v2026.10.17)

## Context
After the v2026.10.17 release, the production admin sidebar showed a **Telescope** link. The user's rule is that Telescope must not be enabled or visible in production.

## Findings
- **Telescope isn't installed:** there's no `laravel/telescope` package, no `config/telescope.php` and no provider, so nothing is recorded and `/telescope` returns 404 on production. Only the link leaked.
- **Cause:** the link's `NavigationItem` in `AdminPanelProvider` called `->visible()` twice. The second call (`can('view admin panel')`) replaced the first (`environment('local')`), so every admin saw the link in every environment.

## What changed
- A single `->visible()` now requires both conditions: the `local` environment **and** `view admin panel`.
- A test in `AdminPanelAccessTest` checks that the link is hidden in production and staging and shown in local.

## Files touched
`app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/Filament/AdminPanelAccessTest.php`.

## Verification
- The new test fails on the old code (production and staging cases) and passes with the fix. `AdminPanelAccessTest` passes (6/6). PHPStan: 0 errors.

## Follow-ups
- Telescope isn't installed locally either, so the link 404s everywhere. Remove it, or install Telescope as a dev-only dependency.
- The `create_telescope_entries_table` migration (2026-05-03) is still in the repo and has run in both environments, leaving an unused `telescope_entries` table.
