# Modules

The application uses `nwidart/laravel-modules` for domain-driven modular architecture.

## Module Status

| Module | Status | Migrations |
|---|---|---|
| Auth | Active | — (uses core migrations) |
| Feed | Active | 3 (categories, items, indexes) |
| Gateway | Active | 1 (api_keys table) |
| User | Active | 1 (interest_categories table) |
| Search | Active | 1 (search_terms table) |

## Inter-Module Dependencies

- **Feed** depends on **User** for interest-based personalization
- **Gateway** is standalone but can authenticate via core Sanctum tokens
- **Auth** is standalone (uses core User model)
- **Search** is standalone (operates on its own `search_terms` table)

All modules share the core `App\Models\User` model and Laravel's base service container.

---

## Feed (News) Module

Core news aggregation and personalization module.

### Models

#### NewsItem

`Modules/Feed/Models/NewsItem.php` — Translatable news article with media support.

| Field | Type | Description |
|---|---|---|
| `title` | string (translatable) | Article title in multiple languages |
| `slug` | string | URL-friendly identifier |
| `description` | text (translatable) | Short summary |
| `body` | text | Full article content |
| `published_at` | timestamp (nullable) | When the article was published |
| `source` | json (nullable) | Source metadata (`{url, name}`) |
| `new_category_id` | foreign id | Belongs to `NewsCategory` |

**Relations:**
- `category()` — BelongsTo `NewsCategory`
- Media via Spatie `InteractsWithMedia` (collection: `cover`)

**Traits:** `HasFactory`, `HasTranslations`, `InteractsWithMedia`

#### NewsCategory

`Modules/Feed/Models/NewsCategory.php` — Hierarchical, translatable category tree.

| Field | Type | Description |
|---|---|---|
| `title` | string (translatable) | Category name |
| `slug` | string | URL-friendly identifier |
| `description` | text (translatable) | Category description |
| `parent_id` | foreign id (nullable) | Self-referencing parent category |

**Relations:**
- `parent()` — BelongsTo self (`NewsCategory`)
- `interests()` — HasMany `InterestCategory` (links to User module)

**Traits:** `HasFactory`, `HasTranslations`

### Services

#### NewsItemService

`Modules/Feed/Services/NewsItemService.php` — Personalized feed engine.

```php
getPaginatedFeed(User $user, int $perPage = 15): CursorPaginator
getNewsItemDetail(NewsItem $newItem, User $user): array
```

**Personalization Algorithm:**
1. Fetches user's interest categories from cache (`user_interests:{id}`, 5 min TTL)
2. If interests exist: queries `NewsItem` filtered by those categories, ranked by interest level priority using `CASE WHEN` SQL
3. If no interests: falls back to chronological ordering (`latest('published_at')`)
4. Uses cursor pagination for performance

**Reading Tracking:** `getNewsItemDetail()` fires `NewsItemRead` event with the category ID and user.

### Events

#### NewsItemRead

`Modules/Feed/Events/NewsItemRead.php`

```php
new NewsItemRead(int $new_category_id, User $user)
```

Fired when a user views a news item detail. Consumed by the User module's `AddInterestOnNewsItem` listener (skeleton) for future auto-interest tracking.

### Controllers

#### FeedController (Web)

`Modules/Feed/Http/Controllers/FeedController.php`

Standard resource controller using Blade views (`feed::index`, `feed::create`, `feed::show`, `feed::edit`). Routes require `auth` + `verified` middleware.

| Method | Route | Description |
|---|---|---|
| `index()` | `GET /feeds` | List feed (Blade view) |
| `create()` | `GET /feeds/create` | Create form |
| `store()` | `POST /feeds` | Store (empty) |
| `show($id)` | `GET /feeds/{id}` | Show item |
| `edit($id)` | `GET /feeds/{id}/edit` | Edit form |
| `update()` | `PUT /feeds/{id}` | Update (empty) |
| `destroy()` | `DELETE /feeds/{id}` | Delete (empty) |

#### NewsItemController (API)

`Modules/Feed/Http/Controllers/Api/NewsItemController.php`

Extends `ApiController` for standardized JSON responses. Routes require `auth:sanctum`.

| Method | Route | Description |
|---|---|---|
| `index()` | `GET /api/feeds` | Paginated personalized feed |
| `show()` | `GET /api/feeds/{newsItem}` | Item detail + track read |

**Response Format:**
```json
{
    "custom_code": 200,
    "status": "success",
    "message": "",
    "body": {
        "news": { /* paginated or single item */ },
        "user_interests": [ /* user's InterestCategory list */ ]
    },
    "info": null
}
```

### Factories

#### NewsItemFactory

`Modules/Feed/Database/Factories/NewsItemFactory.php`

```php
NewsItem::factory()->create();                     // Basic item
NewsItem::factory()->withSubCategory()->create();   // Item in a subcategory
```

- Generates translatable `title` and `description` (en + ar)
- Random `body`, `published_at`, `source` (url + company name)
- Creates related `NewsCategory` via factory
- `configure()`: On creation, downloads a random image from picsum.photos as `cover` media (silently fails on network error)

#### NewsCategoryFactory

`Modules/Feed/Database/Factories/NewsCategoryFactory.php`

```php
NewsCategory::factory()->create();            // Root category
NewsCategory::factory()->withParent()->create(); // Subcategory
```

- Generates translatable `title` and `description` (en + ar)
- `withParent()` state creates a parent `NewsCategory` automatically

### Routes

**Web** (`Modules/Feed/routes/web.php`):
```
GET|HEAD  feeds              feed.index
POST      feeds              feed.store
GET|HEAD  feeds/create       feed.create
GET|HEAD  feeds/{feed}       feed.show
DELETE    feeds/{feed}       feed.destroy
PUT|PATCH feeds/{feed}       feed.update
GET|HEAD  feeds/{feed}/edit  feed.edit
```

**API** (`Modules/Feed/routes/api.php`):
```
GET|HEAD  api/feeds              api.feed.index
POST      api/feeds              api.feed.store
GET|HEAD  api/feeds/{feed}       api.feed.show
PUT|PATCH api/feeds/{feed}       api.feed.update
DELETE    api/feeds/{feed}       api.feed.destroy
```

### Database

**`news_categories`**:
| Column | Type | Constraints |
|---|---|---|
| id | bigint | PK |
| title | string | |
| slug | string | |
| description | text | |
| parent_id | bigint | FK → news_categories, nullable |
| timestamps | | |

**`news_items`**:
| Column | Type | Constraints |
|---|---|---|
| id | bigint | PK |
| title | string | Indexed |
| slug | string | Indexed |
| description | text | |
| body | text | |
| published_at | timestamp | Nullable |
| source | json | Nullable |
| new_category_id | bigint | FK → news_categories |
| timestamps | | |

**Indexes:**
- `idx_news_items_category_published` on `news_items(new_category_id, published_at)`

### Architecture Diagram

```
                    ┌───────────────┐
                    │  NewsItem     │
                    │  (Translatable│
                    │   + Media)    │
                    └──────┬───────┘
                           │ belongsTo
                           ▼
                    ┌───────────────┐
                    │ NewsCategory  │
                    │ (Hierarchical │◄──── self-referencing parent_id
                    │  Translatable)│
                    └──────┬───────┘
                           │ hasMany
                           ▼
                    ┌───────────────┐
                    │InterestCategory│ ──── User module
                    │ (level score) │
                    └───────────────┘
```

---

## Search Module

Autocomplete and search tracking module with a Trie-based suggestion engine. Routes are name-spaced under `api.search.*`.

### Models

#### SearchTerm

`Modules/Search/Models/SearchTerm.php`

| Column | Type | Constraints |
|---|---|---|
| id | bigint | PK |
| term | string(100) | Unique, indexed |
| frequency | unsigned int | Default 1 |
| timestamps | | |

Indexed on `frequency` for trending queries.

### Services

Search uses an **in-memory Trie** data structure for efficient prefix-based autocomplete, rebuilt on each request from the database.

#### AutocompleteService

`Modules/Search/Services/AutocompleteService.php`

```php
suggest(string $query, int $limit = 10): array
recordSearch(string $term): void
```

- `suggest()`: Returns autocomplete suggestions with frequency-based ranking. Minimum query length: 2 chars. Results cached for 1 hour (`autocomplete:{query}:{limit}`).
- `recordSearch()`: Records a search term (upserts frequency counter) and clears relevant cache prefix.

#### SearchTracker

`Modules/Search/Services/SearchTracker.php`

```php
record(string $term): SearchTerm
trending(int $limit = 10): array
```

- `record()`: Increments frequency counter for a search term (case-insensitive, trimmed)
- `trending()`: Returns top N search terms by frequency

#### Trie

`Modules/Search/Services/Trie.php`

```php
insert(string $word, int $weight = 0): void
search(string $prefix): array
```

A prefix tree (trie) for fast autocomplete lookups. Supports multibyte characters.

- `insert()`: Builds the trie node chain for a word, storing weight at the terminal node
- `search()`: Traverses to the prefix node, collects all descendant words, and sorts by weight descending. Returns `[{value, weight}, ...]`.

#### TrieNode

`Modules/Search/Services/TrieNode.php`

| Property | Type | Description |
|---|---|---|
| `children` | array | Child nodes keyed by character |
| `value` | string|null | Complete word if this is an end node |
| `isEnd` | bool | Whether this node terminates a word |
| `weight` | int | Frequency weight of the word |

### Controllers

#### AutocompleteController

`Modules/Search/Http/Controllers/AutocompleteController.php`

Extends `ApiController` for standardized JSON responses.

| Endpoint | Method | Description |
|---|---|---|
| `GET /api/search/autocomplete?q={query}&limit={n}` | `suggest()` | Returns ranked autocomplete suggestions |
| `POST /api/search/autocomplete/track` | `track()` | Records a search term for frequency tracking |

**Request:**
```json
// GET /api/search/autocomplete?q=lar&limit=5
// Response: 200
{
    "custom_code": 200,
    "status": "success",
    "message": "",
    "body": [
        {"value": "laravel", "weight": 42},
        {"value": "laravel news", "weight": 15}
    ],
    "info": null
}

// POST /api/search/autocomplete/track
// Body: { "q": "laravel" }
// Response: 200
{
    "custom_code": 200,
    "status": "success",
    "message": "Search term recorded",
    "body": {},
    "info": null
}
```

### Validation

`Modules/Search/Http/Requests/AutocompleteRequest.php`

| Field | Rules |
|---|---|
| `q` | Required, string, min:2, max:100 |
| `limit` | Optional, integer, min:1, max:50 |

### Routes

**API** (`Modules/Search/routes/api.php`):
```
GET   /api/search/autocomplete   api.search.autocomplete.suggest
POST  /api/search/autocomplete/track  api.search.autocomplete.track
```

**Web** (`Modules/Search/routes/web.php`): API only — no web routes.

### Database

**`search_terms`**:
| Column | Type | Constraints |
|---|---|---|
| id | bigint | PK |
| term | string(100) | Unique |
| frequency | unsigned int | Default 1, Indexed |
| timestamps | | |

### Architecture Diagram

```
     Client Request
          │
          ▼
  AutocompleteController
          │
          ├── suggest(q, limit) ──► AutocompleteService
          │                              │
          │                              ├── buildTrie() ← SearchTerm DB
          │                              ├── Cache::remember() (1hr)
          │                              └── Trie.search(prefix)
          │
          └── track(q) ──► SearchTracker
                               │
                               └── SearchTerm::firstOrNew()
                                   → increment('frequency')
                                   → save()

     Trie (in-memory)
     ┌──────────────────────────┐
     │  root                    │
     │  └── l                   │
     │      └── a               │
     │          └── r           │
     │              ├── a (end, weight:42)  → "laravel"
     │              └── v (end, weight:15)  → "laravel news"
     └──────────────────────────┘
```

---

## Auth Module

Sanctum token-based API authentication module.

**Routes:** `Modules/Auth/routes/api.php` — Login, register, token management, profile

**Controllers:**
- `Api\LoginController` — Email/password token issuance
- `Api\RegisterController` — User registration with token
- `Api\TokenController` — Token CRUD with ability scoping
- `Api\ProfileController` — User profile management

**Key Features:**
- Ability-scoped tokens (`refresh`, `*`, etc.)
- Token listing and revocation
- Rate limited authentication endpoints

## Gateway Module

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

## User Module

User interest profiling for feed personalization.

**Models:**
- `InterestCategory` — Links users to news categories with weighted `level` score

| Column | Type | Constraints |
|---|---|---|
| id | bigint | PK |
| new_category_id | bigint | FK → news_categories |
| user_id | bigint | FK → users |
| level | double | Default 0 |

**Indexes:**
- Unique on `(user_id, new_category_id)` — one interest level per user per category
- Composite on `(user_id, new_category_id, level)` — optimized for feed queries

**Listeners:**
- `AddInterestOnNewsItem` — Auto-track interest from reading behavior (skeleton)

**Routes:**
- `Modules/User/routes/api.php` — Interest management API
- `Modules/User/routes/web.php` — Web routes

## Creating a New Module

```bash
php artisan module:make ModuleName
```

This scaffolds the module directory structure. Register it in `modules_statuses.json` to enable/disable. Each module should follow the standard structure:

```
Modules/{Module}/
├── Http/
│   ├── Controllers/
│   │   ├── {Module}Controller.php
│   │   └── Api/
│   └── Middleware/
├── Models/
├── Services/
├── Events/
├── Listeners/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── Providers/
│   ├── {Module}ServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── config/
│   └── config.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── module.json
```
