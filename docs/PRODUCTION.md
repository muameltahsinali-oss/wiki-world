# Production Deployment

## Infrastructure

Use `docker-compose.prod.yml` overrides on top of the base stack.

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

## Required environment

| Variable | Description |
| -------- | ----------- |
| `APP_KEY` | Laravel application key |
| `DB_*` | PostgreSQL credentials |
| `REDIS_HOST` | Redis for cache/queues |
| `FRONTEND_URL` | Public frontend URL |
| `NEXT_PUBLIC_API_URL` | Public API base URL |
| `REVALIDATE_SECRET` | Shared ISR webhook secret |
| `SSL_CERT_PATH` | Path to TLS cert directory |

## SSL and HSTS

Production nginx config (`docker/nginx/production.conf`) enforces:

- HTTP → HTTPS redirect
- TLS 1.2+
- HSTS with `includeSubDomains; preload`
- Security headers (X-Content-Type-Options, X-Frame-Options, etc.)

## Workers

Production runs 2 queue workers and a scheduler container. Monitor queue depth and failed jobs.

## Backups

Daily cron:

```bash
0 2 * * * /path/to/wikiworld/scripts/backup-database.sh
```

See [BACKUP_RESTORE.md](./BACKUP_RESTORE.md).

## Monitoring

- Health: `GET /api/v1/health`
- Alert on: 5xx rate > 1%, p95 latency > 500ms, queue failures
- Log aggregation recommended (e.g. Loki, CloudWatch)
