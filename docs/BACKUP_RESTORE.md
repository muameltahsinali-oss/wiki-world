# Backup and Restore

## Automated backup

```bash
bash scripts/backup-database.sh
```

Environment variables:

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `BACKUP_DIR` (default: `./backups`)

Backups older than 14 days are pruned automatically.

## Manual restore

```bash
gunzip -c backups/wikiworld_YYYYMMDD_HHMMSS.sql.gz | \
  PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE"
```

## Pre-restore checklist

1. Stop application workers to prevent writes
2. Take a snapshot of current DB (safety)
3. Restore from backup
4. Run `php artisan migrate --force` if schema drifted
5. Clear caches: `php artisan cache:clear`, `php artisan config:clear`
6. Restart workers and verify health endpoint

## Media files

If using S3/MinIO, enable bucket versioning and lifecycle policies separately from database backups.
