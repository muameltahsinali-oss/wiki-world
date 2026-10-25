# Monitoring

## Health endpoints

| Endpoint | Purpose |
|----------|---------|
| `GET /api/health` | Liveness |
| `GET /api/health/ready` | DB, Redis, queue, storage, search, AI |

## Cron monitor

```bash
# Every 5 minutes
*/5 * * * * API_BASE=https://api.wikiworld.example/api bash /path/to/wikiworld/scripts/monitor-health.sh
```

Optional alert email:

```bash
ALERT_EMAIL=ops@example.com bash scripts/monitor-health.sh
```

## Recommended alerts

- `/api/health/ready` non-200 for 3 consecutive checks
- Queue worker container down
- Disk usage on Postgres volume > 80%
- 5xx rate > 1% (via reverse proxy logs)

## Logs

- Laravel: `backend/storage/logs/laravel.log`
- Nginx access/error logs in edge container
- Frontend: container stdout
