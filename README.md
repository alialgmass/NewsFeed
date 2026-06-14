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
| Frontend | Vue 3 + TypeScript |
| SPA | Inertia.js v3 |
| CSS | Tailwind CSS v4 |
| Auth | Laravel Fortify + Sanctum |
| Build | Vite 8 |
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
# Clone and install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Build frontend
npm run build

# Start dev server
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
| `npm run dev` | Vite HMR dev server |
| `npm run build` | Production build |
| `npm run build:ssr` | Build with SSR |
| `npm run lint` | ESLint fix |
| `npm run format` | Prettier format |

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
│   ├── Search/           # Search (placeholder)
│   └── User/             # User interests
├── resources/
│   ├── js/               # Vue 3 frontend
│   │   ├── pages/        # Inertia page components
│   │   ├── layouts/      # Layout components
│   │   ├── components/   # Reusable UI components
│   │   ├── composables/  # Vue composables
│   │   └── types/        # TypeScript type definitions
│   └── views/            # Blade templates (root SPA entry)
├── routes/               # Web, API, settings, console routes
├── config/               # Application configuration
├── database/             # Migrations, factories, seeders
└── tests/                # PHPUnit tests
```

## Modules

- **Auth** — Sanctum token-based API authentication (login, register, token management, profile)
- **Feed** — Core news feed with `NewsItem` and `NewsCategory` models, personalized ranking via `NewsItemService`
- **Gateway** — API key management with rate limiting, IP restrictions, and webhook dispatching
- **User** — Interest categories with weighted scoring to personalize feed ordering
- **Search** — Placeholder for future search functionality

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
# PHP code style
composer run lint          # Auto-fix
composer run lint:check    # Check only

# Frontend linting/formatting
npm run lint:check         # ESLint
npm run format:check       # Prettier
npm run types:check        # TypeScript (vue-tsc)

# Full CI check
composer run ci:check
```

## Deployment

This application can be deployed using [Laravel Cloud](https://cloud.laravel.com/).

For manual deployment, ensure:
- Environment is properly configured (`.env`)
- Database migrations have run
- Queue worker is running for queued jobs
- Vite assets are built (`npm run build`)

## License

MIT
