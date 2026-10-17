# Production deploys only on published GitHub releases

**Completed:** 2026-10-17 · Branch `feature/2026-10-release-deploys` (based on `main`, merged before the October stack so its merges don't deploy)

## Context
Every push to `main` deployed to production twice over: once through the `main_pipeline.yml` deploy hook, and once through Laravel Cloud's own push-to-deploy on the `main` environment. The user wants live deploys to be intentional: only when a release is made in GitHub.

## What changed
- **`main_pipeline.yml`:**
  - Pushes to `main` still run lint and tests.
  - The `deploy` job now runs only on `release: published`, when the release targets `main` and isn't a pre-release.
  - The hook sends the release's commit (`github.sha`).
  - Concurrency is split by event, so a push run can't cancel a release deploy, and release runs are never cancelled.
- **`failed-notification.yml`:** the Slack message links the release when a release run fails.
- **`AGENTS.md`:** documents the release-only deploy.
- **Laravel Cloud (manual, dashboard):** turn off push-to-deploy on the `main` environment. The Cloud CLI can't change it.

## Files touched
`.github/workflows/main_pipeline.yml`, `.github/workflows/failed-notification.yml`, `AGENTS.md`.

## Verification
- `actionlint` passes.
- The real check is the first release after merge: the pipeline's `deploy` job runs and production gets the tagged commit. A push to `main` should run lint and tests with `deploy` skipped, and no Cloud deployment.
