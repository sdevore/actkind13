# Show flaggers as initials avatars instead of blank photos (SCI-111)

**Completed:** 2026-10-24 · Branch `sdevore/sci-111-profile-photos-render-blank-in-the-admin-users-table-and` (based on `main` after SCI-110)

## Context
I found this while fixing SCI-110. The act and comment flag views rendered `<img src="{{ $flag->user->profile_photo_url }}">`, and the admin users table had `ImageColumn::make('profilePhotoUrl')`. Both relied on Jetstream's `profile_photo_url` accessor, which this app no longer has, so they showed empty images. Nothing in the app uploads profile photos. Any `profile_photo_path` values on dev or production are leftovers from the old app whose files probably never moved to Cloud storage. The rest of the app already shows people as Flux initials avatars (the sidebar and user menu use `User::initials()`).

Decision: use initials avatars and add no photo feature for now. The API keeps returning `profile_photo_path`, which is null.

## What changed
- 🐛 `resources/views/components/acts/⚡flag.blade.php` and `components/comments/⚡flag.blade.php`: `<flux:avatar size="xs" circle :name :initials>` replaces the `<img>`.
- 🔥 `UserResource` table: removed the always-empty "Profile Photo" column.
- ✅ `tests/Feature/View/Components/FlagTest.php`: a dataset test for both flag components checks that a flag shows the flagger's initials avatar and name and renders no `<img>`. It failed before the change.

## Files touched
`resources/views/components/acts/⚡flag.blade.php`, `resources/views/components/comments/⚡flag.blade.php`, `app/Filament/Resources/Users/UserResource.php`, `tests/Feature/View/Components/FlagTest.php`.

## Verification
- A browser probe of `/acts/{act}`, viewed by a user with `view flags`, shows a "JD" avatar next to "Jane Doe", in the same style as the header avatar, with no JavaScript errors.
- Full suite: 304/304 (`--no-tia`). PHPStan: 0 errors. No references to `profile_photo_url`/`profilePhotoUrl` remain.

## Follow-ups
- If profile photo uploads are ever added, add a real `profilePhotoUrl` accessor, with initials as the fallback, and bring back the image column and `<img>` tags.
