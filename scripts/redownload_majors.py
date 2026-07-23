#!/usr/bin/env python3
"""Re-télécharge PROPREMENT les 22 arcanes majeurs Marseille depuis Wikimedia.
Par groupes de 4 avec pauses pour éviter le rate limit 429."""
import os, json, time, subprocess, urllib.request, urllib.error, sys

CARDS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'website', 'cards')

MAJORS = [
    ('a_00_Fou',            'TT Tarot.png'),
    ('a_01_Bateleur',       'T1 Tarot.png'),
    ('a_02_Papesse',        'T2 Tarot.png'),
    ('a_03_Impératrice',    'T3 Tarot.png'),
    ('a_04_Emperor',        'T4 Tarot.png'),
    ('a_05_Pape',           'T5 Tarot.png'),
    ('a_06_Amoureux',       'T6 Tarot.png'),
    ('a_07_Chariot',        'T7 Tarot.png'),
    ('a_08_Force',          'T11 Tarot.png'),   # Force = T11 en Marseille
    ('a_09_Hermite',        'T9 Tarot.png'),
    ('a_10_Roue_de_Fortune','T10 Tarot.png'),
    ('a_11_Justice',        'T8 Tarot.png'),    # Justice = T8 en Marseille
    ('a_12_Pendu',          'T12 Tarot.png'),
    ('a_13_Mort',           'T13 Tarot.png'),
    ('a_14_Temperance',     'T14 Tarot.png'),
    ('a_15_Diable',         'T15 Tarot.png'),
    ('a_16_Tour',           'T16 Tarot.png'),
    ('a_17_Etoile',         'T17 Tarot.png'),
    ('a_18_Lune',           'T18 Tarot.png'),
    ('a_19_Soleil',         'T19 Tarot.png'),
    ('a_20_Jugement',       'T20 Tarot.png'),
    ('a_21_Monde',          'T21 Tarot.png'),
]

THUMB_WIDTH = 500
DELAY = 4        # secondes entre chaque carte
GROUP_PAUSE = 15 # secondes entre chaque groupe de 4
GROUP_SIZE = 4
UA = 'TarotWeb/1.0 (contact: github.com/mondary)'

def get_thumb_url(commons_filename, retries=4):
    title = 'File:' + commons_filename.replace(' ', '+')
    api = f'https://commons.wikimedia.org/w/api.php?action=query&titles={title}&prop=imageinfo&iiprop=url&iiurlwidth={THUMB_WIDTH}&format=json'
    for attempt in range(retries):
        try:
            req = urllib.request.Request(api, headers={'User-Agent': UA})
            with urllib.request.urlopen(req, timeout=20) as resp:
                data = json.loads(resp.read())
            page = list(data['query']['pages'].values())[0]
            return page['imageinfo'][0]['thumburl']
        except urllib.error.HTTPError as e:
            wait = DELAY * (3 ** attempt)
            print(f'      ⏳ API 429, attente {wait}s…', flush=True)
            time.sleep(wait)
    raise Exception(f'API failed for {commons_filename}')

def download(url, dest, retries=4):
    for attempt in range(retries):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
            with urllib.request.urlopen(req, timeout=30) as resp:
                data = resp.read()
            with open(dest, 'wb') as f:
                f.write(data)
            return len(data)
        except urllib.error.HTTPError as e:
            wait = DELAY * (3 ** attempt)
            print(f'      ⏳ DL 429, attente {wait}s…', flush=True)
            time.sleep(wait)
    raise Exception(f'DL failed')

def main():
    print(f'Re-téléchargement de {len(MAJORS)} arcanes majeurs Marseille…', flush=True)
    ok, fail = 0, 0
    for i, (base, commons) in enumerate(MAJORS):
        png_tmp = os.path.join(CARDS_DIR, f'{base}_tmp.png')
        jpg_dest = os.path.join(CARDS_DIR, f'{base}_marseille.jpg')
        try:
            url = get_thumb_url(commons)
            size = download(url, png_tmp)
            # Redimensionne à 293x567 (homogène avec les versions nettoyées) + JPG q85
            subprocess.run(['magick', png_tmp, '-resize', '293x567!', '-strip', '-quality', '85', jpg_dest],
                         check=True, capture_output=True)
            os.remove(png_tmp)
            print(f'  [{i+1}/22] ✓ {base} ({size//1024} KB → 293x567)', flush=True)
            ok += 1
        except Exception as e:
            print(f'  [{i+1}/22] ✗ {base} — {e}', flush=True)
            fail += 1
        # Pause entre les cartes
        time.sleep(DELAY)
        # Pause plus longue entre les groupes
        if (i + 1) % GROUP_SIZE == 0 and i < len(MAJORS) - 1:
            print(f'  ── pause {GROUP_PAUSE}s (groupe {(i+1)//GROUP_SIZE}/6) ──', flush=True)
            time.sleep(GROUP_PAUSE)
    print(f'\nTerminé : {ok}/22 OK, {fail} échecs', flush=True)
    return 0 if fail == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
