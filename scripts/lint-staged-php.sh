#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${root}"

if [[ ! -f vendor/bin/php-cs-fixer ]]; then
  echo "Run composer install before committing."
  exit 1
fi

mapfile -t files < <(git diff --cached --name-only --diff-filter=ACM | grep -E '\.php$' || true)

if [[ ${#files[@]} -eq 0 ]]; then
  exit 0
fi

vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --using-cache=no -- "${files[@]}"
git add -- "${files[@]}"
