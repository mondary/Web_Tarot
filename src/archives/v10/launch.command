#!/usr/bin/env bash
cd "$(dirname "$0")" || exit 1
PORT=8773
while lsof -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; do PORT=$((PORT+1)); done
php -n -d auto_prepend_file= -S 0.0.0.0:"$PORT" -t . index.php &
PID=$!
trap 'kill $PID 2>/dev/null' EXIT INT TERM
IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null)
printf '\n  ► V10 sur ce Mac : http://127.0.0.1:%s/\n' "$PORT"
[ -n "$IP" ] && printf '  ► V10 sur le réseau : http://%s:%s/\n' "$IP" "$PORT"
printf '    (ferme cette fenêtre pour arrêter)\n\n'
wait $PID
