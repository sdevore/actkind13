# Admin count column header icons (SCI-113)

## Context

The icon-only count columns in the admin tables (Acts: appreciates, comments, flags; Users: comments, flags) rendered their header icons through `Blade::render('<x-icon ... class="mr-2 h-4 w-4" />')`. Filament's stylesheet doesn't include those Tailwind utilities, so the Font Awesome SVGs had no fixed size and no color. In light mode they came out at uneven sizes; in dark mode they were black on the dark header, leaving only the sort arrows visible. They also had no text alternative for screen readers.

## Linear and branch

- SCI-113 (Admin, Bug), branch `sdevore/sci-113-admin-acts-and-users-tables-count-column-header-icons`.

## What changed

- `app/Filament/Resources/Acts/ActResource.php` and `app/Filament/Resources/Users/UserResource.php`: each has a private `iconLabel($icon, $label)` that renders the header icon with Filament's `generate_icon_html(..., size: IconSize::Small)`, so it gets Filament's `fi-icon fi-size-sm` sizing and theme color. The SVG carries `role="img"` and `aria-label`, so screen readers announce the column by name while the header stays icon-only.
- A `sr-only` text span was tried first, but Filament's panel CSS doesn't apply that utility in table headers, so the text showed visibly. The `aria-label` route needs no CSS.
- New `tests/Feature/Filament/CountColumnHeadersTest.php`: a dataset over all five columns checks the header names its icon for screen readers. It fails on the old code.

## Verification

- Browser checks of both tables in light and dark mode: every header icon is 16×16 in the theme's gray, with no JavaScript errors.
- Filament tests 50/50, PHPStan 0 errors, Pint clean.

## Follow-ups

- None.
