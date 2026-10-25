#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-./backups}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
FILE="${BACKUP_DIR}/wikiworld_${TIMESTAMP}.sql.gz"

mkdir -p "$BACKUP_DIR"

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_DATABASE:-wikiworld}"
DB_USER="${DB_USERNAME:-wikiworld}"

echo "Creating backup: $FILE"
PGPASSWORD="${DB_PASSWORD:-wikiworld}" pg_dump \
  -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DB_NAME" | gzip > "$FILE"

echo "Backup complete: $FILE"
find "$BACKUP_DIR" -name 'wikiworld_*.sql.gz' -mtime +14 -delete 2>/dev/null || true
