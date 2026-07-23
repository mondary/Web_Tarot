#!/usr/bin/env python3
"""Re-télécharge les 22 arcanes MAJEURS marseille (deck Arnoult, Wikimedia).
Compense un décalage introduit lors du commit initial. Convertit en JPG."""
import os, sys, json, time, subprocess, urllib.request, urllib.error

CARDS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'website', 'cards')

# Arcanes majeurs uniquement (le décalage ne touche pas les mineurs)
MAJORS = {
    'a_00_Fou':            'TT Tarot.png',
    'a_01_Bateleur':       'T1 Tarot.png',
    'a_02_Papesse':        'T2 Tarot.png',
    'a_03_Impératrice':    'T3 Tarot.png',
    'a_04_Emperor':        'T4 Tarot.png',
    'a_05_Pape':           'T5 Tarot.png',
    'a_06_Amoureux':       'T6 Tarot.png',
    'a_07_Chariot':        'T7 Tarot.png',
    'a_08_Force':          'T11 Tarot.png',   # Force = T11 en Marseille
    'a_09_Hermite':        'T9 Tarot.png',
    'a_10_Roue_de_Fortune':'T10 Tarot.png',
    'a_11_Justice':        'T8 Tarot.png',    # Justice = T8 en Marseille
    'a_12_Pendu':          'T12 Tarot.png',
    'a_13_Mort':           'T13 Tarot.png',
    'a_14_Temperance':     'T14 Tarot.png',
    'a_15_Diable':         'T15 Tarot.png',
    'a_16_Tour':           'T16 Tarot.png',
    'a_17_Etoile':         'T17 Tarot.png',
    'a_18_Lune':           'T18 Tarot.png',
    'a_19_Soleil':         'T19 Tarot.png',
    'a_20_Jugement':       'T20 Tarot.png',
    'a_21_Monde':          'T21 Tarot.png',
}

THUMB_WIDTH = 500
DELAY = 1.5
UA = 'TarotWeb/1.0 (fix majors offset)'

def get_thumb_url(commons_filename, retries=3):
    title = 'File:' + commons_filename.replace(' ', '+')
    api = f'https://commons.wikimedia.org/w/api.php?action=query&titles={title}&prop=imageinfo&iiprop=url&iiurlwidth={THUMB_WIDTH}&format=json'
    for attempt in range(retries):
        try:
            req = urllib.request.Request(api, headers={'User-Agent': UA})
            with urllib.request.urlopen(req, timeout=15) as resp:
                data = json.loads(resp.read())
            page = list(data['query']['pages'].values())[0]
            return page['imageinfo'][0]['thumburl']
        except urllib.error.HTTPError as e:
            if e.code == 429 and attempt < retries - 1:
                time.sleep(DELAY * (2 ** attempt))
            else:
                raise

def download(url, dest, retries=3):
    for attempt in range(retries):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
            with urllib.request.urlopen(req, timeout=30) as resp:
                data = resp.read()
            with open(dest, 'wb') as f:
                f.write(data)
            return len(data)
        except urllib.error.HTTPError as e:
            if e.code == 429 and attempt < retries - 1:
                time.sleep(DELAY * (2 ** attempt))
            else:
                raise

def main():
    ok, fail = 0, 0
    total = len(MAJORS)
    for i, (base, commons) in enumerate(sorted(MAJORS.items())):
        png_tmp = os.path.join(CARDS_DIR, f'{base}_marseille_fix.png')
        jpg_dest = os.path.join(CARDS_DIR, f'{base}_marseille.jpg')
        try:
            thumb_url = get_thumb_url(commons)
            size = download(thumb_url, png_tmp)
            # Conversion PNG → JPG avec ImageMagick
            subprocess.run(['magick', png_tmp, '-strip', '-quality', '85', jpg_dest],
                         check=True, capture_output=True)
            os.remove(png_tmp)
            print(f'  [{i+1}/{total}] ✓ {base}_marseille.jpg ({size//1024} KB)')
            ok += 1
            time.sleep(DELAY)
        except Exception as e:
            print(f'  [{i+1}/{total}] ✗ {base} — {e}')
            fail += 1
            time.sleep(DELAY * 2)
    print(f'\nTerminé : {ok} re-téléchargés, {fail} échecs')

if __name__ == '__main__':
    main()
