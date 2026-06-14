# Modules

The application uses `nwidart/laravel-modules` for domain-driven modular architecture.

## Active Modules

### Auth

Sanctum token-based API authentication module.

**Routes:**
- `Modules/Auth/routes/api.php` — Login, register, token management, profile
- `Modules/Auth/routes/web.php` — Web routes (if applicable)

**Controllers:**
- `Api\LoginController` — Email/password token issuance
- `Api\RegisterController` — User registration with token
- `Api\TokenController` — Token CRUD with ability scoping
- `Api\ProfileController` — User profile management

**Key Features:**
- Ability-scoped tokens (`refresh`, `*`, etc.)
- Token listing and revocation
- Rate limited authentication endpoints

### Feed

Core news aggregation and personalization module.

**Models:**
- `NewsItem` — Title, slug, description, body, published_at, source (JSON), translatable, media attachments
- `NewsCategory` — Hierarchical categories (parent/child), translatable

**Services:**
- `NewsItemService` — Personalized feed engine with cursor pagination

**Events:**
- `NewsItemRead` — Fired when a user reads an item

**Routes:**
- `Modules/Feed/routes/web.php` — FeedController (web/Blade)
- `Modules/Feed/routes/api.php` — Api\NewsItemController (API)

**Personalization:**
1. Queries user interest levels (cached 5 min)
2. Applies `CASE WHEN` SQL ranking
3. Falls back to chronological ordering
4. Returns cursor-paginated results

### Gateway

API gateway for third-party integrations.

**Models:**
- `ApiKey` — Key management with rate limits, IP whitelist, expiry, metadata

**Controllers:**
- `GatewayController` — API key-gated endpoints
- `WebhookController` — Webhook ingestion

**Middleware:**
- `AuthenticateApiKey` — Validates `X-API-Key` header
- `ValidateWebhookSignature` — HMAC signature validation

**Routes:**
- `Modules/Gateway/routes/api.php` — Key-authenticated endpoints
- `Modules/Gateway/routes/webhooks.php` — Webhook endpoints

### User

User interest profiling for feed personalization.

**Models:**
- `InterestCategory` — Links users to news categories with weighted `level` score

**Listeners:**
- `AddInterestOnNewsItem` — Auto-track interest from reading behavior (skeleton)

**Routes:**
- `Modules/User/routes/api.php` — Interest management API
- `Modules/User/routes/web.php` — Web routes

### Search

Placeholder module for future search functionality. Currently has only an `app/Http/` skeleton directory.

## Module Status

| Module | Status | Migrations |
|---|---|---|
| Auth | Active | — (uses core migrations) |
| Feed | Active | 3 (categories, items, indexes) |
| Gateway | Active | 1 (api_keys table) |
| User | Active | 1 (interest_categories table) |
| Search | Placeholder | — |

## Creating a New Module

```bash
php artisan module:make ModuleName
```

This scaffolds the module directory structure. Register it in `modules_statuses.json` to enable/disable.

## Inter-Module Dependencies

- **Feed** depends on **User** modules for interest-based personalization
- **Gateway** is standalone but can authenticate via core Sanctum tokens
- **Auth** is standalone (uses core User model)
- **Search** (future) will depend on **Feed** models

All modules share the core `App\Models\User` model and Laravel's base service container.
