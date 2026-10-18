# Remove the Telescope admin link and unused Telescope tables

**Completed:** 2026-10-17 · Branch `feature/2026-10-telescope-nav` (based on `main` after v2026.10.17) · PR #22

## Context
After the v2026.10.17 release, the production admin sidebar showed a **Telescope** link. The user's rule is that Telescope must not be enabled or visible in production. On review, Telescope was never actually installed, so the user asked for the dead link and the unused tables to be removed.

## Findings
- **Not installed:** there's no `laravel/telescope` package, `config/telescope.php` or provider, so `/telescope` returns 404 everywhere and nothing was ever recorded.
- **Why the link showed in production:** its `NavigationItem` called `->visible()` twice, and the second call (`can('view admin panel')`) replaced the first (`environment('local')`).
- **Unused tables:** the 2026-05-03 `create_telescope_entries_table` migration had created `telescope_entries`, `telescope_entries_tags` and `telescope_monitoring`. All three were empty on staging and production (checked 2026-10-17).

## What changed
- 🔥 Removed the Telescope `NavigationItem` from `AdminPanelProvider`. The first commit on this branch fixed its visibility, and the second removes it entirely.
- 🗃️ Deleted the `create_telescope_entries_table` migration and added `2026_10_18_003656_drop_telescope_tables` (up only, `dropIfExists`), which removes the three tables from existing databases.
- Removed the unused `TELESCOPE_ENABLED` env from `phpunit.xml`.
- ✅ `AdminPanelAccessTest`: the admin panel shows no Telescope link in production, staging or local.

## Files touched
`app/Providers/Filament/AdminPanelProvider.php`, `database/migrations/2026_05_03_201007_create_telescope_entries_table.php` (deleted), `database/migrations/2026_10_18_003656_drop_telescope_tables.php`, `phpunit.xml`, `tests/Feature/Filament/AdminPanelAccessTest.php`.

## Verification
- `migrate --pretend` shows the three `drop table if exists` statements.
- `AdminPanelAccessTest` passes (6/6). Full suite passes (278/278, `--no-tia`). PHPStan: 0 errors.

## Deploy notes
- Deploys run `php artisan migrate --force`, so the drop migration runs automatically. The tables were empty.
- `migrate:status` will no longer list the deleted create migration, but its row stays in the `migrations` table. That's harmless.
