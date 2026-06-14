# Search Module

The Search module provides **autocomplete suggestions** and **search term tracking** using a Trie-based engine.

## Overview

```
GET  /api/search/autocomplete?q=lar  →  ["laravel", "laravel news"]
POST /api/search/autocomplete/track   →  Records "laravel" query
```

The system stores search terms in a `search_terms` table with frequency counters and loads them into an in-memory Trie for O(k) prefix lookups.

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    AutocompleteController                │
├─────────────────────────────────────────────────────────┤
│  suggest(AutocompleteRequest $request)                  │
│  track(AutocompleteRequest $request)                    │
└────────────────────┬────────────────────────────────────┘
                     │
          ┌──────────┴──────────┐
          ▼                     ▼
  AutocompleteService      SearchTracker
  (Trie + Cache)           (DB tracking)
          │                     │
          ▼                     ▼
       Trie DB              SearchTerm
  (in-memory)               (Eloquent)
          │
          ▼
     TrieNode
  (char children)
```

## API Endpoints

### Suggest

```
GET /api/search/autocomplete
```

**Parameters:**

| Name | Type | Required | Default | Description |
|---|---|---|---|---|
| `q` | string | Yes | — | Search query (min 2, max 100 chars) |
| `limit` | integer | No | 10 | Max results (1–50) |

**Response:**
```json
{
    "custom_code": 200,
    "status": "success",
    "message": "",
    "body": [
        {"value": "laravel", "weight": 42},
        {"value": "laravel news", "weight": 15},
        {"value": "laravel packages", "weight": 8}
    ],
    "info": null
}
```

Results are sorted by `weight` (frequency) descending.

### Track

```
POST /api/search/autocomplete/track
```

**Body:**
```json
{
    "q": "laravel"
}
```

**Response:**
```json
{
    "custom_code": 200,
    "status": "success",
    "message": "Search term recorded",
    "body": {},
    "info": null
}
```

Increments the frequency counter for the given term (case-insensitive, trimmed). Creates a new `SearchTerm` record if it doesn't exist.

### Trending

```
GET /api/search/trending
```

Returns the top N most searched terms by frequency. Implementation via `SearchTracker::trending()`.

## Trie Engine

The autocomplete engine uses a **Trie (prefix tree)** for efficient prefix matching.

### Building the Trie

On each request, `AutocompleteService::buildTrie()`:
1. Queries all `SearchTerm` records ordered by frequency descending
2. Processes in chunks of 100
3. Inserts each term into the Trie with its frequency as weight

### Prefix Search

`Trie::search(string $prefix)`:
1. Traverses the Trie character by character
2. At the final prefix node, performs a DFS to collect all descendant words
3. Sorts results by weight descending
4. Returns top N results

### Caching

- Results are cached for **1 hour** (TTL: 3600s)
- Cache key: `autocomplete:{query}:{limit}`
- On `recordSearch()`, cache for the first 2 chars of the term is cleared

## Data Flow

### Search Lifecycle

1. **User types** in search box (e.g., "lar")
2. **AutocompleteController::suggest()** validates via `AutocompleteRequest`
3. **AutocompleteService::suggest()** checks cache, then builds/searches Trie
4. Returns ranked suggestions
5. **On submit**, **SearchTracker::record()** upserts the search term with incremented frequency

### Frequency Table Growth

- The `SearchTerm` table uses `firstOrNew` + `increment('frequency')`
- Terms that already exist get their counter bumped
- Frequently searched terms float to the top of suggestions
- No pruning logic implemented yet — stale low-frequency terms accumulate

## Database

**`search_terms`**:

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | Primary key, auto-increment |
| term | varchar(100) | Unique, indexed |
| frequency | int unsigned | Default 1, indexed for trending |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- Unique on `term`
- Index on `frequency`

## Validation

Rules defined in `AutocompleteRequest.php`:

| Field | Rules |
|---|---|
| `q` | `required`, `string`, `min:2`, `max:100` |
| `limit` | `sometimes`, `integer`, `min:1`, `max:50` |

## Future Enhancements

- **Search by News Content** — Query `NewsItem` title/body fields with full-text search
- **Filtered Autocomplete** — Scope suggestions by news category
- **Trie Persistence** — Cache the Trie in Redis/memory instead of rebuilding per request
- **Stale Term Pruning** — Scheduled job to clean low-frequency terms
- **Search Analytics** — Track search-to-click conversion rates
- **Multi-language** — Support translatable search terms
