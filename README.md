# NewsFeed

A personalized news aggregation platform built with **Laravel 13**, **Vue 3**, and **Inertia.js v3**.

## Features

- **Personalized News Feed** — Cursor-paginated feed ranked by user interest categories with caching
- **Multi-Auth Support** — Web sessions (Fortify), API tokens (Sanctum), and passkeys (WebAuthn)
- **Two-Factor Authentication** — TOTP-based 2FA with confirmation flow
- **API Gateway** — Key-based authentication with IP whitelisting, rate limit tiers, and webhook support
- **User Interests** — Interest categories with weighted scoring for feed personalization
- **Spatie Translatable** — News items and categories support multi-language content
- **Media Attachments** — Image/file attachments on news items via Spatie Media Library
- **Dark/Light/System Theme** — SSR-persistent theme selection with cookie support
- **Modular Architecture** — Domain modules via nwidart/laravel-modules

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4 / Laravel 13 |


| CSS | Tailwind CSS v4 |
| Auth | Laravel Fortify + Sanctum |
| Build | Vite |
| Database | SQLite (dev) |
| Testing | PHPUnit 12 |
| Modules | nwidart/laravel-modules |

## Requirements

- PHP ^8.3
- Composer
- Node.js 18+
- SQLite (or your database of choice)
## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
composer run dev
```

The `composer run dev` command concurrently starts:
- `php artisan serve` — Laravel dev server
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log viewer
- `npm run dev` — Vite HMR

## Available Scripts

### Composer

| Command | Description |
|---|---|
| `composer run dev` | Start all dev servers concurrently |
| `composer run lint` | Auto-fix PHP code style (Pint) |
| `composer run test` | Run lint check + PHPUnit tests |
| `composer run setup` | Full project setup (install + migrate + build) |

### NPM

| Command | Description |
|---|---|
| `npm run build` | Production build |

## Project Structure

```
├── app/                  # Core Laravel application
│   ├── Actions/          # Fortify actions
│   ├── Concerns/         # Reusable traits
│   ├── Enums/            # Application enums
│   ├── Exceptions/       # Custom exceptions
│   ├── Http/             # Controllers, middleware, requests, resources
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   └── Support/          # Domain support layer (Api, Media, Tenant, etc.)
├── Modules/              # Domain modules (nwidart/laravel-modules)
│   ├── Auth/             # Authentication (Sanctum tokens)
│   ├── Feed/             # News feed (items, categories, personalization)
│   ├── Gateway/          # API gateway (keys, webhooks)
│   ├── Search/           # Search (autocomplete, tracking)
│   └── User/             # User interests
├── resources/
│   └── views/            # Blade templates
├── routes/               # Web, API, settings, console routes
├── config/               # Application configuration
├── database/             # Migrations, factories, seeders
└── tests/                # PHPUnit tests
```

## Modules

- **Feed** — Core news feed with `NewsItem` (translatable, media) and `NewsCategory` (hierarchical) models. Personalized ranking via `NewsItemService` using `CASE WHEN` SQL priority. Cursor-paginated API. [`docs/feed.md`](docs/feed.md)
- **Search** — Trie-based autocomplete engine with frequency tracking. Provides `suggest` and `track` API endpoints with 1-hour result caching. [`docs/search.md`](docs/search.md)
- **Auth** — Sanctum token-based API authentication (login, register, token management, profile)
- **Gateway** — API key management with rate limiting, IP restrictions, and webhook dispatching
- **User** — Interest categories with weighted scoring to personalize feed ordering

## Testing

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test --compact tests/Feature/ExampleTest.php

# Filter by test name
php artisan test --compact --filter=testName
```

## Code Quality

```bash
composer run lint          # Auto-fix
composer run lint:check    # Check only
composer run ci:check      # Full CI check
```

## Deployment

This application can be deployed using [Laravel Cloud](https://cloud.laravel.com/).

For manual deployment, ensure:
- Environment is properly configured (`.env`)
- Database migrations have run
- Queue worker is running for queued jobs
- Vite assets are built (`npm run build`)

## License

## Documentation

- [`docs/architecture.md`](docs/architecture.md) — System architecture, authentication, middleware
- [`docs/api.md`](docs/api.md) — API reference (Sanctum, Gateway, response format)
- [`docs/modules.md`](docs/modules.md) — All modules reference with models, services, routes
- [`docs/feed.md`](docs/feed.md) — Feed (News) module deep-dive: personalization, endpoints, database
- [`docs/search.md`](docs/search.md) — Search module deep-dive: Trie autocomplete, tracking, caching

## License

MIT
