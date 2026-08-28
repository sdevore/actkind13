---
name: laravel-scramble
description: Guidance for interacting with Laravel Scramble for automated API documentation generation.
---

# Laravel Scramble Skill

Use this skill when creating, modifying, or testing API endpoints that rely on Dedoc Scramble for automated OpenAPI documentation generation.

## Context & Rules

Laravel Scramble generates OpenAPI documentation automatically from your codebase without requiring extensive annotations or PHPDoc comments. It infers types from type hints, request validation classes, and controller return statements.

### 1. Controller Implementation
- Always type-hint specific FormRequest classes in controller methods instead of generic `Request`.
- Use explicit return type hints on controller methods (e.g., `public function show(User $user): UserResource`).
- Avoid returning raw arrays or generic `json()` responses where possible; use API Resources to ensure Scramble extracts structural schemas.

### 2. Request & Validation Rules
- Define validation rules explicitly within the `rules()` method of FormRequest classes.
- Use explicit types in validation (e.g., `string`, `integer`, `boolean`, `exists:table,column`).
- Add standard PHPDoc comments above validation rules *only* when extra context is needed (e.g., `/** @var string The user's primary email address */`).

### 3. Documenting Responses
- When using anonymous API resources for collections, use the explicit collection method: `UserResource::collection($users)`.
- For complex return types involving conditional attributes, ensure the resource's `toArray($request)` method explicitly maps the expected keys to help Scramble infer the properties correctly.

### 4. Customizing Documentation Inline
- To add a description to an endpoint, write a standard PHPDoc block above the controller method. The first line becomes the summary, and subsequent lines become the description.
- Use `@tags TagName` in the controller method's PHPDoc to group routes in the API documentation.
