---
paths:
  - 'app/**'
---

# App

## Run PHPStan before pushing
CI's lint job runs `./vendor/bin/phpstan analyse` (Larastan, level 3, `app/`) and fails the pipeline on any error, even when all tests pass. The local pre-commit hook only runs Pint and Prettier, so run `./vendor/bin/phpstan analyse` yourself before every push and fix errors at the source (no ignores, baselines or inline `@var` overrides). Common trap: Eloquent relations without generic return types (`/** @return HasMany<Act, $this> */`) stop Larastan from resolving the related model's scopes through the relation.
