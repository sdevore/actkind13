# Testing gap 8: public page cache headers

**Completed:** 2026-09-23 · Branch `feature/2026-09-cache-headers` (stacked on `feature/2026-09-web-page-tests`)

## Context
Item 8, the last of the testing-gap review started in `2026-09-23-1451-controller-cleanup-and-api-split.md`. `/`, `/acts`, act pages, `/terms`, `/policy` and `/about` were in a route group sending `Cache-Control: public, max-age=30, s-maxage=300, stale-while-revalidate=600` with an ETag. Those pages aren't the same for everyone.

## Findings (measured, not assumed)
- **Every guest response sets two cookies (the session and the XSRF token) and embeds a CSRF token.** A shared cache would either refuse to cache them, making `public` pointless, or cache them and hand one visitor's session and token to others.
- **Stale guest view after login.** A browser probe showed a member who had just logged in was served the cached **guest** version of an act page, with no author shown, for up to 30s. Adding a cache-busting query string made it render correctly, which confirmed the cause.
- **Member pages were private only by accident.** Livewire's `DisableBackButtonCacheMiddleware` overwrote the headers with `no-store, private` because some component on those pages opts into it. Nothing in the app made them private on purpose.

## What changed
Following the user's decision on 2026-09-23 (private caching with ETag), the route group now uses `cache.headers:private;etag`. That means:
- no shared or CDN caching;
- no window where the browser treats a page as fresh without asking;
- unchanged pages still revalidate cheaply with a 304.

## Verification
- The header tests (private, never `public` or `s-maxage`, no fresh `max-age`) failed before the fix and pass after it.
- The ETag/304 test passes. It calls `Livewire::flushState()` between its two requests, because Livewire injects its styles only once per PHP process, which made the second in-test render (and its ETag) differ. Against the real Herd site, the ETag is stable within a session and a conditional request returns **304**.
- The browser probe of logging in and then viewing an act now shows the member view immediately.
- PHPStan: 0 errors. Full suite: 264/264 (`--no-tia`).

## Follow-ups
- If page caching is wanted later, it needs guest pages with no session, cookies or CSRF tokens, and so no Livewire for guests. Or cache data rather than pages.
- **The testing-gap list (items 1–8) is now complete.**
