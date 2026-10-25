#!/usr/bin/env bash
set -euo pipefail

API_BASE="${API_BASE:-http://127.0.0.1:8000/api}"
ALERT_EMAIL="${ALERT_EMAIL:-}"

status=$(curl -s -o /dev/null -w "%{http_code}" "${API_BASE}/health/ready" || echo "000")

if [[ "$status" != "200" ]]; then
  echo "ALERT: health/ready returned ${status}" >&2
  if [[ -n "$ALERT_EMAIL" ]]; then
    echo "Wiki World health check failed with status ${status}" | mail -s "Wiki World alert" "$ALERT_EMAIL" || true
  fi
  exit 1
fi

echo "OK: health/ready ${status}"
