---
paths:
  - '{AGENTS,CLAUDE,GEMINI}.md'
---

# General

## Boost re-duplicates CLAUDE.md/GEMINI.md guidelines on next sync
`php artisan boost:install`/`boost:update` (see `GuidelineWriter::write()`) only replaces an existing `<laravel-boost-guidelines>...</laravel-boost-guidelines>` block in place. If a file has no such block — e.g. after de-duping `CLAUDE.md`/`GEMINI.md` down to a one-line `See @AGENTS.md for ...` pointer — Boost appends a brand-new full guidelines block to the end of the file instead of leaving the pointer alone. So the de-duped pointer state does NOT survive a future Boost sync; after any `boost:install`/`boost:update`, re-check `CLAUDE.md`/`GEMINI.md` and re-apply the single-line pointer if a duplicated block has reappeared. `AGENTS.md` is the source of truth and safe to extend (append custom content after the closing `</laravel-boost-guidelines>` tag, not inside it).
