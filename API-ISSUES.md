# API issues found while building the iOS app

> **Status: all resolved.** Verified fixed on `dev.actkind.online` and reflected
> in the updated OpenAPI spec on 2026-08-20. The client workarounds described
> below have been removed. Kept as a record of what changed, and because the
> API reshaping it prompted is a breaking change for any other client.
>
> | # | Issue | Status |
> |---|-------|--------|
> | 1 | Public feed leaked `__PHP_Incomplete_Class_Name` stubs | Fixed — `appreciates` no longer returned to guests |
> | 2 | `appreciates_count` / `comments_count` were `0` on act detail | Fixed — counts correct |
> | 3 | Comments on act detail had no author | Fixed — `user` now nested on comments and appreciations |
> | 4 | Guest endpoints disagreed on which relations they returned | Fixed — both return counts only |
> | 5 | `deleted_at` exposed, feed ordered oldest-first | Fixed — `deleted_at` gone, feed now newest-first |
> | 6 | Spec drifted from the live API | Fixed — spec regenerated and matches |
>
> Two follow-ups remain open; see "Still outstanding" at the end.

Found against `https://dev.actkind.online/api` on 2026-08-19, using the
`regular@example.com` test account. Each item lists how to reproduce it, what
the API returns today, what the app expected, and how the app currently works
around it. Ordered by severity.

Issues 1–3 are correctness bugs with user-visible effects. Issue 1 is also a
data-exposure concern. Once 1–4 are fixed, the corresponding client workarounds
can be deleted.

---

## 1. `GET /api/acts` leaks broken PHP serialization instead of appreciations

**Severity: high — breaks the endpoint for any typed client, and leaks internals.**

### Reproduce
```
curl -s https://dev.actkind.online/api/acts -H 'Accept: application/json' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data'][0]['appreciates'][0])"
```

### Actual
Every element of `appreciates` is an unusable stub with no model fields:
```json
{
  "__PHP_Incomplete_Class_Name": "App\\Models\\Appreciate",
  "incrementing": true,
  "preventsLazyLoading": false,
  "exists": true,
  "wasRecentlyCreated": false,
  "timestamps": true,
  "usesUniqueIds": false
}
```

### Expected
**Omit `appreciates` entirely and expose only `appreciates_count`.**

Signed-out readers are not meant to see who appreciated an act (product
decision, confirmed 2026-08-19 — see issue 4), so the public feed has no reason
to return the array at all. That makes this the simplest fix available: drop the
relation from the guest payload rather than repairing its serialization. The
client already relies only on `appreciates_count` here.

### Likely cause
`__PHP_Incomplete_Class_Name` means a serialized model was `unserialize()`d
without its class loaded. This is the signature of a cached value (response
cache, `Cache::remember`, or a queued/serialized payload) written by a process
that didn't have `App\Models\Appreciate` autoloadable, or persisted across a
deploy that changed the class. Worth checking the cache layer in front of the
guest acts endpoint and flushing it.

### Impact
This is the highest-value fix. A strongly typed client that requires `id` on an
appreciation fails to decode the **entire** public feed — which is exactly what
happened: the signed-out feed showed a generic error and no acts at all. It also
leaks internal model/class names and ORM flags to unauthenticated callers.

### Current client workaround
`Appreciation.id` was made optional so decoding can't fail, and `Act` filters out
entries without an `id`. Remove both once this returns real records or is dropped.

---

## 2. `appreciates_count` and `comments_count` are `0` on act detail

**Severity: high — visibly wrong numbers.**

### Reproduce
```
TOKEN=$(curl -s -X POST https://dev.actkind.online/api/sanctum/token \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"regular@example.com","password":"password","device_name":"probe"}' \
  | python3 -c 'import sys,json; print(json.load(sys.stdin)["token"])')

curl -s https://dev.actkind.online/api/private/acts/1 \
  -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" \
  | python3 -c "import sys,json; d=json.load(sys.stdin)['data']; \
print('appreciates_count', d['appreciates_count'], 'actual', len(d['appreciates'])); \
print('comments_count', d['comments_count'], 'actual', len(d['comments']))"
```

### Actual
```
appreciates_count 0 actual 5
comments_count 0 actual 5
```

### Expected
`appreciates_count: 5`, `comments_count: 5`. The counts contradict the arrays
returned in the same payload. Note `GET /api/private/acts` (the list) returns
these counts **correctly** — only the single-act detail endpoint is wrong.

### Likely cause
The detail route eager-loads the relations (`with('appreciates', 'comments')`)
but doesn't add `withCount(['appreciates', 'comments'])`, so the resource
serializes the attributes as 0. Adding `withCount` to the detail query should be
a one-line fix, since the list query already does it.

### Impact
An act with 5 appreciations and 5 comments displayed "0" and "0" next to a
visible list of 5 comments.

### Current client workaround
Counts are derived from `appreciates.length` / `comments.length` whenever those
arrays are present, falling back to the count fields otherwise.

---

## 3. Comments on act detail have no author, so names can't be displayed

**Severity: medium — a feature can't be built as specified.**

### Reproduce
```
curl -s https://dev.actkind.online/api/private/acts/1 \
  -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['comments'][0])"
```

### Actual
```json
{"id": 1, "body": "…", "user_id": 7, "act_id": 1,
 "created_at": "…", "updated_at": "…", "deleted_at": null}
```
Raw model attributes with `user_id` only — and `deleted_at`, which shouldn't be
exposed at all.

### Expected
The nested `user` object, matching what `PUT /api/private/acts/{act}/comments`
and `POST /api/private/comments/{comment}` already return on create and update:
```json
{"id": 1, "act_id": 1, "body": "…", "created_at": "…", "updated_at": "…",
 "user": {"id": 7, "name": "…", "email": "…", "profile_photo_path": null}}
```

### Likely cause
The detail endpoint serializes the `comments` relation as raw models rather than
through `CommentResourceWithUser`, which the create/update endpoints already use.
The same applies to `appreciates`, which is also raw (see issue 5).

### Impact
Comments by other members render as "A kind member" instead of their name. The
signed-in reader's own comments show their name only because the app already
knows who they are. Same problem for `appreciates`, which is why the app can't
show who appreciated an act.

### Current client workaround
Falls back to "A kind member" for any comment whose `user_id` isn't the signed-in
user.

---

## 4. Guest endpoints should return no relations — the feed currently does

**Severity: medium — the two guest endpoints disagree with each other.**

**Resolved 2026-08-19:** signed-out readers must not see comments, and must not
see who appreciated an act. Counts alone are fine. This section is kept as the
specification for the guest payloads.

### Reproduce
```
curl -s https://dev.actkind.online/api/acts/1 -H 'Accept: application/json'   # detail
curl -s https://dev.actkind.online/api/acts    -H 'Accept: application/json'   # feed
```

### Actual
`GET /api/acts/{act}` (detail) is **correct**:
```
keys: id, title, description, type, created_at, updated_at,
      appreciates_count, comments_count
```
No `user`, no `comments`, no `appreciates`.

`GET /api/acts` (feed) is **wrong**: it also returns an `appreciates` array
(as broken stubs — issue 1) and `flags_count`.

### Expected
Both guest endpoints should return the detail shape above: the act, its
timestamps, and `appreciates_count` / `comments_count` — no `user`, no
`comments`, no `appreciates`. The feed should be brought in line with the
detail endpoint, not the other way around.

Fixing this resolves issue 1 at the same time, since the malformed
`appreciates` array simply stops being serialized.

### Impact on the app
None — the app already shows guests only the act, its counts, and a prompt to
sign in. It never reads `appreciates` or `comments` from a guest response. This
is about not sending data that shouldn't leave the server.

---

## 5. Smaller items

- **`deleted_at` is exposed** on raw `Act` and `Comment` payloads. Soft-delete
  bookkeeping shouldn't reach API clients; it's absent from the `*Resource`
  shapes but present wherever raw models are serialized.
- **`per_page` is ignored.** `GET /api/acts?per_page=10` still returns 12. The
  app needed the first 10 acts for signed-out visitors and had to trim
  client-side. Honouring `per_page` (with a sane maximum) would avoid
  over-fetching.
- **Feed ordering is oldest-first.** `GET /api/private/acts` page 1 starts at
  `id: 1` with `created_at` of 2025-10-28, so a newly created act lands on the
  *last* page. A social feed almost certainly wants `ORDER BY created_at DESC`.
  The app inserts locally created acts at the top, but they drop back down on
  refresh.
- **`GET /api/user` exposes more than a client needs**: `email_verified_at`,
  `current_team_id`, `two_factor_confirmed_at`, `flag_ct`. Worth narrowing to a
  `UserResource`, particularly since nested `user` objects would embed the same
  fields wherever issue 3 is fixed.
- **No registration endpoint.** Sign-up has to happen on the web; the app links
  out to `/register`. Fine if intentional — flagging it as a deliberate choice
  rather than an oversight.

---

## 6. OpenAPI spec is out of date

The spec under-describes the live API, so it can't be used to generate a client.
Differences found:

| Spec says | API actually returns |
|---|---|
| Feed items are bare `Act` (`id`, `title`, `description`, `type`, `user_id`, timestamps) | Also `user`, `appreciates_count`, `comments_count`, `appreciates`, `flags_count` |
| Paginator has no `current_page_url` | `current_page_url` is present |
| Counts are `string` or integer `0` (`anyOf`) | Plain integers |
| `Act.deleted_at` is a required non-null `date-time` | Nullable, and usually `null` |
| `/acts/{act}` returns `ActResource` | Matches, but the guest/auth split isn't documented |

Regenerating the spec from the live routes would let the client be generated
rather than hand-written, and would have caught issues 1 and 2 as contract
violations.


---

## Still outstanding

Minor, and neither blocks the app.

1. ~~**`per_page` is still ignored.**~~ Fixed — `resolvePerPage()` now backs
   every acts list endpoint (guest and private), verified live and covered by
   `ActsTest.php`.

2. **Paginator `links`/`meta` paths are wrong on private routes.**
   `GET /api/private/acts` returns `"next": "/acts?page=2"` and
   `"path": "/acts"` — the public path, not `/private/acts`. Following those
   links would silently fetch the wrong collection. The app paginates by page
   number and only reads `links.next` as a has-more flag, so it isn't affected,
   but a client that follows the URLs would be.

## Note on the reshaping

The fixes changed several response shapes in ways unrelated to the issues
reported, which is fine but worth recording since they broke the client:

- Pagination moved from a flat envelope (`current_page`, `data`,
  `next_page_url`) to `{ data, links, meta }`.
- `GET /api/user` is now wrapped in `data`.
- Acts no longer carry `user_id`; the author arrives as a nested `user`.
- `User` still includes `current_team_id` and `profile_photo_path` on the wire,
  though the updated spec omits them. Harmless, but spec and server disagree.
