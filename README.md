# Wiki World | ويكي العالمية

Entity-first Islamic knowledge platform — **MVP complete (295/295 tasks)**.

## Stack

| Layer | Technology |
|-------|------------|
| Frontend | Next.js 16, React 19, TypeScript, Tailwind v4, next-intl |
| Backend | Laravel 13 (PHP 8.4+), Sanctum, modular monolith |
| Data | PostgreSQL 16, Redis 7 |
| Search | Database driver (dev) / Meilisearch (prod) |
| Storage | S3-compatible (MinIO in dev) |
| Infra | Docker Compose, GitHub Actions |

## Quick Start

### With Docker (recommended)

```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env.local
docker compose up -d
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
bash scripts/seed-launch-content.sh
```

- API: http://localhost:8080/api/health  
- Frontend: http://localhost:3000/ar  
- Mailpit: http://localhost:8025  

### Local (without Docker)

**Backend**

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DevUserRolesSeeder --force
bash ../scripts/seed-launch-content.sh
php artisan serve
```

**Frontend**

```bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

Set `SEARCH_ENABLED=true` and `AI_ENABLED=true` in `backend/.env` to enable search and AI chat.

## Commands

```bash
npm run test:all          # backend tests + frontend unit tests + build
npm run test:backend      # PHPUnit
npm run test:frontend     # Vitest
npm run test:e2e          # Playwright
npm run build:frontend    # Production build
```

## Deployment

| Environment | Command |
|-------------|---------|
| Staging | `docker compose -f docker-compose.yml -f docker-compose.staging.yml up -d` |
| Production | `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d` |

See [docs/STAGING.md](docs/STAGING.md), [docs/PRODUCTION.md](docs/PRODUCTION.md), [docs/LAUNCH.md](docs/LAUNCH.md).

## Documentation

| Document | Purpose |
|----------|---------|
| [docs/PROJECT_TASKS.md](docs/PROJECT_TASKS.md) | Task tracker (295/295 Done) |
| [docs/RELEASE_CHECKLIST.md](docs/RELEASE_CHECKLIST.md) | Pre-launch checklist |
| [docs/EXECUTION_ROADMAP.md](docs/EXECUTION_ROADMAP.md) | Full execution plan |
| [docs/API_SPECIFICATION.md](docs/API_SPECIFICATION.md) | API contract |

## Health Checks

- `GET /api/health` — liveness  
- `GET /api/health/ready` — database, redis, queue, storage, search, AI  
- `bash scripts/monitor-health.sh` — cron-friendly monitor script

## License

Proprietary — Wiki World / ويكي العالمية
