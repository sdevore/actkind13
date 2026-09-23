# Enforce invitation codes on registration

**Completed:** 2026-09-23 · Branch `feature/2026-09-dependancy-updates`

## Context
This is item 1 of the testing-gap review that followed the controller cleanup (`2026-09-23-1451-controller-cleanup-and-api-split.md`). Registration required a `code`, but it was only checked as a string. Any value let someone register, and the invitation was never marked as used. Decision (user, 2026-09-23): **a code must match an unused invitation.**

## What changed
- `CreateNewUserRequest`: `code` must exist in `invitations` with `joined_id` null and not soft-deleted. Custom message: "This invitation code is invalid or has already been used."
- `CreateNewUser`: in a transaction, locks the matching unused invitation (`lockForUpdate`), creates the user, and sets `joined_id` and `joined_at`. If a concurrent sign-up used the code first, it rethrows a `code` validation error.
- **Related bug:** `Invitation` used `joined` in `$fillable`/`$casts`, but the column is `joined_at`. That made `$invitation->joined` always null, so the invitation list never hid Resend/Delete on used invitations. Fixed in the model and in `components/invitations/⚡invitation-list.blade.php`.
- `InvitationFactory`: new `unused()` and `joined()` states, because the default state randomly picks one.

## Files touched
`app/Http/Requests/CreateNewUserRequest.php`, `app/Actions/Fortify/CreateNewUser.php`, `app/Models/Invitation.php`, `database/factories/InvitationFactory.php`, `resources/views/components/invitations/⚡invitation-list.blade.php`, `tests/Feature/Auth/RegistrationTest.php`, `resources/views/components/invitations/⚡invitiation-list.test.php`.

## Verification
- `RegistrationTest` covers: registering with an unused code, registering with an email different from the invited address, the invitation being consumed (`joined_id` and `joined_at`), an unknown code rejected, a used code rejected, and a soft-deleted invitation rejected.
- Invitation list: Resend/Delete are hidden for joined invitations and visible for unused ones.
- Full suite: 163/163 passing.

## Follow-ups
- The rest of the testing-gap list: notifications (2), soft-delete cascade (3), invitation and bulk email (4), admin moderation (5), policy admin paths (6), web pages (7), public cache headers on `/acts` (8).
- Decided (user, 2026-09-23): the sign-up email does **not** need to match `invitations.email`, since people may get a code at one address and prefer another. There is a test that guards this.
- `Invitation::send()` hardcoded a personal BCC address. Moved to config in `2026-09-23-1510-invitation-bcc-config-and-notification-cascade-tests.md`.
