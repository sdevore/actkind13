# Real appreciation sparkline on the admin Acts page

**Completed:** 2026-10-17 · Branch `feature/2026-10-appreciation-sparkline` (stacked on `feature/2026-10-appreciate-soft-deletes`) · Linear SCI-83

## Context
`AppreciatesStatsWidget` showed a hardcoded placeholder sparkline (`[1, 2, 3, 4, 5, 4, 3, 2, 1]`), first noted in `2026-09-23-1545-admin-moderation-tests.md`.

## What changed
- The sparkline is now daily appreciation counts for the last 14 days, built with `flowframe/laravel-trend`, which the dashboard chart widgets already use. Empty days are filled with 0.
- It uses the same scope as the count: appreciations on the acts the table currently lists, so table filters apply to both. The query is shared in `appreciationsOnListedActs()`. Trashed appreciations are excluded, which follows from SCI-78.

## Files touched
`app/Filament/Resources/Acts/Widgets/AppreciatesStatsWidget.php`, `tests/Feature/Filament/WidgetsTest.php`.

## Verification
- A new test checks that the chart has 14 points, places appreciations on the right day and sums to the total. `WidgetsTest` passes (11/11). PHPStan: 0 errors.
