# Fix the admin Acts chart GROUP BY error

**Completed:** 2026-10-17 · Branch `feature/2026-10-acts-widget-grouping` · Linear SCI-90 · Nightwatch #9 (and duplicate #3)

## Context
The Acts chart on the admin dashboard failed on Postgres with `column "acts.id" must appear in the GROUP BY clause`. `ActsWidget` builds its Trend query from the `ListActs` table query. Because of the table's `counts('appreciates' | 'comments' | 'flags')` columns, that query already selects `acts.*` plus three `withCount` subselects. `Trend::aggregate()` appends its own `selectRaw()` and groups by date, which Postgres rejects. SQLite accepts the query, so the existing "widgets render" test passed.

## What changed
- Each chart widget now calls `$query->getQuery()->select([])` after clearing the orders, so only Trend's date and aggregate columns are selected. `select([])` also clears the select bindings: clearing only `columns` leaves the morph-type bindings from the count subselects behind, which shifts every later placeholder.
- The same fix is applied to `CommentsWidget` and `FlagsWidget`. They don't fail today because their tables have no `counts()` columns, but they would if one were added.

## Files touched
`app/Filament/Widgets/ActsWidget.php`, `app/Filament/Widgets/CommentsWidget.php`, `app/Filament/Widgets/FlagsWidget.php`, `tests/Feature/Filament/WidgetsTest.php`.

## Verification
- A new test logs the queries each chart widget runs and checks that its aggregate query selects neither `.*` nor any `_count` column. Before the fix it failed for `ActsWidget`. The existing render test caught the leftover-bindings problem with the first attempted fix (clearing `columns` only).
- `WidgetsTest` passes (14/14). Pint and PHPStan pass (0 errors).

## Follow-ups
- The tests run on SQLite and the local database is SQLite, so the Postgres behaviour hasn't been seen directly. Open the admin dashboard on staging (develop) after deploy and confirm that Nightwatch #9 stops recurring.
