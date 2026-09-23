# Testing gap 7: web pages (plus a stored XSS fix)

**Completed:** 2026-09-23 · Branch `feature/2026-09-web-page-tests` (stacked on `feature/2026-09-pest-browser`)

## Context
Item 7 of the testing-gap review: the welcome page, `/terms`, `/policy`, `/about`, `/contact` and the public act page had almost no tests. Before writing the tests I compared what signed-out visitors can see with the guest rules recorded in API-ISSUES.md. That comparison turned up a stored XSS hole and two privacy mismatches.

## What changed
- **🔒 Stored XSS fixed (security).**
  - Act descriptions, comments and invitation messages were rendered with `{!! Str::markdown() !!}`, which lets raw HTML and `javascript:` links through.
  - A browser probe confirmed that script stored in an act description and in a comment **ran in a guest's browser**. Any member could run JavaScript in the browser of anyone viewing their content, admins included.
  - A new `<x-user-markdown>` anonymous component renders user-written markdown with `html_input => escape` and `allow_unsafe_links => false`. The same probe confirms nothing runs afterwards.
  - `Str::markdown()` is now used without these options only for the repository's own `resources/markdown/*.md` pages.
- **🔒 Welcome page hides authors from guests** (user decision, 2026-09-23). This matches the guest API and `/acts`. `WelcomeController` no longer eager-loads `user`.
- **🔒 Act pages hide comments from guests** (user decision, 2026-09-23). This matches the API-ISSUES.md decision that signed-out readers must not see comments.
- **🔥 Removed unused files** (user approved): `resources/views/terms.blade.php`, `resources/views/policy.blade.php`, and the unrouted `resources/markdown/community-guidelines.md`, whose text already lives in `terms.md`.
- **🐛 Contact page:** it hardcoded "Welcome to ActKind.online" as its title, ignoring the route's `Contact Us`, and it closed `<x-layouts::app>` with `</x-layouts::guest>`.
- **✅ New tests:**
  - welcome page: the guest view, and the redirect to the dashboard when signed in;
  - act page: the guest and member views;
  - `/terms`, `/policy`, `/about`: content and titles;
  - `/contact`: renders the form, is rate-limited to 5 requests a minute, and shows the right title.

## Files touched
`resources/views/components/user-markdown.blade.php`, `resources/views/{acts/show,welcome,invitations/show,contact_us/contact}.blade.php`, `resources/views/components/comments/⚡comment-list.blade.php`, `app/Http/Controllers/WelcomeController.php`, `tests/Feature/Security/UserMarkdownTest.php`, `tests/Feature/Http/{WelcomePageTest,StaticPagesTest,ActsTest}.php`, plus the three removed files.

## Verification
- The XSS, welcome-author, guest-comments and contact-title tests were each confirmed failing before their fix.
- The XSS fix is also verified in a real browser: the injected script ran before the fix and doesn't run after it.
- PHPStan: 0 errors. Full suite: 252/252 (`--no-tia`).
- One test first used `assertSeeLivewire()`. That's a Livewire macro PHPStan can't resolve, so the test now asserts the form's markup instead.

## Follow-ups
- Existing production content may already contain injected HTML. The escaping neutralises it on render, but a one-off scan of `acts.description`, `comments.body` and `invitations.message` for `<script`, `on*=` or `javascript:` would show whether anyone tried.
- The last remaining testing gap is item 8: public cache headers on `/acts`.
