#!/usr/bin/env bash
# Launcher V3 local. Ne fait pas partie du deploy web.
cd "$(dirname "$0")" || exit 1

command -v php >/dev/null 2>&1 || { printf 'PHP introuvable.\n'; read -n1 -r; exit 1; }
[ -f index.php ] || { printf 'index.php manquant dans %s\n' "$(pwd)"; read -n1 -r; exit 1; }
[ -f tarot.sqlite ] || { printf 'tarot.sqlite manquant.\n'; read -n1 -r; exit 1; }

PORT=8765
while lsof -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; do PORT=$((PORT + 1)); done

php -S 127.0.0.1:"$PORT" -t . index.php &
PID=$!
trap 'kill "$PID" 2>/dev/null' EXIT INT TERM

sleep 1
case "$(uname)" in
  Darwin) open "http://127.0.0.1:$PORT/" ;;
  Linux)  xdg-open "http://127.0.0.1:$PORT/" >/dev/null 2>&1 ;;
esac

printf '\n  V3 : http://127.0.0.1:%s/  (ferme cette fenêtre pour arrêter)\n\n' "$PORT"
wait "$PID"
