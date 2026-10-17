# Admin email: bulk email authorization and queueing, unified invitation copies

**Completed:** 2026-10-17 · Branch `feature/2026-10-admin-email` (stacked on `feature/2026-10-housekeeping`) · Linear SCI-79, SCI-81, SCI-80

## Context
These are follow-ups from `2026-09-23-1528-invitation-and-bulk-email-tests.md` and `2026-09-23-1621-policy-admin-paths.md`:
- The user bulk email action was only protected by the resource's `viewAny`.
- It sent synchronously in a loop.
- `Invitation::send()` copied differently on its queued and direct paths: queued sends BCC'd `mail.from.address`, while direct sends CC'd it and BCC'd `mail.invitations.bcc`.

## What changed
- **🔒 SCI-79:** `UserPolicy::emailAny()` (administrator and super-admin, the same check as the rest of the policy). The `email` bulk action in `UserResource` now calls `->authorize('emailAny')`, so it stays hidden and blocked even if the resource's access rules change.
- **⚡️ SCI-81:** the bulk email is queued (`Mail::to()->queue()`), one job per recipient. The notification now reads "Email queued for …".
- **♻️ SCI-80:** both invitation paths send to the invitee and BCC only `mail.invitations.bcc`, if it's set. Neither path copies `mail.from.address` any more.

## Files touched
`app/Policies/UserPolicy.php`, `app/Filament/Resources/Users/UserResource.php`, `app/Models/Invitation.php`, `tests/Feature/Filament/UserBulkEmailTest.php`, `tests/Feature/Models/InvitationTest.php`, `tests/Feature/Policies/UserManagementTest.php`.

## Verification
- The new hidden-action test fails without `->authorize('emailAny')` and passes with it.
- The affected Policies, Filament and Invitation tests pass (25/25). PHPStan: 0 errors.

## Deploy notes
- Bulk emails and invitations now depend on a running queue worker (`QUEUE_CONNECTION=database`). Check that the production worker is processing jobs.
- `mail.from.address` no longer gets a copy of every invitation. Set `MAIL_INVITATIONS_BCC` if someone should keep receiving copies.
