---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Prefer omitting @response docblocks — let Scramble infer from the return statement
Scramble parses `@response {Type}` as a plain `@return {Type}` override with NO status-code support, and the `{Type}` name resolves via the file's `use` imports — a bare class name (e.g. `@response Comment` in a file that does `use App\Models\Comment;`) silently resolves to the Model, not an identically-named Resource, even if the Resource is imported under an alias.

Worse: an explicit `@response` docblock also disables Scramble's static return-type inference, which is what matches `JsonResource` `#[SchemaVariant(whenLoaded: [...])]` variants to the relations actually loaded at that call site (see the `SchemaVariant` learning in AGENTS.md), and what picks up literal `->setStatusCode(201)` calls.

So: when a controller method returns `SomeResource::make($model)->response()->setStatusCode($code)`, do NOT add a `@response` docblock — leave the PHP return type as `JsonResponse` and let Scramble trace the return statement directly; it correctly infers both the SchemaVariant and a literal status code this way. If the status code is conditional, split it into separate `if`/`return` branches with literal `setStatusCode()` calls rather than a ternary — Scramble can trace multiple literal-status return statements (documents each as a separate response) but not a ternary expression inside the call chain.
