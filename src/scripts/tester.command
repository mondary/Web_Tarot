#!/usr/bin/env bash
# Tester de versions — double-cliquez pour ouvrir la liste des versions.
cd "$(dirname "$0")" || exit 1

for c in git php python3; do
  command -v "$c" >/dev/null 2>&1 || { printf '%s introuvable.\n' "$c"; read -n1 -r; exit 1; }
done

if ! git branch --list archive/legacy >/dev/null; then
  printf 'Récupération de archive/legacy…\n'
  git fetch origin archive/legacy:archive/legacy || { printf 'archive/legacy introuvable.\n'; read -n1 -r; exit 1; }
fi

exec python3 tester-server.py
