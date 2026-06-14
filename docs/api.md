# API Documentation

## Overview

NewsFeed provides two API authentication pathways:

1. **Sanctum Tokens** — For internal/first-party API clients
2. **API Keys** — For external third-party services

## Sanctum Token API

All Sanctum routes are prefixed with `/api` and require token-based authentication unless noted.

### Authentication

```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
GET  /api/auth/me
```

#### Register

```json
// POST /api/auth/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secure-password",
    "password_confirmation": "secure-password"
}

// Response: 201
{
    "token": "1|abc123...",
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" }
}
```

#### Login

```json
// POST /api/auth/login
{
    "email": "john@example.com",
    "password": "secure-password"
}

// Response: 200
{
    "token": "1|abc123...",
    "user": { ... }
}
```

### Token Management

```
GET  /api/tokens       # List tokens
POST /api/tokens       # Create token
DELETE /api/tokens/{id} # Revoke token
```

### Profile

```
GET    /api/profile          # Get profile
PUT    /api/profile          # Update profile
PUT    /api/profile/password # Change password
```

## API Gateway Module

### API Key Authentication

Third-party services authenticate via API keys. Keys are managed through the Gateway module with:

- **IP whitelisting** — Restrict to specific IPs
- **Rate limit tiers** — Configurable request limits
- **Expiry** — Time-based key expiration
- **Metadata** — Custom metadata via JSON payload

#### Authentication Header

```
X-API-Key: your-api-key-here
X-API-Signature: hmac-sha256-signature  (for webhooks)
```

### Webhooks

```
POST /api/webhooks/{provider}
```

Webhook signature validation is handled by `ValidateWebhookSignature` middleware.

## Standardized Response Format

All API responses follow a consistent structure via the base `ApiController`:

```json
{
    "custom_code": 200,
    "status": "success",
    "message": "Operation completed",
    "body": { ... },
    "info": null
}
```

### Error Responses

```json
{
    "custom_code": 422,
    "status": "error",
    "message": "Validation failed",
    "body": {
        "email": ["The email field is required."]
    },
    "info": null
}
```

## Rate Limiting

- Sanctum routes: Configured via Laravel Fortify settings
- API Gateway routes: Per-key rate limit tiers
- Custom throttle middleware for additional route groups

## CORS

CORS is configured in `config/cors.php` to allow API and Sanctum origins.
