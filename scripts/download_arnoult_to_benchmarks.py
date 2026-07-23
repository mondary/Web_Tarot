#!/usr/bin/env python3
"""Télécharge le deck Marseille Arnoult BRUT (500px PNG, Wikimedia Commons)
vers benchmarks/decks/marseille_arnoult/. Mapping corrigé : T8=Justice, T11=Force."""
import os, json, time, urllib.request, urllib.error, sys

DEST = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'benchmarks', 'decks', 'marseille_arnoult')
os.makedirs(DEST, exist_ok=True)

# Mapping complet 78 cartes (préfixe_num_slug → fichier Wikimedia Commons)
MAPPING = {
    # Majeurs (T8=Justice, T11=Force en Marseille)
    'a_00_Fou':'TT Tarot.png','a_01_Bateleur':'T1 Tarot.png','a_02_Papesse':'T2 Tarot.png',
    'a_03_Impératrice':'T3 Tarot.png','a_04_Emperor':'T4 Tarot.png','a_05_Pape':'T5 Tarot.png',
    'a_06_Amoureux':'T6 Tarot.png','a_07_Chariot':'T7 Tarot.png',
    'a_08_Force':'T11 Tarot.png','a_09_Hermite':'T9 Tarot.png','a_10_Roue_de_Fortune':'T10 Tarot.png',
    'a_11_Justice':'T8 Tarot.png','a_12_Pendu':'T12 Tarot.png','a_13_Mort':'T13 Tarot.png',
    'a_14_Temperance':'T14 Tarot.png','a_15_Diable':'T15 Tarot.png','a_16_Tour':'T16 Tarot.png',
    'a_17_Etoile':'T17 Tarot.png','a_18_Lune':'T18 Tarot.png','a_19_Soleil':'T19 Tarot.png',
    'a_20_Jugement':'T20 Tarot.png','a_21_Monde':'T21 Tarot.png',
    # Bâtons (B)
    'b_01_As':'1B Tarot.png','b_02_Deux':'2B Tarot.png','b_03_Trois':'3B Tarot.png',
    'b_04_Quatre':'4B Tarot.png','b_05_Cinq':'5B Tarot.png','b_06_Six':'6B Tarot.png',
    'b_07_Sept':'7B Tarot.png','b_08_Huit':'8B Tarot.png','b_09_Neuf':'9B Tarot.png',
    'b_10_Dix':'10B Tarot.png','b_11_Valet':'JB Tarot.png','b_12_Cavalier':'HB Tarot.png',
    'b_13_Reine':'QB Tarot.png','b_14_Roi':'KB Tarot.png',
    # Coupes (C)
    'c_01_As':'1C Tarot.png','c_02_Deux':'2C Tarot.png','c_03_Trois':'3C Tarot.png',
    'c_04_Quatre':'4C Tarot.png','c_05_Cinq':'5C Tarot.png','c_06_Six':'6C Tarot.png',
    'c_07_Sept':'7C Tarot.png','c_08_Huit':'8C Tarot.png','c_09_Neuf':'9C Tarot.png',
    'c_10_Dix':'10C Tarot.png','c_11_Valet':'JC Tarot.png','c_12_Cavalier':'HC Tarot.png',
    'c_13_Reine':'QC Tarot.png','c_14_Roi':'KC Tarot.png',
    # Deniers (P = Piece)
    'd_01_As':'1P Tarot.png','d_02_Deux':'2P Tarot.png','d_03_Trois':'3P Tarot.png',
    'd_04_Quatre':'4P Tarot.png','d_05_Cinq':'5P Tarot.png','d_06_Six':'6P Tarot.png',
    'd_07_Sept':'7P Tarot.png','d_08_Huit':'8P Tarot.png','d_09_Neuf':'9P Tarot.png',
    'd_10_Dix':'10P Tarot.png','d_11_Valet':'JP Tarot.png','d_12_Cavalier':'HP Tarot.png',
    'd_13_Reine':'QP Tarot.png','d_14_Roi':'KP Tarot.png',
    # Épées (S = Sword)
    'e_01_As':'1S Tarot.png','e_02_Deux':'2S Tarot.png','e_03_Trois':'3S Tarot.png',
    'e_04_Quatre':'4S Tarot.png','e_05_Cinq':'5S Tarot.png','e_06_Six':'6S Tarot.png',
    'e_07_Sept':'7S Tarot.png','e_08_Huit':'8S Tarot.png','e_09_Neuf':'9S Tarot.png',
    'e_10_Dix':'10S Tarot.png','e_11_Valet':'JS Tarot.png','e_12_Cavalier':'HS Tarot.png',
    'e_13_Reine':'QS Tarot.png','e_14_Roi':'KS Tarot.png',
}

THUMB_WIDTH = 500
DELAY = 5
GROUP_SIZE = 6
GROUP_PAUSE = 20

def get_thumb_url(commons_filename, retries=4):
    title = 'File:' + commons_filename.replace(' ', '+')
    api = f'https://commons.wikimedia.org/w/api.php?action=query&titles={title}&prop=imageinfo&iiprop=url&iiurlwidth={THUMB_WIDTH}&format=json'
    for attempt in range(retries):
        try:
            req = urllib.request.Request(api, headers={'User-Agent': 'TarotWeb/1.0 (github.com/mondary)'})
            with urllib.request.urlopen(req, timeout=20) as resp:
                return json.loads(resp.read())['query']['pages']
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
                open(dest, 'wb').write(resp.read())
            return True
        except urllib.error.HTTPError as e:
            wait = DELAY * (3 ** attempt)
            print(f'      ⏳ DL 429, attente {wait}s…', flush=True)
            time.sleep(wait)
    return False

def main():
    items = sorted(MAPPING.items())
    ok, skip, fail = 0, 0, 0
    for i, (base, commons) in enumerate(items):
        dest = os.path.join(DEST, f'{base}.png')
        if os.path.exists(dest) and os.path.getsize(dest) > 10000:
            print(f'  [{i+1}/78] ✓ {base} (existe)', flush=True)
            skip += 1
            continue
        try:
            pages = get_thumb_url(commons)
            page = list(pages.values())[0]
            url = page['imageinfo'][0]['thumburl']
            if download(url, dest):
                size = os.path.getsize(dest) // 1024
                print(f'  [{i+1}/78] ✓ {base}.png ({size} KB)', flush=True)
                ok += 1
            else:
                print(f'  [{i+1}/78] ✗ {base}', flush=True)
                fail += 1
        except Exception as e:
            print(f'  [{i+1}/78] ✗ {base} — {e}', flush=True)
            fail += 1
        time.sleep(DELAY)
        if (i + 1) % GROUP_SIZE == 0 and i < len(items) - 1:
            print(f'  ── pause {GROUP_PAUSE}s ──', flush=True)
            time.sleep(GROUP_PAUSE)
    print(f'\nTerminé : {ok} téléchargés, {skip} existants, {fail} échecs', flush=True)

if __name__ == '__main__':
    main()
