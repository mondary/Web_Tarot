#!/usr/bin/env bash
set -e

cd "$(dirname "$0")"
command -v php >/dev/null || { printf 'PHP est requis.\n' >&2; exit 1; }

# Routeur V3/V5 généré dans /tmp (versions PHP à URL propres → index.php)
TMPDIR_WT=$(mktemp -d /tmp/web-tarot.XXXXXX)
ROUTER="$TMPDIR_WT/router.php"
trap 'rm -rf "$TMPDIR_WT"' EXIT
cat > "$ROUTER" <<'PHP'
<?php
$root = $_SERVER['DOCUMENT_ROOT'];
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = $root . $path;
// Route toute version PHP (website/vN/) à travers son index.php
if (preg_match('#^/(website/[^/]+)/#', $path, $m)) {
    if (is_file($file)) return false;
    $idx = $root . '/' . $m[1] . '/index.php';
    if (is_file($idx)) {
        $_SERVER['TAROT_LOCAL_BASE_PATH'] = '/' . $m[1];
        require $idx;
        return true;
    }
}
if (is_file($file) || is_dir($file)) return false;
http_response_code(404);
echo '404 Not Found';
PHP

# Port libre à partir de 8765
PORT=8765
while lsof -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; do
  PORT=$((PORT+1))
done

printf '\nServeur Web_Tarot sur le port %s :\n\n' "$PORT"
found=0
while IFS= read -r idx; do
  found=1
  rel=$(dirname "$idx"); rel=${rel#./}
  printf '  %s : http://localhost:%s/%s/\n' "$rel" "$PORT" "$rel"
done < <(find . -type d \( -name node_modules -o -name vendor -o -name dist -o -name archives -o -name store -o -name .git -o -name .agents \) -prune -o \( -name index.html -o -name index.php \) -print | sort)
[ "$found" = 1 ] || printf '  (aucun index.html/php trouvé)\n'
printf '\nCtrl+C pour arrêter.\n\n'

exec php -S 127.0.0.1:"$PORT" -t . "$ROUTER"
