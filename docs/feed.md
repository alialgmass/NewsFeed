# Feed (News) Module

The Feed module manages **news articles**, **categories**, and **personalized feed delivery**.

## Overview

The Feed module provides:
- Translatable news articles with media attachments
- Hierarchical news categories
- Personalized feed ranked by user interest levels
- Both web (Blade) and API endpoints
- Reading activity tracking

## Models

### NewsItem

`Modules/Feed/Models/NewsItem.php`

**Fillable attributes:**

| Attribute | Type | Description |
|---|---|---|
| `title` | string (translatable) | Article title |
| `slug` | string | URL slug |
| `description` | text (translatable) | Short excerpt |
| `body` | text | Full content |
| `published_at` | datetime (nullable) | Publication timestamp |
| `source` | json (nullable) | Source metadata |
| `new_category_id` | integer | FK to news_categories |

**Casts:**
- `published_at` → `datetime`
- `source` → `array`

**Relations:**
- `category()` — `BelongsTo(NewsCategory::class, 'new_category_id')`

**Media:** Implements `HasMedia` via Spatie Media Library. Default collection: `cover`.

**Translatable:** `title`, `description` (en, ar via factory).

### NewsCategory

`Modules/Feed/Models/NewsCategory.php`

**Fillable attributes:**

| Attribute | Type | Description |
|---|---|---|
| `title` | string (translatable) | Category name |
| `slug` | string | URL slug |
| `description` | text (translatable) | Category description |
| `parent_id` | integer (nullable) | Self-referencing FK |

**Relations:**
- `parent()` — `BelongsTo(NewsCategory::class, 'parent_id')` — self-referencing
- `interests()` — `HasMany(InterestCategory::class, 'new_category_id')` — links to User module

**Translatable:** `title`, `description` (en, ar via factory).

## Feed Personalization

### NewsItemService

`Modules/Feed/Services/NewsItemService.php`

The service provides two public methods:

```php
getPaginatedFeed(User $user, int $perPage = 15): CursorPaginator
getNewsItemDetail(NewsItem $newItem, User $user): array
```

#### Personalization Algorithm

```
getPaginatedFeed(user)
    │
    ├── getInterestCategories(user)
    │       └── Cache::remember("user_interests:{id}", 300s)
    │               └── InterestCategory::where(user_id)
    │                       → orderByDesc('level')
    │                       → pluck('new_category_id')
    │
    ├── if interests exist:
    │       personalizedFeed(categoryIds, perPage)
    │           └── NewsItem::whereIn('new_category_id', categoryIds)
    │                   → orderByRaw(CASE WHEN ... END)  ← priority ranking
    │                   → orderByDesc('published_at')
    │                   → orderByDesc('id')
    │                   → cursorPaginate(perPage)
    │
    └── if no interests:
            defaultFeed(perPage)
                └── NewsItem::latest('published_at')
                        → latest('id')
                        → cursorPaginate(perPage)
```

**Priority Ranking:**
```sql
CASE new_category_id
    WHEN 3 THEN 0    -- highest priority (user's top interest)
    WHEN 5 THEN 1
    WHEN 1 THEN 2    -- lowest priority
    ELSE 999999
END
```

Categories are ordered by the user's `level` score in `InterestCategory`. Lower `CASE WHEN` values = higher priority.

**Caching:**
- User interests cached for **5 minutes** (`user_interests:{id}`)
- Cache key is scoped per user ID
- Cleared when interest levels are updated

### Reading Event

When `getNewsItemDetail()` is called, it fires:

```php
event(new NewsItemRead(
    new_category_id: $newItem->new_category_id,
    user: $user
));
```

This event can be listened for to auto-track user interests over time. The User module has a skeleton `AddInterestOnNewsItem` listener for this purpose.

## API Endpoints

### Web (Blade)

Requires `auth` + `verified` middleware. Route names prefixed with `feed.`.

```
GET|HEAD   /feeds                  feed.index     → index.blade.php
GET|HEAD   /feeds/create           feed.create    → create.blade.php
POST       /feeds                  feed.store
GET|HEAD   /feeds/{feed}           feed.show      → show.blade.php
GET|HEAD   /feeds/{feed}/edit      feed.edit      → edit.blade.php
PUT|PATCH  /feeds/{feed}           feed.update
DELETE     /feeds/{feed}           feed.destroy
```

### API (JSON)

Requires `auth:sanctum` middleware. Route names prefixed with `api.feed.`.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/feeds` | Paginated personalized feed |
| `POST` | `/api/feeds` | Create news item |
| `GET` | `/api/feeds/{newsItem}` | Item detail (triggers read event) |
| `PUT/PATCH` | `/api/feeds/{newsItem}` | Update item |
| `DELETE` | `/api/feeds/{newsItem}` | Delete item |

**Response Format (via ApiController):**

```json
// GET /api/feeds
{
    "custom_code": 200,
    "status": "success",
    "message": "",
    "body": {
        "news": {
            "data": [
                {
                    "id": 1,
                    "title": "Breaking News",
                    "slug": "breaking-news",
                    "description": "A short summary...",
                    "published_at": "2026-06-14T10:00:00Z",
                    "source": {
                        "url": "https://example.com",
                        "name": "Example News"
                    },
                    "category": { "id": 1, "title": "Technology" },
                    "media": [ /* cover image */ ]
                }
            ],
            "next_cursor": "eyJpZCI6MTAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
            "per_page": 15
        }
    },
    "info": null
}

// GET /api/feeds/{newsItem}
{
    "custom_code": 200,
    "status": "success",
    "message": "",
    "body": {
        "news": { /* full NewsItem */ },
        "user_interests": [
            { "new_category_id": 1, "level": 0.8 },
            { "new_category_id": 3, "level": 0.5 }
        ]
    },
    "info": null
}
```

## Database Schema

### news_categories

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| title | varchar(255) | |
| slug | varchar(255) | |
| description | text | |
| parent_id | bigint unsigned | FK → news_categories, nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### news_items

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| title | varchar(255) | Indexed |
| slug | varchar(255) | Indexed |
| description | text | |
| body | text | |
| published_at | timestamp | Nullable |
| source | json | Nullable |
| new_category_id | bigint unsigned | FK → news_categories |
| created_at | timestamp | |
| updated_at | timestamp | |

### Indexes

```sql
-- Composite index for feed queries:
CREATE INDEX idx_news_items_category_published
ON news_items (new_category_id, published_at);
```

This composite index optimizes the personalized feed query:
```sql
SELECT * FROM news_items
WHERE new_category_id IN (...)
ORDER BY CASE ... END, published_at DESC, id DESC;
```

## Factories

### NewsItemFactory

```php
// Basic usage
NewsItem::factory()->create();

// With subcategory
NewsItem::factory()->withSubCategory()->create();
```

**Default state:**
- Translatable `title` (en + ar), `description` (en + ar)
- English `body`
- Random `published_at` within last year
- `source` with random URL + company name
- `new_category_id` → creates `NewsCategory` via factory

**After creating:** Downloads a cover image from picsum.photos (silently catches connection errors).

### NewsCategoryFactory

```php
// Root category
NewsCategory::factory()->create();

// Subcategory (creates parent automatically)
NewsCategory::factory()->withParent()->create();
```

**Default state:**
- Translatable `title` (en + ar), `description` (en + ar)
- `parent_id` → null (root category)
- `withParent()` → creates and links to a parent category

## Integration with User Module

The Feed module depends on the User module for personalization:

```
Feed Module                     User Module
──────────                     ────────────
NewsItem ◄─── FK ──── NewsCategory
                            │
                     InterestCategory ◄─── FK ──── User
                            │
                        level (double)
                        (interest score)
```

- `InterestCategory` stores a `level` per user per category
- Higher `level` = higher priority in the personalized feed
- The `CASE WHEN` expression in `NewsItemService` converts interest levels to SQL ordering
- Reading events (`NewsItemRead`) can be used to automatically adjust interest levels

## Caching Strategy

| Cache Key | TTL | Purpose |
|---|---|---|
| `user_interests:{user_id}` | 5 min | User's interest category IDs ordered by level |
| Feed results | N/A | Cursor pagination — no caching on results |
| Media | Per Spatie | Thumbnail/conversion caching |

## Future Enhancements

- **Auto-interest tracking** via `AddInterestOnNewsItem` listener
- **Full-text search** on news items
- **Category-based filtering** in API
- **Trending articles** based on read counts
- **Scheduled publishing** using `published_at`
