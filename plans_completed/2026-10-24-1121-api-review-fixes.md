# Fix the 2026-10-24 iOS API review findings (SCI-106, SCI-107, SCI-108)

**Completed:** 2026-10-24 · Stacked branches on `main`: `sdevore/sci-106-feed-paging-drops-and-repeats-acts-no-unique-sort-tiebreaker` → `sdevore/sci-107-unknown-act-type-returns-500-instead-of-422` → `sdevore/sci-108-openapi-spec-drift-from-live-api`

## Context
The iOS team reviewed `dev.actkind.online` against the published OpenAPI spec and raised three tickets:

- **SCI-106 (High):** paging a feed returned some acts twice and never returned others. Seed data had 80 acts across 14 timestamps, and every feed sorted only by `created_at`.
- **SCI-107 (Medium):** `PUT /api/private/acts` with `type: "bogus"` returned a 500 and created Nightwatch issue #10 (`ValueError` from the `ActType` enum cast).
- **SCI-108 (Low):** the spec was missing several details the live API returns.

## What changed
- ⬆️ **Dependencies (first commit on the SCI-106 branch, at the user's request):** `composer update` (Laravel 13.38, Filament 5.10.3, Scramble 0.13.49, Livewire 4.4.8 and others) and `npm update` (Vite 8.3.5), plus the re-published Filament assets. `@prettier/plugin-php` stays on 0.25 because `prettier-plugin-blade` 3.3.3, the latest, only accepts `^0.24 || ^0.25`. `boost:update` rewrote `CLAUDE.md` during the update, and that change was reverted so it stays the `AGENTS.md` pointer.
- 🐛 **SCI-106:** `Act::newestFirst()` now sorts by `created_at` and then `id`, which fixes the guest, private and mine API feeds and the web acts pages. The public recents component now uses the scope too, instead of `latest()`.
- 🐛 **SCI-107:** `ActStoreRequest` and `ActUpdateRequest` validate `type` with `Rule::enum(ActType::class)`, so an unknown type returns 422 with `errors.type`. The spec now documents `type` as a `$ref` to the `ActType` enum.
- 📝 **SCI-108:**
  - `page`/`per_page` descriptions and defaults are PHPDoc on the `ActIndexRequest` rules. Action-level `#[QueryParameter]` attributes don't override parameters that come from a FormRequest.
  - `AppServiceProvider::configureApiDocs()` adds an operation transformer that puts `current_page_url` into every paginated `meta` schema. Scramble's paginator definition leaves it out.
  - `UserResource` lists its fields explicitly: `id`, `name`, `email`, `profile_photo_path`, `created_at`, `updated_at`. A guarded migration adds `profile_photo_path` where it's missing. Dev and production already have the column from the previous app. `current_team_id` and any other legacy columns are no longer exposed.
  - `ActAppreciationsController::store()` has a summary and a description saying that appreciating your own act returns 403.
  - `scramble.servers` lists production and dev. The docs description explains how to open the docs with a Sanctum token. Access stays token-gated.

## Files touched
`composer.lock`, `package-lock.json`, `public/js/filament/**`, `public/css/filament/**`, `app/Models/Act.php`, `resources/views/components/acts/⚡public-recents.blade.php`, `app/Http/Requests/ActStoreRequest.php`, `app/Http/Requests/ActUpdateRequest.php`, `app/Http/Requests/ActIndexRequest.php`, `app/Http/Resources/User.php`, `app/Http/Controllers/Api/ActAppreciationsController.php`, `app/Providers/AppServiceProvider.php`, `config/scramble.php`, `database/migrations/2026_10_24_181312_add_profile_photo_path_to_users_table.php`, `tests/Feature/Api/ActsTest.php`, `tests/Feature/Api/UserTest.php`.

## Verification
- The new tied-timestamp paging test (guest, private and mine feeds) failed before the fix: ties came back oldest-id first. It passes after.
- The new invalid-type tests for create and update failed before the fix (500) and pass after (422).
- `UserTest` now reads the wrapped `data` payload. The older "does not expose internal account fields" test was checking the top level and passed no matter what was exposed.
- A `scramble:export` checked with `jq` shows: parameter descriptions and defaults on all three feeds, `meta.current_page_url` on all three, `profile_photo_path` (nullable) in `User` with no `current_team_id`, both servers, `ActType` refs, and the appreciation 403 description. Run `php artisan scramble:clear` first, because the spec is cached in the file store.
- Full suite: 287/287 (`--no-tia`). PHPStan: 0 errors. Build passes.

## Follow-ups
- Nightwatch #10 (Develop) is assigned, set to Medium priority, and linked to SCI-107 with a comment. Resolve it once the fix reaches dev and production.
- After staging deploys, re-run the iOS repro: walk `/api/acts?page=1..N` and expect 80 unique ids out of 80.
- Bump `@prettier/plugin-php` to 0.26 once `prettier-plugin-blade` supports it.
- Scramble caches the spec (`scramble.cache`). Run `php artisan scramble:clear` after deploys if the docs look stale.
