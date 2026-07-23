#!/usr/bin/env python3
"""
Télécharge les 78 images Marseille (deck Arnoult, Wikimedia Commons)
en miniature 500px — ~800 Ko par carte — avec suffixe _marseille.
"""
import os, sys, urllib.request, urllib.error, json, time

CARDS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'website', 'cards')

MAPPING = {
    'a_00_Fou':           'TT Tarot.png',
    'a_01_Bateleur':      'T1 Tarot.png',
    'a_02_Papesse':       'T2 Tarot.png',
    'a_03_Impératrice':   'T3 Tarot.png',
    'a_04_Emperor':       'T4 Tarot.png',
    'a_05_Pape':          'T5 Tarot.png',
    'a_06_Amoureux':      'T6 Tarot.png',
    'a_07_Chariot':       'T7 Tarot.png',
    'a_08_Force':         'T11 Tarot.png',   # Force = T11 en Marseille (inversion vs Rider-Waite)
    'a_09_Hermite':       'T9 Tarot.png',
    'a_10_Roue_de_Fortune':'T10 Tarot.png',
    'a_11_Justice':       'T8 Tarot.png',    # Justice = T8 en Marseille (inversion vs Rider-Waite)
    'a_12_Pendu':         'T12 Tarot.png',
    'a_13_Mort':          'T13 Tarot.png',
    'a_14_Temperance':    'T14 Tarot.png',
    'a_15_Diable':        'T15 Tarot.png',
    'a_16_Tour':          'T16 Tarot.png',
    'a_17_Etoile':        'T17 Tarot.png',
    'a_18_Lune':          'T18 Tarot.png',
    'a_19_Soleil':        'T19 Tarot.png',
    'a_20_Jugement':      'T20 Tarot.png',
    'a_21_Monde':         'T21 Tarot.png',
    'b_01_As':            '1B Tarot.png',
    'b_02_Deux':          '2B Tarot.png',
    'b_03_Trois':         '3B Tarot.png',
    'b_04_Quatre':        '4B Tarot.png',
    'b_05_Cinq':          '5B Tarot.png',
    'b_06_Six':           '6B Tarot.png',
    'b_07_Sept':          '7B Tarot.png',
    'b_08_Huit':          '8B Tarot.png',
    'b_09_Neuf':          '9B Tarot.png',
    'b_10_Dix':           '10B Tarot.png',
    'b_11_Valet':         'JB Tarot.png',
    'b_12_Cavalier':      'HB Tarot.png',
    'b_13_Reine':         'QB Tarot.png',
    'b_14_Roi':           'KB Tarot.png',
    'c_01_As':            '1C Tarot.png',
    'c_02_Deux':          '2C Tarot.png',
    'c_03_Trois':         '3C Tarot.png',
    'c_04_Quatre':        '4C Tarot.png',
    'c_05_Cinq':          '5C Tarot.png',
    'c_06_Six':           '6C Tarot.png',
    'c_07_Sept':          '7C Tarot.png',
    'c_08_Huit':          '8C Tarot.png',
    'c_09_Neuf':          '9C Tarot.png',
    'c_10_Dix':           '10C Tarot.png',
    'c_11_Valet':         'JC Tarot.png',
    'c_12_Cavalier':      'HC Tarot.png',
    'c_13_Reine':         'QC Tarot.png',
    'c_14_Roi':           'KC Tarot.png',
    'd_01_As':            '1P Tarot.png',
    'd_02_Deux':          '2P Tarot.png',
    'd_03_Trois':         '3P Tarot.png',
    'd_04_Quatre':        '4P Tarot.png',
    'd_05_Cinq':          '5P Tarot.png',
    'd_06_Six':           '6P Tarot.png',
    'd_07_Sept':          '7P Tarot.png',
    'd_08_Huit':          '8P Tarot.png',
    'd_09_Neuf':          '9P Tarot.png',
    'd_10_Dix':           '10P Tarot.png',
    'd_11_Valet':         'JP Tarot.png',
    'd_12_Cavalier':      'HP Tarot.png',
    'd_13_Reine':         'QP Tarot.png',
    'd_14_Roi':           'KP Tarot.png',
    'e_01_As':            '1S Tarot.png',
    'e_02_Deux':          '2S Tarot.png',
    'e_03_Trois':         '3S Tarot.png',
    'e_04_Quatre':        '4S Tarot.png',
    'e_05_Cinq':          '5S Tarot.png',
    'e_06_Six':           '6S Tarot.png',
    'e_07_Sept':          '7S Tarot.png',
    'e_08_Huit':          '8S Tarot.png',
    'e_09_Neuf':          '9S Tarot.png',
    'e_10_Dix':           '10S Tarot.png',
    'e_11_Valet':         'JS Tarot.png',
    'e_12_Cavalier':      'HS Tarot.png',
    'e_13_Reine':         'QS Tarot.png',
    'e_14_Roi':           'KS Tarot.png',
}

THUMB_WIDTH = 500
DELAY = 5  # secondes entre chaque requête
UA = 'TarotWeb/1.0 (https://github.com/awesome-selfhosted; contact@example.com)'

def get_thumb_url(commons_filename, retries=3):
    """Interroge l'API Wikimedia pour obtenir l'URL de la miniature."""
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
                wait = DELAY * (2 ** attempt)
                print(f'    ⏳ rate-limit API, attente {wait}s…')
                time.sleep(wait)
            else:
                raise

def download(url, dest, retries=3):
    for attempt in range(retries):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'})
            with urllib.request.urlopen(req, timeout=30) as resp:
                data = resp.read()
            with open(dest, 'wb') as f:
                f.write(data)
            return len(data)
        except urllib.error.HTTPError as e:
            if e.code == 429 and attempt < retries - 1:
                wait = DELAY * (2 ** attempt)
                print(f'    ⏳ rate-limit download, attente {wait}s…')
                time.sleep(wait)
            else:
                raise

def main():
    ok, skip, fail = 0, 0, 0
    total = len(MAPPING)

    for i, (base, commons) in enumerate(sorted(MAPPING.items())):
        dest = os.path.join(CARDS_DIR, f'{base}_marseille.png')
        if os.path.exists(dest) and os.path.getsize(dest) > 10000:
            print(f'  [{i+1}/{total}] ✓ {base}_marseille.png (existe)')
            skip += 1
            continue

        try:
            thumb_url = get_thumb_url(commons)
            size = download(thumb_url, dest)
            print(f'  [{i+1}/{total}] ✓ {base}_marseille.png ({size//1024} KB)')
            ok += 1
            time.sleep(DELAY)  # respect rate limit Wikimedia
        except Exception as e:
            print(f'  [{i+1}/{total}] ✗ {base} — {e}')
            fail += 1
            time.sleep(DELAY * 2)

    print(f'\nTerminé : {ok} téléchargées, {skip} existantes, {fail} échecs')

if __name__ == '__main__':
    main()
