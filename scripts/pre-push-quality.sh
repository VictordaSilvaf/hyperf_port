#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${root}"

if [[ ! -f vendor/bin/php-cs-fixer ]]; then
  echo "Run composer install before pushing."
  exit 1
fi

echo "→ PHP-CS-Fixer (dry-run)..."
composer lint

echo "→ PHPStan..."
composer analyse

echo "→ Pest..."
./vendor/bin/pest --no-coverage

echo "Quality checks passed."
