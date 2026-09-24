# Testing gap 6: policy admin paths

**Completed:** 2026-09-23 · Branch `feature/2026-09-policy-tests` (stacked on `feature/2026-09-pest-tooling`)

## Context
Item 6 of the testing-gap review. The item 5 probe had shown that moderators could open the user admin pages and that administrators got 403 editing members' content. The user decided on 2026-09-23:
- policies check the `edit {subject}` permission the seeder grants;
- user management is for administrators only;
- moderators review and dismiss flags, administrators edit them, and nobody creates flags in the admin panel;
- flagging stays with moderators and above.

## What changed
- **🐛 Edit permissions:** `ActPolicy`, `CommentPolicy` and `InvitationPolicy::update` checked `update {subject}`, which only super-admin holds, so administrators got 403. They now check `edit {subject}`. This is a code-only change, with no re-seed needed.
- **🔒 `UserPolicy`:** limited to administrator and super-admin, the same role check `ContactUsPolicy` uses. `UserResource` only hid its menu item, so the list, edit page and bulk email were reachable by URL.
- **🔒 `FlagPolicy`:**
  - `view flags` allows listing and viewing;
  - `delete flags` allows dismissing;
  - `edit flags` allows editing;
  - `create` is always false.
- **✅ Moderator-only flagging:** in both flag components, members get 403 and nothing is saved, while moderators can flag.
- **🐛 Flag reason is now validated:** the flag components called `flag()` without `$this->validate()`. The `#[Validate]` attribute only runs when the property changes, so a flag could be saved with no reason.

## Files touched
`app/Policies/{ActPolicy,CommentPolicy,InvitationPolicy,UserPolicy,FlagPolicy}.php`, `resources/views/components/{acts,comments}/⚡flag.{blade,test}.php`, `tests/Feature/Policies/{EditPermissionsTest,UserManagementTest,FlagManagementTest}.php`.

## Verification
- Every new admin, moderator and create check, plus the missing-reason tests, was confirmed failing before its fix.
- PHPStan: 0 errors. Full suite: 226/226 (forced full run, `--no-tia`).

## Follow-ups
- ~~Restoring deleted content~~ and ~~unused `update` permissions~~: resolved in `2026-09-23-1631-admin-restore-and-permission-cleanup.md`.
- The `UserResource` bulk email action is now protected by `viewAny`, but has no `authorize` of its own.
- Remaining testing gaps: web pages (7) and public cache headers on `/acts` (8).
