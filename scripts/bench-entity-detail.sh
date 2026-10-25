#!/usr/bin/env bash
set -euo pipefail

API_BASE="${API_BASE:-http://127.0.0.1:8000/api/v1}"
LOCALE="${LOCALE:-ar}"

echo "Benchmarking entity detail reads..."

uuid=$(curl -s "${API_BASE}/${LOCALE}/persons?per_page=1" | python -c "import sys,json; d=json.load(sys.stdin); print(d['data'][0]['uuid'])" 2>/dev/null || true)

if [[ -z "${uuid:-}" ]]; then
  echo "No published person found; seed content first." >&2
  exit 1
fi

REQUESTS="${REQUESTS:-100}"
total=0
for i in $(seq 1 "$REQUESTS"); do
  start=$(date +%s%3N)
  status=$(curl -s -o /dev/null -w "%{http_code}" "${API_BASE}/${LOCALE}/persons/${uuid}")
  end=$(date +%s%3N)
  elapsed=$((end - start))
  total=$((total + elapsed))
  if [[ "$status" != "200" ]]; then
    echo "Request $i failed with status $status" >&2
    exit 1
  fi
done

avg=$((total / REQUESTS))
echo "Entity ${uuid} average latency: ${avg}ms over ${REQUESTS} requests"
