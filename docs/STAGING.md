# Staging Deployment

## Prerequisites

- Docker and Docker Compose
- TLS certificates in `docker/nginx/certs/` (`fullchain.pem`, `privkey.pem`)
- Environment variables: `REVALIDATE_SECRET`, `FRONTEND_URL`, `NEXT_PUBLIC_API_URL`

## Deploy

```bash
cp .env.example .env
# Edit secrets and URLs

docker compose -f docker-compose.yml -f docker-compose.staging.yml up -d --build
```

## Post-deploy

```bash
docker compose exec backend php artisan migrate --force
bash scripts/seed-launch-content.sh
```

## Verify

- `https://staging.wikiworld.example/ar` — homepage
- `https://staging.wikiworld.example/api/health` — API health
- Security headers: `curl -I https://staging.wikiworld.example/ar`

## ISR revalidation

Set matching `REVALIDATE_SECRET` in backend and frontend `.env`. On publish, Laravel calls `POST /api/revalidate?secret=...`.
