# Admin restore and permission cleanup

**Completed:** 2026-09-23 · Branch `feature/2026-09-policy-tests` (PR #9)

## Context
Follow-up to `2026-09-23-1621-policy-admin-paths.md`. The user decided on 2026-09-23 that **admins should be able to restore** deleted content, and that the **unused `update {subject}` permissions should be discarded**.

## What changed
- **🔥 `update {subject}` permissions removed.** The seeder no longer creates them, and the migration `2026_09_23_232624_remove_unused_update_permissions` deletes them from existing databases.
- **✨ `restore {subject}` permissions** for acts, comments and invitations. The policies already checked these, but they were never seeded.
  - The seeder creates them and grants them to administrators; super-admin gets them through `Permission::all()`.
  - The migration `2026_09_23_232625_add_restore_permissions_for_administrators` grants them on existing databases. It skips fresh databases (no roles yet): migrations run before the seeder, whose `Permission::create()` would otherwise crash. The tests caught this.
- **✨ Restoring in the admin panel** for the Acts, Comments and Invitations resources:
  - `TrashedFilter`, plus `RestoreAction` and `RestoreBulkAction` in the table and `RestoreAction` on edit pages.
  - `getRecordRouteBindingEloquentQuery()` without `SoftDeletingScope`, per the Filament 5 docs.
  - `restoreAny()` on the three policies, so bulk restore is authorized.
  - Force-delete is intentionally not exposed.

## Files touched
`database/seeders/RolesAndPermissionsSeeder.php`, `database/migrations/2026_09_23_23262{4,5}_*.php`, `app/Filament/Resources/{Acts,Comments,Invitations}/**`, `app/Policies/{Act,Comment,Invitation}Policy.php`, `tests/Feature/Database/PermissionsTest.php`, `tests/Feature/Filament/RestoreTest.php`.

## Verification
- **Migrations:** tested against an existing database (old permissions removed, restore granted to existing roles) and against a fresh one (the migration no-ops and the seeder creates the permissions).
- **Restore:** administrators can restore single and bulk records for all three resources. Moderators can't: the action is hidden and the record stays deleted.
- **Checks:** PHPStan 0 errors. Full suite 240/240 (`--no-tia`).

## Deploy note
Run `php artisan migrate` on deploy to remove the `update` permissions and grant `restore` to the administrator and super-admin roles.
