# Deploy checklist: September 2026 stack (#3–#13)

The whole stack is merged and deployed together, **staging first**, after manual testing. Each item links to the PR that introduced it.

| # | PR | Needs at deploy |
|---|---|---|
| 3 | Dependency updates | — |
| 4 | Controller cleanup & web/API split | — |
| 5 | Invitation codes & first testing gaps | Optional env var; **invitation data check** |
| 6 | Invitation & bulk email tests and fixes | — |
| 7 | Admin moderation tests & fixes | — |
| 8 | Pest tooling (TIA, agent & PHPStan plugins) | — (dev only) |
| 9 | Policies, admin restore, permission cleanup | **`php artisan migrate`** |
| 10 | Pest browser plugin | — (dev only) |
| 11 | Web pages, guest privacy & **stored XSS fix** | — |
| 12 | Private cache headers | — |
| 13 | Edge-cacheable guest pages | **Cloudflare changes and purge** |

---

## 1. Before merging

- [ ] CI passes on every PR, #3 through #13.
- [ ] Merge in order: #3 → #4 → … → #13. After each merge, check that the next PR's base moved to `main`. If it didn't, retarget it.
- [ ] Look up production's and staging's `SESSION_COOKIE` values. Production is `actkind-session`. Step 5 needs both.

## 2. Environment and configuration

- [ ] **`MAIL_INVITATIONS_BCC`** (#5), optional. Set it to an address that should receive a blind copy of directly sent invitation emails. If it's left empty, no BCC is sent; before #5 the address was hardcoded.
- [ ] Nothing else is new. The `pestphp/*` plugins and Playwright are dev dependencies, so `composer install --no-dev` and the production build don't use them.

## 3. Database

- [ ] **Run `php artisan migrate`** (#9). The two migrations are up-only:
  - `2026_09_23_232624_remove_unused_update_permissions` deletes the `update {acts,comments,flags,appreciates,invitations}` permissions. Nothing checks them anymore; policies use `edit {subject}`.
  - `2026_09_23_232625_add_restore_permissions_for_administrators` creates `restore {acts,comments,invitations}` and grants them to the existing `administrator` and `super-admin` roles.
  - Both reset Spatie's permission cache themselves.
- [ ] **Check invitation data** (#5). Registration never recorded which invitation a user joined through (the model used `joined`, but the column is `joined_at`). So every existing invitation still counts as unused, and once #5 ships, a code someone already signed up with would work again. Check:
  ```sql
  -- Invitations whose invitee already has an account but the invitation is still "unused"
  SELECT i.id, i.email, i.code, u.id AS user_id
  FROM invitations i
  JOIN users u ON u.email = i.email
  WHERE i.joined_id IS NULL AND i.deleted_at IS NULL;
  ```
  To close those codes, mark the rows as joined, ideally in a one-off migration so it's recorded. This version works on any database:
  ```php
  Invitation::query()
      ->whereNull('joined_id')
      ->each(function (Invitation $invitation): void {
          $user = User::firstWhere('email', $invitation->email);

          if (! $user) {
              return;
          }

          $invitation->update(['joined_id' => $user->id, 'joined_at' => $invitation->joined_at ?? $user->created_at]);
      });
  ```
  Invitees who signed up with a *different* email can't be matched this way. Review any remaining old invitations by hand.
- [ ] Optional (#4): the old guest-feed cache entries (`acts.{page}` and `acts.{page}.{perPage}`) are no longer read and will expire on their own. Don't run `cache:clear` if sessions are stored in the same cache store.

## 4. Staging manual tests

**Security and privacy (#11)**
- [ ] As a member, create an act whose description is `Hi <img src=x onerror=alert(1)> [x](javascript:alert(2))`. View it signed out and signed in: there should be **no alert**, and the tag should appear as plain text. Repeat in a comment and in an invitation message.
- [ ] Signed out: the welcome page shows no author names, and act pages show no comments. Signed in: both appear.

**Caching (#12, #13)**
- [ ] Signed out, open an act. Log in, then open the same act again: the author and comments show **immediately**.
- [ ] Run the `curl` checks in step 6 against staging.

**Registration and invitations (#5, #6)**
- [ ] Registering with an unused invitation code works and marks the invitation used. A used code, an unknown code and a deleted invitation's code are all rejected with "This invitation code is invalid or has already been used."
- [ ] Registering with an email different from the invited address works.
- [ ] The signup link in the invitation email prefills the code. The email's help `mailto:` link works, and the email no longer says you must use the invited address.
- [ ] Admin → Users → bulk **Email** sends from the chosen from-address. It used to fail with a missing template.

**Moderation and admin (#7, #9)**
- [ ] As a moderator, flag an act and a comment. The flag saves without errors, the author receives an email whose **Review** link opens the act, and a flag without a reason is rejected.
- [ ] Admin → Acts: the **Appreciation Count** stat counts appreciations, not acts.
- [ ] As an administrator, edit another member's act, comment and invitation; this used to give 403. Delete an act, find it with the **Trashed** filter, and **restore** it.
- [ ] As a moderator: **Users** isn't reachable, even by URL (403). Flags can be viewed and dismissed but not edited or created. There's no restore action.
- [ ] Members can't flag. The flag UI is for moderators and above.

**App and API (#4)**
- [ ] `/acts/mine` works for a signed-in member. It returned 404 before #4.
- [ ] `/invitations` and an invitation's page work.
- [ ] iOS app smoke test: feed, act detail, create, edit and delete an act, appreciate, comment, log in and log out. API URLs, route names and response shapes are unchanged, and guest pagination links now point at `/api/acts`.

## 5. Cloudflare (#13): staging zone first, then production, with the deploy

- [ ] **Remove the existing cache rule for `/`.** It sets a 31-day browser TTL and serves the cached guest homepage even to signed-in members.
- [ ] **Add a Cache Rule** named "Cache guest pages". Replace `actkind-session` with the environment's `SESSION_COOKIE`:
  ```
  (http.request.method in {"GET" "HEAD"}
   and (http.request.uri.path in {"/" "/acts" "/terms" "/policy" "/about"}
        or (starts_with(http.request.uri.path, "/acts/") and http.request.uri.path ne "/acts/mine"))
   and not http.cookie contains "actkind-session"
   and not http.cookie contains "remember_web_")
  ```
  - Cache eligibility: **Eligible for cache**
  - Edge TTL: **Use cache-control header if present, bypass cache if not**
  - Browser TTL: **Respect origin TTL**
- [ ] **Caching → Configuration → Browser Cache TTL: Respect Existing Headers.**
- [ ] **After the deploy, purge** `/`, `/acts`, `/about`, `/terms` and `/policy`.

## 6. After deploying (staging, then production)

- [ ] `php artisan migrate:status` shows both September migrations as run.
- [ ] Guest page caching:
  ```bash
  curl -sI https://actkind.online/acts | grep -iE 'cache-control|set-cookie|cf-cache-status'   # run twice
  ```
  Expect `cache-control: max-age=0, public, s-maxage=300`, **no `set-cookie`** except Cloudflare's own `__cf_bm`, and `cf-cache-status` going `MISS` then `HIT`.
- [ ] Session requests bypass the cache:
  ```bash
  curl -sI -H 'Cookie: actkind-session=x' https://actkind.online/acts | grep -iE 'cache-control|cf-cache-status'
  ```
  Expect `private`, and `cf-cache-status` not `HIT`.
- [ ] ⚠️ Production also sets a **random-named httponly cookie** that nothing in the codebase creates, probably from the hosting side. If it still appears on cookieless guest responses, those pages are sent `private`: safe, but Cloudflare won't cache them. Find where it comes from before expecting `HIT`s.
- [ ] Repeat the XSS and log-in-then-view checks from step 4 on production.
- [ ] Check the application logs and error tracking for new exceptions, especially 403s from the new policies and `Route [...] not defined`.

## 7. Known effects and caveats

- **Browsers that loaded `/` in the last 31 days** keep that copy until it expires or the visitor hard-refreshes. Cloudflare can't purge browser caches.
- **Guests may see new acts up to 5 minutes late** on edge-cached pages.
- **Behavior changes to tell moderators and admins about:**
  - Administrators can now edit and restore members' content.
  - Moderators no longer have user management, and can't edit or create flags.
  - Flags require a reason.
- **Behavior changes for members and guests:**
  - Signed-out visitors no longer see comments or author names.
  - Invitation codes are now enforced, but the sign-up email may differ from the invited address.
- **Rolling back:** both migrations are up-only. Redeploying the previous release is safe with the migrated permissions. The old code checked `update {subject}`, which would be gone, so administrators would go back to getting 403 on edits, as before. Revert the Cloudflare rule to match.
