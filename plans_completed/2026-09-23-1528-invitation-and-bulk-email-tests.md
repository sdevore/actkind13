# Testing gap 4: invitation and bulk email

**Completed:** 2026-09-23 · Branch `feature/2026-09-email-tests` (stacked on `feature/2026-09-invitation-codes-and-tests`)

## Context
Item 4 of the testing-gap review: invitation and bulk email had no coverage. Reading the code first turned up three live bugs. Each one was fixed test-first in its own commit.

## What changed
- **🐛 Support link in the invitation email:** `config('\nmail.from.address')` was split across a line, so the `mailto:` link was empty.
- **✏️ Invitation email copy:** removed "You need to use the same email to register". Since 2026-09-23 the sign-up email does not have to match the invited address.
- **🐛 Signup link didn't prefill the code:** `/register?code=…` was ignored because the form only read `old('code')`. It now falls back to `request()->query('code')`.
- **🐛 Admin bulk email was broken:** `BulkEmailUsers` rendered the missing view `mail.users.bulk-email`, so every send threw. Added `emails/user/bulk.blade.php`, alongside the other mail templates.
- **🐛 Bulk email "From" fields were ignored:** the envelope now uses the email and name chosen in the admin form.
- **✅ New tests:**
  - `InviteUser`: from, reply-to, subject and content
  - `BulkEmailUsers`: rendering and from address
  - The admin bulk action: sends only to selected users, and validates subject and body
  - Queued `Invitation::send()`
  - `User::sendInvitation()`: self as inviter, or on behalf of another user

## Files touched
`resources/views/emails/user/{invite,bulk}.blade.php`, `resources/views/livewire/auth/register.blade.php`, `app/Mail/BulkEmailUsers.php`, `tests/Feature/Mail/{InviteUserTest,BulkEmailUsersTest}.php`, `tests/Feature/Filament/UserBulkEmailTest.php`, `tests/Feature/Models/{InvitationTest,UserTest}.php`, `tests/Feature/Auth/RegistrationTest.php`.

## Verification
- The support-link, same-email copy, code-prefill and missing-template tests were each confirmed to fail before their fix.
- Full suite: 184/184 passing.

## Follow-ups
- `pestphp/pest-plugin-livewire` isn't installed, so Filament tests use `Livewire::test()`.
- `Invitation::send()` still copies differently depending on the path: queued sends BCC `mail.from.address`, while direct sends CC it and BCC `mail.invitations.bcc`.
- The admin bulk email sends synchronously in a loop. For larger user counts, consider queueing it.
- Remaining testing gaps: admin moderation (5), policy admin paths (6), web pages (7), public cache headers on `/acts` (8).
