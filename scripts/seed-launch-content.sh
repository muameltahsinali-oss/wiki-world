#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT/backend"

echo "==> Seeding relationship types"
php artisan db:seed --class=RelationshipTypeSeeder --force

echo "==> Seeding Ahlulbayt launch content"
php artisan db:seed --class=AhlulbaytLaunchSeeder --force

echo "==> Seeding Quran corpus (if available)"
php artisan db:seed --class=QuranCorpusSeeder --force 2>/dev/null || echo "QuranCorpusSeeder skipped"

echo "==> Launch content seed complete"
