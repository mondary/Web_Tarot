#!/usr/bin/env python3
"""
extract_vault.py — Extrait tous les fichiers depuis tarot.sqlite
Usage: 
  python3 extract_vault.py              → tout dans extracted/
  python3 extract_vault.py --filter .md → seulement les .md
  python3 extract_vault.py --path website/cards/a_00_Fou.md
"""

import sqlite3, os, hashlib, sys
from pathlib import Path

DB_PATH = Path(__file__).parent / 'website' / 'v4' / 'tarot.sqlite'
OUT = Path(__file__).parent / 'extracted'

db = sqlite3.connect(str(DB_PATH))

# Parse args
args = sys.argv[1:]
single_path = None
filter_ext = None
i = 0
while i < len(args):
    if args[i] == '--path' and i + 1 < len(args):
        single_path = args[i + 1]
    elif args[i] == '--filter' and i + 1 < len(args):
        filter_ext = args[i + 1]
    i += 1

if single_path:
    row = db.execute('SELECT * FROM vault WHERE path = ?', (single_path,)).fetchone()
    if not row:
        print(f'Non trouvé: {single_path}')
        sys.exit(1)
    out_path = OUT / row[0]
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_bytes(row[1])
    h = hashlib.sha256(row[1]).hexdigest()
    print(f"{'✅' if h == row[4] else '❌'} {row[0]} ({row[3]} bytes)")
else:
    if filter_ext:
        rows = db.execute('SELECT * FROM vault WHERE path LIKE ?', (f'%{filter_ext}%',)).fetchall()
    else:
        rows = db.execute('SELECT * FROM vault ORDER BY path').fetchall()

    print(f'📦 Extraction de {len(rows)} fichiers...\n')

    extracted = verified = failed = 0
    for row in rows:
        out_path = OUT / row[0]
        out_path.parent.mkdir(parents=True, exist_ok=True)
        out_path.write_bytes(row[1])
        h = hashlib.sha256(row[1]).hexdigest()
        if h == row[4]:
            verified += 1
        else:
            failed += 1
            print(f'❌ CHECKSUM: {row[0]}')
        extracted += 1

    # Card images
    cards = db.execute('SELECT id, img FROM cards WHERE img IS NOT NULL AND length(img) > 100').fetchall()
    for card_id, img in cards:
        out_path = OUT / 'website' / 'cards' / f'{card_id}_fr.jpg'
        out_path.parent.mkdir(parents=True, exist_ok=True)
        out_path.write_bytes(img)
        extracted += 1

    print(f'\n✅ {extracted} fichiers extraits dans extracted/')
    print(f'   Vérifiés: {verified}/{len(rows)}')
    if failed:
        print(f'   ❌ Échecs: {failed}')

db.close()
