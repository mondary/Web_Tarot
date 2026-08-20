#!/usr/bin/env python3
"""
build_vault.py — Construit tarot.sqlite (coffre fort)
Usage: python3 build_vault.py
Output: website/v4/tarot.sqlite
"""

import sqlite3, os, hashlib, re, json, glob
from pathlib import Path
from datetime import datetime

ROOT = Path(__file__).parent
OUT = ROOT / 'website' / 'v4' / 'tarot.sqlite'

# ── Helpers ──────────────────────────────────────────────

def sha256(data):
    return hashlib.sha256(data).hexdigest()

MIME_MAP = {
    '.md': 'text/markdown', '.html': 'text/html', '.js': 'application/javascript',
    '.json': 'application/json', '.css': 'text/css', '.php': 'application/x-httpd-php',
    '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png',
    '.webp': 'image/webp', '.svg': 'image/svg+xml', '.txt': 'text/plain',
    '.sqlite': 'application/x-sqlite3', '.py': 'text/x-python',
}

def mime_for(ext):
    return MIME_MAP.get(ext.lower(), 'application/octet-stream')

IMG_EXTS = {'.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp'}

def walk_dir(directory, base=''):
    results = []
    d = Path(directory)
    if not d.exists():
        return results
    for entry in sorted(d.iterdir()):
        if entry.name.startswith('.') and entry.name not in ('.gitignore', '.htaccess'):
            continue
        if entry.name in ('node_modules', '.git', '__pycache__'):
            continue
        rel = f"{base}/{entry.name}" if base else entry.name
        if entry.is_dir():
            results.extend(walk_dir(entry, rel))
        else:
            results.append((str(entry), rel))
    return results

def extract_card_of_day(md):
    m = re.search(r'## Carte du Jour\s*\n\s*([\s\S]*?)(?=## |$)', md)
    return m.group(1).strip() if m else None

def md_to_html(md):
    sections = []
    for m in re.finditer(r'^## (.+)$', md, re.MULTILINE):
        title = m.group(1)
        start = m.end()
        next_m = re.search(r'^## ', md[start:], re.MULTILINE)
        content = md[start:start + next_m.start()] if next_m else md[start:]
        content = content.strip()
        if title not in ('Carte du Jour', 'Conclusion'):
            sections.append(f'<h2>{title}</h2>\n{content}')
    return '\n\n'.join(sections) or f'<p>{md}</p>'

# ── Build ────────────────────────────────────────────────

print('🔨 Construction du coffre fort...\n')

if OUT.exists():
    OUT.unlink()
OUT.parent.mkdir(parents=True, exist_ok=True)

db = sqlite3.connect(str(OUT))

# ── 1. VAULT ─────────────────────────────────────────────

db.execute('''CREATE TABLE IF NOT EXISTS vault (
    path TEXT PRIMARY KEY, content BLOB, mime TEXT,
    size INTEGER, sha256 TEXT, modified TEXT)''')

vault_dirs = ['website', 'archives', 'benchmarks', 'scripts']
vault_count = 0
for vd in vault_dirs:
    files = walk_dir(ROOT / 'src' / vd, vd)
    stored = 0
    for full_path, rel_path in files:
        ext = Path(full_path).suffix.lower()
        if ext in IMG_EXTS:
            continue
        if ext in ('.sqlite', '.bak', '.pyc'):
            continue
        stat = os.stat(full_path)
        with open(full_path, 'rb') as f:
            content = f.read()
        db.execute('INSERT OR REPLACE INTO vault VALUES (?,?,?,?,?,?)',
                   (rel_path, content, mime_for(ext), len(content), sha256(content),
                    datetime.fromtimestamp(stat.st_mtime).isoformat()))
        vault_count += 1
        stored += 1
    print(f'  📁 {vd}: {stored} fichiers texte')

# Fichiers root
for rf in ['README.md', 'CHANGELOG.md', 'VERSION', 'LICENSE', '.gitignore',
           'build_vault.py', 'extract_vault.py']:
    fp = ROOT / rf
    if fp.exists():
        stat = fp.stat()
        content = fp.read_bytes()
        db.execute('INSERT OR REPLACE INTO vault VALUES (?,?,?,?,?,?)',
                   (rf, content, mime_for(fp.suffix), len(content), sha256(content),
                    datetime.fromtimestamp(stat.st_mtime).isoformat()))
        vault_count += 1

print(f'  📦 Vault: {vault_count} fichiers stockés\n')

# ── 2. APP TABLES ────────────────────────────────────────

db.executescript('''CREATE TABLE IF NOT EXISTS families (
    key TEXT PRIMARY KEY, name TEXT, title_full TEXT, short TEXT,
    element TEXT, element_sym TEXT, element_line TEXT, desc TEXT,
    accent TEXT, sort_order INTEGER);
CREATE TABLE IF NOT EXISTS cards (
    id TEXT PRIMARY KEY, family_key TEXT, name TEXT, num INTEGER,
    sort_global INTEGER, html TEXT, card_of_day TEXT, img BLOB);
CREATE TABLE IF NOT EXISTS card_es (
    card_id TEXT PRIMARY KEY, affirmation TEXT, reponse TEXT);
CREATE INDEX IF NOT EXISTS idx_cards_sort ON cards(sort_global);''')

# ── Families ─────────────────────────────────────────────

families = [
    ('majors', 'Arcanes Majeurs', 'Arcanes Majeurs', 'Majeurs', 'Éther', '✦︎',
     'les 22 arcanes du chemin initiatique',
     'Les 22 arcanes majeurs racontent les grandes étapes et leçons de la vie.', '#c9a227', 0),
    ('wands', 'Bâtons', 'Bâtons', 'Bâtons', 'Feu', '△',
     "l'action, la passion et la créativité",
     'Les bâtons représentent l\'action et la créativité.', '#e85d3a', 1),
    ('cups', 'Coupes', 'Coupes', 'Coupes', 'Eau', '▽',
     "les émotions, l'amour et l'intuition",
     'Les coupes représentent les émotions et les relations.', '#3a8de8', 2),
    ('pentacles', 'Deniers', 'Deniers', 'Deniers', 'Terre', '◯',
     'le monde matériel et la finance',
     'Les deniers représentent le monde matériel.', '#c9a227', 3),
    ('swords', 'Épées', 'Épées', 'Épées', 'Air', '△',
     "l'intellect et la communication",
     'Les épées représentent l\'intellect.', '#7eb8d4', 4),
]
db.executemany('INSERT OR REPLACE INTO families VALUES (?,?,?,?,?,?,?,?,?,?)', families)
print(f'  Familles: {len(families)}')

# ── Cards ────────────────────────────────────────────────

FAMILY_MAP = {'a': 'majors', 'b': 'wands', 'c': 'cups', 'd': 'pentacles', 'e': 'swords'}
RANK_NAMES = {1: 'As', 11: 'Valet', 12: 'Cavalier', 13: 'Reine', 14: 'Roi'}
NUM_NAMES = {0: '', 1: 'As', 2: 'Deux', 3: 'Trois', 4: 'Quatre', 5: 'Cinq',
             6: 'Six', 7: 'Sept', 8: 'Huit', 9: 'Neuf', 10: 'Dix'}
SUIT_NAMES = {'b': 'de Bâton', 'c': 'de Coupe', 'd': 'de Denier', 'e': "d'Épée"}

fr_files = sorted(glob.glob(str(ROOT / 'src' / 'benchmarks' / 'cards_alt' / '*_unlimitedFR.md')))
fr_files = [f for f in fr_files if not Path(f).name.startswith('t_')]

cards_dir = ROOT / 'src' / 'website' / 'cards'
sort_global = 0
card_count = 0
img_count = 0

for fr_path in fr_files:
    basename = Path(fr_path).stem.replace('_unlimitedFR', '')
    parts = basename.split('_')
    letter = parts[0]
    num = int(parts[1])
    family_key = FAMILY_MAP.get(letter, 'majors')
    prefix = f'{letter}_{parts[1]}'

    # Nom affichable
    if letter == 'a':
        display_name = ' '.join(parts[2:]).capitalize()
    else:
        rank = RANK_NAMES.get(num, NUM_NAMES.get(num, str(num)))
        display_name = f'{rank} {SUIT_NAMES.get(letter, "")}'

    md_content = Path(fr_path).read_text(encoding='utf-8')
    html = md_to_html(md_content)
    card_of_day = extract_card_of_day(md_content)

    # Image: matcher par prefix
    img_data = None
    for f in os.listdir(str(cards_dir)):
        if f.startswith(prefix) and f.endswith('_fr.jpg'):
            fp = cards_dir / f
            if fp.stat().st_size > 100:
                img_data = fp.read_bytes()
                break
    if not img_data:
        for f in os.listdir(str(cards_dir)):
            if f.startswith(prefix) and f.endswith('.jpg') and 'marseille' not in f and 'wiki' not in f:
                fp = cards_dir / f
                if fp.stat().st_size > 100:
                    img_data = fp.read_bytes()
                    break

    if img_data:
        img_count += 1

    db.execute('INSERT OR REPLACE INTO cards VALUES (?,?,?,?,?,?,?,?)',
               (basename, family_key, display_name, num, sort_global, html, card_of_day, img_data))
    sort_global += 1
    card_count += 1

print(f'  Cartes: {card_count} (images: {img_count}/{card_count})')

# ── ES Data ──────────────────────────────────────────────

es_files = sorted(glob.glob(str(ROOT / 'website' / 'cards' / '*_ES.md')))
es_count = 0
for es_path in es_files:
    card_id = Path(es_path).stem.replace('_ES', '')
    content = Path(es_path).read_text(encoding='utf-8')
    aff_m = re.search(r'\*\*Affirmation\s*:?\*\*\s*\n*\s*(.+)', content)
    rep_m = re.search(r'\*\*R[ÉE]PONSE\s*:?\*\*\s*(.+)', content)
    aff = aff_m.group(1).strip().replace('> ', '') if aff_m else ''
    rep = rep_m.group(1).strip() if rep_m else ''
    if aff or rep:
        db.execute('INSERT OR REPLACE INTO card_es VALUES (?,?,?)', (card_id, aff, rep))
        es_count += 1
print(f'  ES data: {es_count}')

# ── Meta ─────────────────────────────────────────────────

db.execute('CREATE TABLE IF NOT EXISTS meta (key TEXT PRIMARY KEY, value TEXT)')
db.execute('INSERT OR REPLACE INTO meta VALUES (?,?)', ('version', '4.0.0'))
db.execute('INSERT OR REPLACE INTO meta VALUES (?,?)', ('built', datetime.now().isoformat()))
db.execute('INSERT OR REPLACE INTO meta VALUES (?,?)', ('vault_files', str(vault_count)))
db.execute('INSERT OR REPLACE INTO meta VALUES (?,?)', ('cards', str(card_count)))

db.commit()
db.close()

size_mb = OUT.stat().st_size / 1024 / 1024
print(f'\n✅ Coffre fort: {OUT}')
print(f'   Taille: {size_mb:.1f} MB | Vault: {vault_count} | Cartes: {card_count} | Images: {img_count}/78')
