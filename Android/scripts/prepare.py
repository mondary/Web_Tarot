"""Export the existing Tarot vault into the Android offline bundle."""
from pathlib import Path
import sqlite3, shutil, json
android = Path(__file__).resolve().parents[1]
web = android / 'www'
web.mkdir(exist_ok=True)
for source in (android / 'web').iterdir():
    if source.is_file(): shutil.copy2(source, web / source.name)
with sqlite3.connect(android.parent / 'src/website/v9/vault.sqlite') as db:
    for name, data in db.execute("SELECT path,CAST(data AS BLOB) FROM vault WHERE path='/app-data.json' OR path LIKE '/img/%' OR path LIKE '/fonts/%'"):
        target = web / name.lstrip('/')
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_bytes(data if isinstance(data, bytes) else data.encode())
cards = json.loads((web / 'app-data.json').read_text())['cards']
assert len(cards) == 78
assert all((web / 'img' / (c['id'] + '.jpg')).is_file() for c in cards)
print('Android: 78 cartes et ressources hors ligne vérifiées.')
