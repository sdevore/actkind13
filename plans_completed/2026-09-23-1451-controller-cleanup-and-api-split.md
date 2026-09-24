# Controller cleanup & web/API controller split

**Completed:** 2026-09-23 · Branch `feature/2026-09-dependancy-updates`

## Context
After API-ISSUES.md was resolved, we reviewed the controllers for unused code and moved them to a more standard Laravel layout. The review turned up two live bugs alongside the dead code.

## What changed

### Bugs fixed
- **`/acts/mine` returned a 404.** It was registered after `Route::resource('acts')`, so `acts/{act}` captured "mine". It is now registered first.
- **Invitations resource.** It registered 7 routes, but the controller only implemented `index`/`show`. The other 5 threw `BadMethodCallException`. It is now `->only(['index', 'show'])`.
- **Guest cache.** `Cache::remember` stored `Paginator`/model objects, which was the root cause of the old `__PHP_Incomplete_Class` leak. The cache is gone. `config/cache.php` `serializable_classes` is back to `false`.

### Dead code removed
- Web `ActController@create/store/edit/update/destroy`. There were no views, nothing linked to them, and acts are created via Livewire.
- `FlagController` (empty), `MarkdownViewController::about()`, and the base `Controller::getAuthUser()/isAuth()` (plus their test).
- `Flag{Store,Update}Request`, `Invitation{Store,Update}Request`, `invitations/{create,edit}.blade.php`, `InvitationController::send()`.
- `App\View\Components\Acts\PublicList`. Its view didn't exist and nothing used it.
- `App\Actions\Api\User\*`, which became controllers.

### New structure
- Web: `ActsController` (index, show, mine), `InvitationsController` (index, show), `MarkdownPagesController` (show), `WelcomeController`.
- API: `Api\ActsController`, `Api\Guest\ActsController`, `Api\MyActsController`, `Api\ActAppreciationsController`, `Api\AppreciationsController`, `Api\ActCommentsController`, `Api\CommentsController`, `Api\UserController`, `Api\LogoutController`, `Api\SanctumTokenController`. They use CRUD method names only.
- `Act` model scopes: `withEngagementCounts()`, `newestFirst()`.
- `ActIndexRequest`: validates `page`/`per_page`, and `paginate($query, defaultPerPage)` clamps `per_page` to 1–50. `paginate()` declares a `Paginator` return type so that Scramble still documents the `links`/`meta` envelope.
- Controllers carry `#[Group('Act'|'Appreciate'|'Comment'|...)]` so the OpenAPI tags stay the same.
- Route URLs, names, and HTTP verbs are unchanged, apart from the dead web routes that were removed.

### Behavior notes
- Guest `/api/acts` pagination links are now absolute `/api/acts` URLs. They used to be `/acts`, which pointed at the web page.
- `per_page=abc` now returns 422 instead of silently falling back to the default. Out-of-range integers are still clamped.
- `act-card` and `public-recents` use `appreciates_count` for guests, which removes the lazy-load N+1.

## Files touched (representative)
`routes/web.php`, `routes/api.php`, `app/Http/Controllers/**`, `app/Http/Requests/ActIndexRequest.php`, `app/Models/Act.php`, `config/cache.php`, `resources/views/components/acts/{act-card,⚡public-recents}.blade.php`, `API-ISSUES.md`, `AGENTS.md`, `tests/Feature/Http/{ActsTest,InvitationsTest}.php`, `tests/Feature/ArchTest.php`.

## Verification
- Full suite: 154/154 passing, including new web acts/invitations tests and arch tests (controller suffix and base class, CRUD-only public methods).
- Route diff against the baseline: only the 10 dead web routes were removed. No new routes and no duplicate names.
- `scramble:export` diff against the baseline: operationIds, tags, and response schemas are unchanged. The only additions are the documented `page`/`per_page` params and a 422 response.
- Smoke test on Herd: `/`, `/acts`, `/about`, `/api/acts` return 200. `/acts/mine` redirects guests to login instead of returning a 404.

## Follow-ups
- A future v2 API should use standard REST verbs (`POST` create, `PUT/PATCH` update). They were kept unchanged here to avoid breaking the iOS client.
- `/acts` sits inside the `cache.headers:public` group, yet it renders content that depends on authentication. Shared caches could serve an authenticated view to guests.
- `ActPolicy`/`InvitationPolicy` still contain `//` stubs and should be tidied.
