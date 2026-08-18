---
paths:
  - 'app/Providers/**'
---

# Providers

## Gate::define closures for guest-callable abilities need a nullable typed first param
Laravel's `Gate::allows()`/`Gate::check()` will silently SKIP an ability closure and just return `false` — without ever invoking it — if the current request is a guest (no user on the default guard, e.g. `Auth::user()` is null) AND the closure's first parameter doesn't look guest-safe.

Internally `Gate::canBeCalledWithUser()` → `callbackAllowsGuests()` uses Reflection: it only calls the closure for a guest if `isset($parameters[0])` AND that parameter is nullable-typed (or has a `null` default). A **zero-parameter** closure (e.g. `function (): bool { ... }`) fails `isset($parameters[0])` and is therefore treated as NOT guest-callable — Gate falls through to a no-op `function () {}` and the ability silently evaluates to `false`, with no error, no exception, nothing in logs.

This bites any Gate ability meant to run for non-session-authenticated requests — e.g. Sanctum bearer-token checks (`app/Providers/AppServiceProvider.php`'s `viewApiDocs` gate), since a Sanctum Bearer token does NOT populate the default `web` guard's user, so `Gate::allows()` sees a guest.

**Always declare the first parameter as `?User $user` (nullable-typed) even if unused**, e.g.:
```php
Gate::define('viewApiDocs', function (?User $user): bool {
    return Auth::guard('sanctum')->check();
});
```
If you need to debug "why does my Gate closure never run," check `Gate::has($ability)` (registered?) vs `Gate::allows($ability)` (evaluates true/false) — if `has` is true but the closure's side effects (logging, etc.) never happen, this is almost certainly the cause.

## Auth::guard('sanctum')->check() also passes for logged-in web-session users
`Laravel\Sanctum\Guard::__invoke()` checks the STATEFUL guard(s) (`config('sanctum.guard', 'web')`) FIRST, before ever looking at the `Authorization: Bearer` header — so `Auth::guard('sanctum')->check()` (or `->user()`) returns truthy for ANY request with a logged-in `web` session, not just requests carrying a real API token. The resolved user gets wrapped in a `Laravel\Sanctum\TransientToken`, not a real `PersonalAccessToken`.

This bit the `viewApiDocs` Gate in `app/Providers/AppServiceProvider.php` — the intent was "valid API token OR administrator role", but `Auth::guard('sanctum')->check()` alone also silently let in *any* logged-in web user regardless of role, since session-authenticated requests pass through the stateful fallback.

To check for a **real** Sanctum API token specifically (excluding the stateful/session fallback), inspect the resolved user's access token type:
```php
$sanctumUser = Auth::guard('sanctum')->user();
return $sanctumUser?->currentAccessToken() instanceof \Laravel\Sanctum\PersonalAccessToken;
```
See also [[gate-guest-callback-nullable-param]] for the related "closure never runs for guests" Gate gotcha hit in the same file.
