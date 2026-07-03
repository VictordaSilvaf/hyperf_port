#!/usr/bin/env bash
set -euo pipefail

from="${1:?Usage: validate-pr-commits.sh <from-sha> <to-sha>}"
to="${2:?Usage: validate-pr-commits.sh <from-sha> <to-sha>}"

while IFS= read -r subject; do
  if [[ -z "${subject}" ]]; then
    continue
  fi
  echo "${subject}" | php scripts/validate-commit-msg.php -
done < <(git log --format=%s "${from}..${to}")

echo "All commits follow Conventional Commits."
