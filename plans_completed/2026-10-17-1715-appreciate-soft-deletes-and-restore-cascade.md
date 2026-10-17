# Soft-delete appreciations and cascade restores

**Completed:** 2026-10-17 · Branch `feature/2026-10-appreciate-soft-deletes` (stacked on `feature/2026-10-admin-email`) · Linear SCI-78

## Context
Soft-deleting an act or comment hard-deleted its appreciations, so the admin restore from `2026-09-23-1631-admin-restore-and-permission-cleanup.md` brought an act back without its appreciations (`2026-09-23-1510-invitation-bcc-config-and-notification-cascade-tests.md`). It turned out restore also brought back none of the comments or flags. The user decided on 2026-10-17 that `Appreciate` should use `SoftDeletes` and be restored along with its act.

## What changed
- **🗃️ Migration** `2026_10_17_202110_add_soft_deletes_to_appreciates_table` (up only) adds `appreciates.deleted_at`. `Appreciate` now uses `SoftDeletes`, so the existing delete cascades in `Act` and `Comment` soft-delete appreciations instead of removing them.
- **✨ Restore cascade:**
  - `Act::restoring` restores the comments, appreciations and flags that were trashed at or after the act's own `deleted_at`. Content deleted earlier, such as a comment the author removed beforehand, stays deleted.
  - Each restored comment cascades in turn through `Comment::restoring`, which restores its own flags and appreciations the same way.
- **API un-appreciate** (`DELETE /api/private/appreciations/{id}`) now force-deletes. Withdrawing an appreciation isn't something to restore, and trashed rows would otherwise pile up each time a member toggles. This matches the previous hard-delete behaviour.
- Counts (`withCount('appreciates')`, the admin column and stats) exclude trashed rows automatically.

## Files touched
`database/migrations/2026_10_17_202110_add_soft_deletes_to_appreciates_table.php`, `app/Models/{Appreciate,Act,Comment}.php`, `app/Http/Controllers/Api/AppreciationsController.php`, `tests/Feature/Models/{ActTest,CommentTest}.php`, `tests/Feature/Api/AppreciationsTest.php`, `tests/Feature/Filament/RestoreTest.php`.

## Verification
- The new act restore test fails without the `restoring` hook and passes with it.
- Affected tests pass (32/32). PHPStan: 0 errors.

## Deploy notes
- **Run `php artisan migrate`.** Appreciations already hard-deleted before this change can't be recovered.

## Follow-ups
- Acts and comments deleted before this deploy can be restored, but their comments and flags were trashed in the old cascade at the same time, so they come back too. Their appreciations are gone.
