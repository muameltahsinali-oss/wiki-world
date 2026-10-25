#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <backup.sql.gz>" >&2
  exit 1
fi

BACKUP_FILE="$1"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_DATABASE:-wikiworld}"
DB_USER="${DB_USERNAME:-wikiworld}"

echo "Restoring ${BACKUP_FILE} into ${DB_NAME}..."
gunzip -c "$BACKUP_FILE" | PGPASSWORD="${DB_PASSWORD:-wikiworld}" psql \
  -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME"

echo "Restore complete. Run: php artisan migrate --force && php artisan cache:clear"
