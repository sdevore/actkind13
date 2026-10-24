# Move the API's write routes to standard REST verbs in place (SCI-82)

**Completed:** 2026-10-24 · Branch `sdevore/sci-82-v2-api-with-standard-rest-verbs` (based on `main` after the SCI-106/107/108 stack)

## Context
The 2026-09-23 controller split (`2026-09-23-1451-controller-cleanup-and-api-split.md`) kept the original verbs, `PUT` to create and `POST /{id}` to update, so the iOS client wouldn't break, and deferred standard verbs to a "v2". The user decided against a v2: the iOS client is still in early development, so the API changes in place and the client follows in SCI-109.

## What changed
- ♻️ `routes/api.php`:
  - `POST /private/acts` creates an act (was `PUT`).
  - `PUT|PATCH /private/acts/{act}` updates it (was `POST`).
  - `POST /private/acts/{act}/appreciations` and `POST /private/acts/{act}/comments` create (were `PUT`).
  - `PUT|PATCH /private/comments/{comment}` updates a comment (was `POST`).
  - Route names, controllers, request and response bodies, and status codes are unchanged.
- ✅ The API and policy tests use the new verbs, and their names were updated to match. New tests cover `PATCH` on both update routes and check that the five old verbs return 405.

## Files touched
`routes/api.php`, `tests/Feature/Api/ActsTest.php`, `tests/Feature/Api/CommentsTest.php`, `tests/Feature/Api/AppreciationsTest.php`, `tests/Feature/Policies/EditPermissionsTest.php`.

## Verification
- `route:list` and a `scramble:export` show the new verbs. Scramble documents only `PUT` for the `PUT|PATCH` routes, but `PATCH` is routed and tested.
- Full suite: 294/294 (`--no-tia`). PHPStan: 0 errors.

## Follow-ups
- **SCI-109** (blocked by SCI-82): switch the iOS client's five write calls. Write calls from the current iOS build fail with 405 on dev as soon as this PR's staging deploy runs, and on production after the next release.
- Nothing else calls these routes. The web app uses Livewire, not the JSON API.
