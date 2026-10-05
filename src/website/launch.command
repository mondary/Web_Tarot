#!/usr/bin/env bash
# launch V9 — launcher de dev (double-clic). Ne fait pas partie de l'app.
# détecte le dossier · vérifie index.php · port libre · ouvre le navigateur · stop propre
cd "$(dirname "$0")" || exit 1

command -v php >/dev/null 2>&1 || { printf 'PHP introuvable.\n'; read -n1 -r; exit 1; }
[ -f index.php ] || { printf 'index.php manquant dans %s\n' "$(pwd)"; read -n1 -r; exit 1; }

PORT=8772
while lsof -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; do PORT=$((PORT+1)); done

# -n -d auto_prepend_file= : évite l'interférence Herd · 0.0.0.0 : accessible du téléphone (même Wi-Fi)
php -n -d auto_prepend_file= -S 0.0.0.0:"$PORT" -t . index.php &
PID=$!
trap 'kill $PID 2>/dev/null' EXIT INT TERM

for _ in {1..20}; do
  curl -fsS "http://127.0.0.1:$PORT/" >/dev/null 2>&1 && break
  kill -0 "$PID" 2>/dev/null || { printf 'Le serveur PHP a quitté prématurément.\n'; read -n1 -r; exit 1; }
  sleep .1
done
curl -fsS "http://127.0.0.1:$PORT/" >/dev/null 2>&1 || { printf 'Le serveur ne répond pas sur le port %s.\n' "$PORT"; read -n1 -r; exit 1; }
case "$(uname)" in
  Darwin) open "http://127.0.0.1:$PORT/" ;;
  Linux)  xdg-open "http://127.0.0.1:$PORT/" >/dev/null 2>&1 ;;
esac

LAN_IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null)
printf '\n  ► Sur ce Mac : http://127.0.0.1:%s/\n' "$PORT"
[ -n "$LAN_IP" ] && printf '  ► Sur le téléphone (même Wi-Fi) : http://%s:%s/\n' "$LAN_IP" "$PORT"
printf '    (ferme cette fenêtre pour arrêter)\n\n'
wait $PID
