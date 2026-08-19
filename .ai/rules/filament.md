---
paths:
  - 'app/Filament/**'
---

# Filament

## User must implement FilamentUser or the /admin panel is unreachable once deployed
Filament's `Authenticate` middleware (`vendor/filament/filament/src/Http/Middleware/Authenticate.php`) has a built-in fallback when the `User` model doesn't implement `Filament\Models\Contracts\FilamentUser`:

```php
abort_if(
    $user instanceof FilamentUser ? (! $user->canAccessPanel($panel)) : (config('app.env') !== 'local'),
    403,
);
```

Without `FilamentUser` implemented: on `APP_ENV=local` *any* authenticated user gets into every panel (no role/permission check at all); on any other `APP_ENV` (a deployed dev/staging/production environment) *every* user gets a 403 — including admins. The panel silently works on a laptop and is completely locked out the moment it's deployed, with no error pointing at the cause.

`App\Models\User` now implements `FilamentUser::canAccessPanel(Panel $panel): bool`, gating on the `'view admin panel'` permission (seeded in `database/seeders/RolesAndPermissionsSeeder.php`, granted to `moderator`/`administrator`/`super-admin`). This makes access identical across all environments — no more env-conditional Filament default to reason about.

If adding more panels (multi-panel Filament apps), remember `canAccessPanel()` receives the `$panel` — check `$panel->getId()` if different panels need different gates.

See `tests/Feature/Filament/AdminPanelAccessTest.php` for the access-control test pattern (administrator → 200, permission-less authenticated user → 403, guest → 302 to `/admin/login`).
