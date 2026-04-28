# podcast-site/api

Laravel API for podcast articles and anonymous board.

## Stack

- Laravel 12 (PHP)
- MySQL 8
- Docker Compose

## Setup

### Local with Docker

```bash
cd /Users/jumpeihirosawa/development/podcast-site/api
docker compose up -d --build
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan db:seed
```

### Local without Docker

Use SQLite instead of the Docker-only MySQL host `db`.

```bash
cd /Users/jumpeihirosawa/development/podcast-site/api
cp .env.example .env
```

Update `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/Users/jumpeihirosawa/development/podcast-site/api/database/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Then run:

```bash
php artisan migrate
php artisan serve
```

API base URL:

- `http://localhost:8000`

## Environment

Main values in `.env`:

- `DB_CONNECTION=mysql`
- `DB_HOST=db`
- `DB_PORT=3306`
- `DB_DATABASE=podcast`
- `DB_USERNAME=user`
- `DB_PASSWORD=password`
- `CORS_ALLOWED_ORIGINS=http://localhost:3000`
- `ADMIN_API_TOKEN=local-dev-token`

## Render Deploy

This repo includes a Render Blueprint at [render.yaml](./render.yaml) and a startup script at [scripts/render-start.sh](./scripts/render-start.sh).

Recommended first public URL:

- `https://nichijobase.onrender.com`

### Render steps

1. Push this repo to GitHub.
2. In Render, create a new Blueprint or Web Service from the repo.
3. Keep the service name as `nichijobase`.
4. Set these required environment variables in Render:
   - `APP_KEY`
   - `APP_URL`
   - `ADMIN_API_TOKEN`
   - `CORS_ALLOWED_ORIGINS`
5. Deploy. The startup script creates the SQLite file on the attached persistent disk and runs `php artisan migrate --force`.

### Suggested production values

```env
APP_NAME=日常BASE
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=/var/data/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Optional custom domain

After the first deploy works, add a custom domain such as:

- `nichijobase.jp`
- `nichijobase.com`
- `nichijo-base.com`

## API Summary

### Public APIs

- `GET /api/articles`
- `GET /api/articles/{slug}`
- `GET /api/articles/{slug}/thread`
- `GET /api/threads`
- `GET /api/threads/{thread}`
- `POST /api/threads` (anonymous)
- `POST /api/threads/{thread}/posts` (anonymous)

### Admin-only APIs (`X-Admin-Token` required)

- `POST /api/articles`
- `PUT /api/articles/{slug}`
- `DELETE /api/articles/{slug}`
- `DELETE /api/threads/{thread}`
- `DELETE /api/threads/{thread}/posts/{post}`

Header example:

```http
X-Admin-Token: local-dev-token
```

## Quick Examples

Create article:

```bash
curl -X POST http://localhost:8000/api/articles \
  -H "Content-Type: application/json" \
  -H "X-Admin-Token: local-dev-token" \
  -d '{"title":"New Article","slug":"new-article","body":"text","published_at":"2026-04-16 12:00:00","is_public":true}'
```

Delete board thread:

```bash
curl -X DELETE http://localhost:8000/api/threads/1 \
  -H "X-Admin-Token: local-dev-token"
```

Run tests:

```bash
docker compose exec -T app php artisan test
```
