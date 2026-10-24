# Let admins create and edit users in the admin panel (SCI-110)

**Completed:** 2026-10-24 · Branch `sdevore/sci-110-admins-cant-create-or-edit-users-in-the-admin-panel-empty` (based on `main` after v2026.10.24)

## Context
Admins couldn't create a user at `/admin/users/create`. `UserResource::form()` returned an empty `components([])`, so the Create page had no fields and saving would have inserted a user with no name, email or password. The Edit page shares that form and was blank too. Nightwatch had no exception, because there was never anything to submit. The policy, the Create button and the model's fillable fields were already correct.

Decisions: a blank password on create emails a set-password link, and a filled one is used as is ("both"). Admins can assign roles in the form.

## What changed
- 🐛 `UserResource::form()` now has these fields, in two columns like the invitations form:
  - `name` and `email`. Email is unique, ignoring the current record.
  - `password`: revealable, and only saved when it's filled. The model's `hashed` cast does the hashing. Its helper text depends on the operation.
  - `roles`: a multi-select on the Spatie relationship.
- 🔒 Only a super-admin can grant `super-admin`. `assignableRoles()` hides it from the options, and a closure validation rule rejects it if it's posted anyway. Filament's own option check for a multiple relationship select ignores `modifyQueryUsing`, so the hidden option alone didn't block a forged id. A test caught this. When an administrator edits a super-admin, that user keeps the role.
- ✨ `CreateUser`: a blank password is replaced with `Str::password(32)`, and `afterCreate()` sends `Password::sendResetLink()`, which uses Fortify's reset route and Laravel's `ResetPassword` notification. The admin sees a success notification, or a warning if the link wasn't sent (e.g. throttled).

## Files touched
`app/Filament/Resources/Users/UserResource.php`, `app/Filament/Resources/Users/Pages/CreateUser.php`, `tests/Feature/Filament/UserResourceTest.php` (new).

## Verification
- `UserResourceTest`: 8 tests. Create with a password and a role. Create without a password, which sends `ResetPassword`. Edit with a blank password, and with a new one. A duplicate email is rejected. An administrator can't grant `super-admin` and a super-admin can. Editing a super-admin keeps the role. All 7 of the original tests failed before the fix.
- A browser probe (`pest --agent` + `visit()`) of `/admin/users/create` as an administrator shows the fields, the helper text and the roles select, with no JavaScript errors.
- Full suite: 302/302 (`--no-tia`). PHPStan: 0 errors.
- Local setup note: the Playwright 1.64 bump in v2026.10.24 needs a fresh browser download on each machine (`npx playwright install chromium`).

## Follow-ups
- SCI-111: the users table's `profilePhotoUrl` column and the flag views' `profile_photo_url` use a Jetstream accessor that no longer exists, so images render blank.
- SCI-91: reset mail isn't arriving on staging. Creating a user without a password depends on it, so on staging set a password until SCI-91 is fixed.
