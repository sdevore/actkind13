# Invitation BCC to config; notification and delete-cascade tests

**Completed:** 2026-09-23 · Branch `feature/2026-09-invitation-codes-and-tests`

## Context
Follow-up to `2026-09-23-1455-enforce-invitation-codes-on-registration.md`. The user asked to move the hardcoded invitation BCC address into config, then to cover items 2–3 of the testing-gap review: act-owner notifications, and the soft-delete cascades on acts and comments.

## What changed
- **BCC to config:** `config/mail.php` gains `invitations.bcc` (from `MAIL_INVITATIONS_BCC`, default empty). `Invitation::send()` uses it and sends no BCC when it's unset. `.env.example` documents the variable. **Set `MAIL_INVITATIONS_BCC` in `.env` and production to keep the old copies.**
- **Notification tests (item 2):** appreciating an act through the API notifies the owner with `ActAppreciated`, and a repeat appreciation sends nothing. Commenting through the API notifies the owner with `ActCommented`.
- **Cascade tests (item 3):** deleting an act soft-deletes its comments and flags and hard-deletes its appreciations (`Appreciate` has no `SoftDeletes`), and leaves other acts' content alone. Deleting a comment soft-deletes its flags and removes its appreciations.

## Files touched
`config/mail.php`, `.env.example`, `app/Models/Invitation.php`, `tests/Feature/Models/{InvitationTest,ActTest,CommentTest}.php`, `tests/Feature/Api/{AppreciationsTest,CommentsTest}.php`.

## Verification
- Full suite: 172/172 passing.

## Follow-ups
- Appreciations are hard-deleted when their act or comment is soft-deleted, so restoring an act can't bring them back. Decide whether `Appreciate` should use `SoftDeletes`.
- `Invitation::send()` queued vs. direct paths CC/BCC differently (queued BCCs `mail.from.address`; direct CCs it). Consider unifying.
- Remaining testing gaps: invitation and bulk email (4), admin moderation (5), policy admin paths (6), web pages (7), public cache headers on `/acts` (8).
