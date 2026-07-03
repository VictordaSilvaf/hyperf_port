#!/usr/bin/env bash
set -euo pipefail

branch="$(git symbolic-ref --quiet HEAD 2>/dev/null | sed 's|refs/heads/||' || true)"

if [[ -z "${branch}" ]]; then
  exit 0
fi

# Git Flow: main/master/develop + feature|bugfix|hotfix|release/<slug>
pattern='^(main|master|develop)$|^(feature|bugfix|hotfix|release)/[a-z0-9][a-z0-9._-]*$'

if [[ ! "${branch}" =~ ${pattern} ]]; then
  cat <<EOF
Branch name "${branch}" does not follow Git Flow.

Allowed:
  main | master | develop
  feature/<slug>   e.g. feature/auth-login
  bugfix/<slug>    e.g. bugfix/422-validation
  hotfix/<slug>    e.g. hotfix/token-expiry
  release/<version> e.g. release/1.2.0

See CONTRIBUTING.md for details.
EOF
  exit 1
fi
