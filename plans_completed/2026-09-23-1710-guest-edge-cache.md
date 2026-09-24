# Edge-cacheable guest pages behind Cloudflare

**Completed:** 2026-09-23 · Branch `feature/2026-09-guest-edge-cache` (stacked on `feature/2026-09-cache-headers`)

## Context
Follow-up to `2026-09-23-1658-public-page-cache-headers.md`. The user asked whether guest pages without forms could drop their CSRF token and cookies so they wouldn't need `private`, and confirmed that **Cloudflare is in front of production**.

Read-only probes of production showed:
- `/acts` and `/about` were `cf-cache-status: DYNAMIC`, meaning never cached, because they set cookies.
- `/` was cached by a Cloudflare rule with a **31-day browser TTL** (`max-age=2678400`), and was served from cache (HIT) **even to requests carrying a session cookie**. So members got the cached guest homepage instead of the redirect to their dashboard.

The user chose to cache guests only.

## What changed
- **`SkipSessionForCookielessGuests`:** on a GET or HEAD with no session cookie, no `remember_*` cookie and no authenticated user, it runs **without** the session and CSRF middleware and shares an empty `$errors`. Otherwise it pipes the request through `StartSession`, `ShareErrorsFromSession` and `PreventRequestForgery` as normal.
- **`SetPageCacheHeaders`:** a response is `public, max-age=0, s-maxage=300` only when the request has no session, no user is signed in, and the response sets no cookies. Everything else is `private`. Both get an ETag and a 304 when unchanged.
- **Routes:** the public group drops the three web-group middleware and uses these two instead, replacing `cache.headers:private;etag` from #12.
- **Layouts:** the guest layouts print the CSRF meta tag only when a session exists.
- **`AGENTS.md`:** documents what may and may not render for guests on these pages.

## Verification
- **Tests:** cookieless guests get no cookies, no CSRF token, the public headers and a stable ETag. A session cookie, a remember cookie or a signed-in member each get `private`. The 304 still works.
- **Herd over HTTPS:** cookieless requests get `max-age=0, public, s-maxage=300` with no Set-Cookie and no CSRF token. With a session cookie, the response is `private` and sets cookies.
- **Real browser:** guest pages have no JavaScript errors, and logging in then viewing an act immediately shows the member view.
- **Checks:** PHPStan 0 errors. Full suite 266/266 (`--no-tia`).

## Cloudflare configuration (manual; do on staging first, then production)
1. **Remove or replace the existing cache rule for `/`**, the one giving a 31-day browser TTL and ignoring session cookies.
2. **Add a Cache Rule** named "Cache guest pages":
   - **When** (custom filter expression):
     ```
     (http.request.method in {"GET" "HEAD"}
      and (http.request.uri.path in {"/" "/acts" "/terms" "/policy" "/about"}
           or (starts_with(http.request.uri.path, "/acts/") and http.request.uri.path ne "/acts/mine"))
      and not http.cookie contains "actkind-session"
      and not http.cookie contains "remember_web_")
     ```
     Production's session cookie is `actkind-session`. Check `SESSION_COOKIE` on staging, since locally it is `actkindonline-session`.
   - **Then:** Cache eligibility **Eligible for cache**. Edge TTL **Use cache-control header if present, bypass cache if not**, which honours `s-maxage=300` and `private`. Browser TTL **Respect origin TTL**, which passes `max-age=0` through.
3. **Zone setting** Caching → Configuration → **Browser Cache TTL: Respect Existing Headers.**
4. **After deploying, purge** `/`, `/acts`, `/about`, `/terms`, `/policy`.
5. **Verify:**
   - `curl -sI https://actkind.online/acts` twice: no `set-cookie` other than Cloudflare's own `__cf_bm`, `cache-control: max-age=0, public, s-maxage=300`, and `cf-cache-status` going `MISS` then `HIT`.
   - With `-H 'Cookie: actkind-session=x'`: `cf-cache-status: BYPASS` or `DYNAMIC`, and `private`.

## Caveats and follow-ups
- **Browsers that fetched `/` in the last 31 days** keep their old copy until it expires or the visitor hard-refreshes. A Cloudflare purge can't reach browser caches.
- **An unexplained random-named httponly cookie** appears on production responses (not set anywhere in the app or its packages, so probably from the hosting side). If it's still sent to cookieless guests after deploy, `SetPageCacheHeaders` marks the response `private`. That's safe, but Cloudflare won't cache until its source is found.
- **Guests see new acts up to 5 minutes late** on cached pages (`s-maxage=300`).
