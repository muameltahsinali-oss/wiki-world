#!/usr/bin/env bash
set -euo pipefail

API_BASE="${API_BASE:-http://127.0.0.1:8000/api/v1}"
LOCALE="${LOCALE:-ar}"
REQUESTS="${REQUESTS:-200}"

echo "Benchmarking public GET ${API_BASE}/${LOCALE}/persons (${REQUESTS} requests)..."

total=0
for i in $(seq 1 "$REQUESTS"); do
  start=$(date +%s%3N)
  status=$(curl -s -o /dev/null -w "%{http_code}" "${API_BASE}/${LOCALE}/persons?per_page=10")
  end=$(date +%s%3N)
  elapsed=$((end - start))
  total=$((total + elapsed))
  if [[ "$status" != "200" ]]; then
    echo "Request $i failed with status $status" >&2
    exit 1
  fi
done

avg=$((total / REQUESTS))
echo "Average latency: ${avg}ms over ${REQUESTS} requests"
echo "Target: p95 public GET < 200ms (run with production-like stack for accurate p95)"
