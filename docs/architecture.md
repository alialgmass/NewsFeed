# Architecture

## Overview

NewsFeed follows a **modular monolith** architecture built on Laravel 13. The application is organized into domain modules using `nwidart/laravel-modules`.

## System Design

```
External API Client
      |
      v
  API Gateway (Sanctum/ApiKey)
      |
      +------+------+
             |             |
        Rate Limit    Auth Check
             |             |
             v             v
        GatewayController
             |
             v
        JSON Response
```

## Authentication Architecture

The application supports three authentication pathways:

### 1. Web Sessions (Fortify)
- Laravel Fortify handles login, registration, password reset, email verification
- 2FA TOTP with confirmation flow
- Passkeys (WebAuthn) via `@laravel/passkeys`
- Session-based, routed through `routes/web.php` and `routes/settings.php`

### 2. API Tokens (Sanctum)
- Token-based auth for API consumers
- Ability scoping (e.g., `refresh`, `*`)
- Routes in `Modules/Auth/routes/api.php`
- Token management via `Modules/Auth/Http/Controllers/Api/TokenController`

### 3. API Keys (Gateway Module)
- Key-based authentication for external services
- IP whitelist, rate limit tiers, and expiry
- Routes in `Modules/Gateway/routes/api.php`
- Handled by `AuthenticateApiKey` middleware

## Modular Structure

Each module follows a consistent structure:

```
Modules/{Module}/
├── Http/
│   ├── Controllers/
│   │   ├── {Module}Controller.php
│   │   └── Api/                       (API controllers)
│   └── Middleware/                    (module-specific middleware)
├── Models/
├── Services/
├── Events/
├── Listeners/
├── database/
│   └── migrations/
├── routes/
│   ├── web.php
│   └── api.php
├── Providers/
│   └── {Module}ServiceProvider.php
└── tests/
```

## Personalization Engine

The feed personalization lives in `Modules/Feed/Services/NewsItemService`:

1. User interest levels are fetched with a 5-minute cache (`user_interests:{id}`)
2. Items are ranked using a `CASE WHEN` SQL expression matching interest categories
3. Priority ranked via `CASE WHEN` SQL expression — lower ordinal = higher rank
4. Fetched via cursor pagination for performance
5. Falls back to chronological ordering when no interests exist
6. Reading activity triggers `NewsItemRead` event for auto-interest tracking



## Database

### Key Tables

| Table | Module | Purpose |
|---|---|---|
| `users` | Core | Fortify 2FA + passkey columns |
| `news_categories` | Feed | Hierarchical translatable categories |
| `news_items` | Feed | Translatable articles with media |
| `interest_categories` | User | User-category interest scores |
| `api_keys` | Gateway | Rate limits, IP whitelist, expiry |
| `personal_access_tokens` | Sanctum | API tokens |
| `media` | Media Library | File attachments |
| `passkeys` | Fortify | WebAuthn credentials |

### Caching Strategy

- User interests: `user_interests:{id}`, 5-minute TTL
- Personalized feed: SQL-level with indexed composite key `(new_category_id, published_at)`
- Autocomplete suggestions: `autocomplete:{query}:{limit}`, 1-hour TTL. Cleared on new term recording
- Standard Eloquent model caching where applicable

## Middleware Pipeline

Custom middleware (20 total) includes:

- **Auth**: `AuthenticateApiKey`, `RoleMiddleware`
- **Security**: IP whitelist, CORS, session security headers
- **Multi-tenancy**: `InitializeTenancyByAuth`, tenant maintenance mode
- **Feature**: Locale, appearance (theme), rate limiting
## Error Handling

- Standard Laravel exception handling
- Custom `ApiException` for API responses
