#!/usr/bin/env bash
# Smoke test: API pronta para o frontend (CORS, settings, project media URLs, contact).
# Uso:
#   API_BASE=https://api.victorsf.com \
#   ADMIN_EMAIL=admin@example.com \
#   ADMIN_PASSWORD='secret' \
#   ./scripts/smoke-frontend-api.sh
#
# Opcionais: ORIGIN (default https://victorsf.com), SKIP_UPLOAD=1
set -euo pipefail

API_BASE="${API_BASE:-http://127.0.0.1:9501}"
API_BASE="${API_BASE%/}"
ADMIN_EMAIL="${ADMIN_EMAIL:?Set ADMIN_EMAIL}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:?Set ADMIN_PASSWORD}"
ORIGIN="${ORIGIN:-https://victorsf.com}"
SKIP_UPLOAD="${SKIP_UPLOAD:-0}"

json_get() {
  local json="$1" key="$2"
  php -r '
    $j = json_decode(stream_get_contents(STDIN), true);
    $path = explode(".", $argv[1]);
    $cur = $j;
    foreach ($path as $p) {
      if (!is_array($cur) || !array_key_exists($p, $cur)) { fwrite(STDERR, "missing key {$argv[1]}\n"); exit(1); }
      $cur = $cur[$p];
    }
    if (is_array($cur) || is_object($cur)) { echo json_encode($cur); }
    else { echo $cur; }
  ' "$key" <<<"$json"
}

assert_contains() {
  local haystack="$1" needle="$2" label="$3"
  if [[ "$haystack" != *"$needle"* ]]; then
    echo "FAIL: $label — expected to contain: $needle" >&2
    echo "Got: $haystack" >&2
    exit 1
  fi
  echo "OK: $label"
}

echo "==> 1. GET /health/live"
LIVE=$(curl -sS -f "${API_BASE}/api/v1/health/live")
assert_contains "$LIVE" '"status"' 'health/live returns status'

echo "==> 2. POST /auth/login"
LOGIN=$(curl -sS -f -X POST "${API_BASE}/api/v1/auth/login" \
  -H 'Content-Type: application/json' \
  -d "{\"email\":\"${ADMIN_EMAIL}\",\"password\":\"${ADMIN_PASSWORD}\"}")
TOKEN=$(json_get "$LOGIN" access_token)
[[ -n "$TOKEN" ]] || { echo "FAIL: no access_token" >&2; exit 1; }
echo "OK: login"

AUTH=(-H "Authorization: Bearer ${TOKEN}" -H 'Accept: application/json')

echo "==> 3. PUT /admin/site/settings (mínimo SEO + contact + social)"
curl -sS -f -X PUT "${API_BASE}/api/v1/admin/site/settings" \
  "${AUTH[@]}" \
  -H 'Content-Type: application/json' \
  -d '{
    "seo": {"site_name": "Victor SF", "default_meta_description": "Portfolio"},
    "contact": {"email": "hello@victorsf.com", "notification_email": "'"${ADMIN_EMAIL}"'"},
    "social": {"github": "https://github.com/", "linkedin": "https://linkedin.com/"}
  }' >/dev/null
echo "OK: site settings updated"

echo "==> 4. GET /site/settings"
SETTINGS=$(curl -sS -f "${API_BASE}/api/v1/site/settings")
assert_contains "$SETTINGS" '"contact"' 'public site settings has contact'

SLUG="smoke-$(date +%s)"
PROJECT_ID=""

if [[ "$SKIP_UPLOAD" != "1" ]]; then
  echo "==> 5. Upload + create project + publish"
  TMPIMG=$(mktemp /tmp/smoke-XXXXXX.png)
  # Minimal 1x1 PNG
  printf '\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB`\x82' >"$TMPIMG"

  UPLOAD=$(curl -sS -f -X POST "${API_BASE}/api/v1/admin/uploads" \
    "${AUTH[@]}" \
    -F "file=@${TMPIMG};type=image/png")
  PATH_VALUE=$(json_get "$UPLOAD" path)
  rm -f "$TMPIMG"
  echo "OK: upload path=${PATH_VALUE}"

  CREATED=$(curl -sS -f -X POST "${API_BASE}/api/v1/admin/projects" \
    "${AUTH[@]}" \
    -H 'Content-Type: application/json' \
    -d "{
      \"title\": \"Smoke ${SLUG}\",
      \"slug\": \"${SLUG}\",
      \"description\": \"Smoke test project\",
      \"content\": \"# Smoke\",
      \"thumbnail\": \"${PATH_VALUE}\",
      \"cover\": \"${PATH_VALUE}\",
      \"featured\": false
    }")
  PROJECT_ID=$(json_get "$CREATED" data.id)
  echo "OK: project created id=${PROJECT_ID}"

  curl -sS -f -X PATCH "${API_BASE}/api/v1/admin/projects/${PROJECT_ID}/publish" \
    "${AUTH[@]}" >/dev/null
  echo "OK: project published"
else
  echo "==> 5. SKIP_UPLOAD=1 — skip upload/create/publish"
fi

echo "==> 6. GET /projects and optional detail"
PROJECTS=$(curl -sS -f "${API_BASE}/api/v1/projects?per_page=5")
assert_contains "$PROJECTS" '"data"' 'projects list has data'

if [[ -n "$PROJECT_ID" ]]; then
  DETAIL=$(curl -sS -f "${API_BASE}/api/v1/projects/${SLUG}")
  assert_contains "$DETAIL" '"thumbnail"' 'project detail has thumbnail'
  if [[ "$DETAIL" == *'"thumbnail_url"* ]]; then
    echo "OK: thumbnail_url present (null or absolute)"
  else
    echo "WARN: thumbnail_url missing from detail — check ProjectPresenter deploy" >&2
  fi
fi

echo "==> 7. POST /contact"
CONTACT=$(curl -sS -f -X POST "${API_BASE}/api/v1/contact" \
  -H 'Content-Type: application/json' \
  -H "Origin: ${ORIGIN}" \
  -d '{
    "name": "Smoke Tester",
    "email": "smoke@example.com",
    "message": "Smoke test contact message body."
  }')
assert_contains "$CONTACT" '"message"' 'contact accepted'

echo "==> 8. OPTIONS preflight CORS"
HEADERS=$(curl -sS -D - -o /dev/null -X OPTIONS "${API_BASE}/api/v1/contact" \
  -H "Origin: ${ORIGIN}" \
  -H 'Access-Control-Request-Method: POST' \
  -H 'Access-Control-Request-Headers: Content-Type')
assert_contains "$HEADERS" 'Access-Control-Allow-Origin' 'CORS Allow-Origin header'
assert_contains "$HEADERS" "${ORIGIN}" 'CORS origin matches'
assert_contains "$HEADERS" '204' 'OPTIONS returns 204'

echo
echo "All smoke checks passed against ${API_BASE}"
