# nichijobase API

Laravel API and admin pages for 日常BASE.

Frontend repository:

- <https://github.com/turnmeon0119/nichijobase-front>

## Stack

- PHP 8.x
- Laravel 12
- MySQL 8
- Docker Compose
- Cloudinary for image uploads

## What This API Provides

- Articles API and admin management
- News API and admin management
- Anonymous board threads and replies
- Article comments and reactions
- Board reactions and reports
- Ogiri prompts and answers
- Image upload support through Cloudinary

## Local Setup

Use Docker for the local API and MySQL.

```bash
cd /Users/jumpeihirosawa/development/podcast-site/api
cp .env.example .env
docker compose up -d --build
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate --seed
```

API URL:

- <http://localhost:8000>

Health check:

```bash
curl http://localhost:8000/api/test
```

Expected response:

```json
{"message":"OK"}
```

## Local Admin

Admin login page:

- <http://localhost:8000/admin/articles/login>

Default local token in `.env.example`:

```env
ADMIN_API_TOKEN=local-dev-token
```

Do not use the local token in production.

## Environment Variables

Create `.env` from `.env.example`.

```bash
cp .env.example .env
```

Important values:

```env
APP_KEY=
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=podcast
DB_USERNAME=user
DB_PASSWORD=password
CORS_ALLOWED_ORIGINS=http://localhost:3000
ADMIN_API_TOKEN=local-dev-token
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
```

Cloudinary values are required only when testing image uploads.

## Frontend Connection

The local frontend should point to this API.

Frontend `.env.local` example:

```env
API_BASE_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

CORS must allow the frontend URL:

```env
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

## API Summary

Public APIs:

```txt
GET  /api/test
GET  /api/articles
GET  /api/articles/{slug}
GET  /api/articles/{slug}/comments
POST /api/articles/{slug}/comments
POST /api/articles/{slug}/reactions
GET  /api/news
GET  /api/news/{slug}
GET  /api/threads
GET  /api/threads/{thread}
POST /api/threads
POST /api/threads/{thread}/posts
POST /api/threads/{thread}/report
POST /api/threads/{thread}/reactions
POST /api/threads/{thread}/posts/{post}/report
POST /api/threads/{thread}/posts/{post}/reactions
GET  /api/ogiri/prompts
GET  /api/ogiri/prompts/{prompt}
POST /api/ogiri/prompts/{prompt}/answers
POST /api/ogiri/prompts/{prompt}/answers/{answer}/reactions
```

Admin API requires `X-Admin-Token`:

```txt
POST   /api/articles
PUT    /api/articles/{slug}
DELETE /api/articles/{slug}
DELETE /api/articles/id/{article}
POST   /api/ogiri/prompts
DELETE /api/threads/{thread}
DELETE /api/threads/{thread}/posts/{post}
PATCH  /api/threads/{thread}/hide
PATCH  /api/threads/{thread}/unhide
PATCH  /api/threads/{thread}/posts/{post}/hide
PATCH  /api/threads/{thread}/posts/{post}/unhide
```

Header example:

```http
X-Admin-Token: local-dev-token
```

## Admin Pages

```txt
/admin/articles/login
/admin
/admin/articles
/admin/articles/create
/admin/articles/trash
/admin/news
/admin/news/create
/admin/ogiri
/admin/ogiri/create
/admin/board
/admin/timeline
```

## Tests

```bash
docker compose exec -T app php artisan test
```

## Production Services

Current production structure:

- Frontend: Vercel
- API: Render
- Database: SQLite on Render persistent disk (`/var/data/database.sqlite`)
- Images: Cloudinary

```txt
User
  ↓
Vercel (Next.js frontend)
  ↓ API requests
Render (Laravel API)
  ├─ SQLite on persistent disk
  └─ Cloudinary images
```

The current Render configuration uses Docker and starts Laravel through
`scripts/render-start.sh`. It creates the SQLite database file on the mounted
Render disk, runs migrations, and starts the Laravel server.

Railway MySQL is not used by the latest Render configuration. If traffic,
concurrent writes, or data volume grows, consider moving the production database
to a managed PostgreSQL or MySQL service.

Production environment variables are managed in each service dashboard. Do not commit production secrets.

## Deployment Notes

When API code changes:

1. Commit and push this repository.
2. Render deploys from the connected GitHub repository.
3. Check logs in Render.
4. Confirm health check: `/api/test`.

When database schema changes:

- Render startup runs migrations if configured.
- Check migration logs after deploy.
- Be careful with destructive schema changes on production data.

## Collaboration Rules

- Work on a feature branch instead of pushing directly to `main`.
- Do not commit `.env` or any secret values.
- Open a Pull Request before merging important changes.
- Run tests before asking for review.
- Keep frontend and API changes in separate commits when possible.

Suggested branch names:

```txt
feature/news-admin
feature/board-ui
feature/article-comments
fix/image-upload
```
