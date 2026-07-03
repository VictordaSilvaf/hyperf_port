#!/usr/bin/env bash
set -euo pipefail

export LC_ALL=C.UTF-8
export LANG=C.UTF-8

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${root}"

if [[ ! -f vendor/bin/php-cs-fixer ]]; then
  echo "Run hyper composer install (or ./hyper bootstrap) before pushing."
  exit 1
fi

echo "→ PHP-CS-Fixer (dry-run)..."
vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.php --quiet

echo "→ PHPStan..."
vendor/bin/phpstan analyse --memory-limit=300M -c phpstan.neon.dist

if php -r 'exit(class_exists("DOMDocument") ? 0 : 1);'; then
  echo "→ Pest..."
  ./vendor/bin/pest --no-coverage
else
  echo "→ Pest skipped (install php-xml for DOMDocument — ex.: sudo apt install php8.4-xml)"
  echo "  Run full suite in Docker: hyper test"
fi

echo "Quality checks passed."
