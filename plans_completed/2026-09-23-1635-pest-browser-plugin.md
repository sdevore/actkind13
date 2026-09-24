# Pest browser plugin for `--agent` probes

**Completed:** 2026-09-23 · Branch `feature/2026-09-pest-browser` (stacked on `feature/2026-09-policy-tests`)

## Context
The user asked for `pestphp/pest-plugin-browser` to be added once the other work was done, so `pest --agent` probes can drive a real browser. It follows `2026-09-23-1604-pest-tia-agent-and-phpstan-plugins.md`, which added the Agent plugin.

## What changed
- **➕ `pestphp/pest-plugin-browser`** as a dev dependency. This added only new packages (the amphp and revolt async stack), with no upgrades. The `post-update-cmd` `boost:update` also refreshed the `testing-best-practices` skill with browser-test guidance, committed alongside.
- **➕ Playwright 1.63.0** as an npm dev dependency. Only Chromium (Pest's default) was installed locally, into the per-machine cache `~/Library/Caches/ms-playwright`.
- **🙈 `.gitignore`:** `/tests/Browser/Screenshots`, as the plugin docs recommend.
- **📝 `AGENTS.md`:** covers the one-time `npx playwright install chromium`, the `@data-test` selector advice, and the CI note.

## Verification
- **Homepage check** passes, with `assertNoJavaScriptErrors`.
- **Deliberately wrong assertion** fails, which confirms a real browser is running.
- **Mobile screenshot** captured.
- **Login flow** (fill the form, `click("@login-button")`, `assertPathIs("/dashboard")`, then `assertAuthenticatedAs` on the server) passes in about 1.5s.
- **Selector trap:** the first login attempt used `press("Log in")`, which clicked the header's "Log in" link instead of the submit button. The failure screenshot showed an empty login form, so it's documented in `AGENTS.md`.
- **Checks:** full suite 240/240, PHPStan 0 errors.

## Follow-ups
- CI doesn't install a browser. That's fine while the suite has no permanent `tests/Browser` tests, but adding one needs a Playwright install step in `tests.yml`.
- Only Chromium is installed locally. `npx playwright install firefox webkit` enables `--browser firefox|safari`.
- The remaining testing gaps, web pages (7) and public cache headers on `/acts` (8), could now also use browser tests where JavaScript is involved.
