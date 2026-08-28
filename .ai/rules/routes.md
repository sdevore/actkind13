---
paths:
  - 'routes/**'
---

# Routes

## Route names must be globally unique across web.php and api.php
Laravel's named-route registry is global across ALL route files — `web.php` and `api.php` share one namespace. If a name collides (e.g. `acts.index` defined by both `Route::resource('acts', ...)` in `web.php` and a `->name('acts.')` group in `api.php`), `route('acts.index')` silently resolves to whichever was registered LAST — no error, no warning. This broke `redirect()->route('acts.index')` and several `route('acts.show', ...)` calls across notifications/blade views, silently pointing them at the JSON API instead of the web page.

Fix/convention: every `routes/api.php` route is now wrapped in an outer `Route::name('api.')->group(...)`, with sub-groups further prefixed (`guest.`, `user.`, `private.`) — so ALL API route names look like `api.private.acts.index`, `api.guest.acts.show`, `api.sanctum.token`, etc., fully distinct from any `web.php` name. When adding new API routes, keep everything inside that `api.` group; when adding new web routes, don't reuse a name that already exists under `api.*`.

To check for collisions: `php artisan route:list --json`, group by `name`, flag any with more than one entry.

Also: `bootstrap/cache/routes-v7.php` (gitignored) can go stale and make `php artisan test` fail in confusing, seemingly-unrelated ways (404s, "Route [x] not defined") that don't reproduce via `route:list` or in a fresh environment. If test failures look inexplicable after editing `routes/*.php`, run `php artisan route:clear` first before debugging further.
