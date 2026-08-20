#!/usr/bin/env python3
"""
Scrape Rider-Waite Tarot Card Images from Wikipedia FR - FINAL VERSION
Downloads all 78 cards using French Wikipedia (has all images!)
"""

import requests
from bs4 import BeautifulSoup
import os
import time
import hashlib
from pathlib import Path

# Card mappings with Wikipedia FR file names
CARDS = [
    # Major Arcana (0-21)
    ("a_00_Fou", "RWS_Tarot_00_Fool.jpg"),
    ("a_01_Bateleur", "RWS_Tarot_01_Magician.jpg"),
    ("a_02_Papesse", "RWS_Tarot_02_High_Priestess.jpg"),
    ("a_03_Impératrice", "RWS_Tarot_03_Empress.jpg"),
    ("a_04_Emperor", "RWS_Tarot_04_Emperor.jpg"),
    ("a_05_Pape", "RWS_Tarot_05_Hierophant.jpg"),
    ("a_06_Amoureux", "RWS_Tarot_06_Lovers.jpg"),
    ("a_07_Chariot", "RWS_Tarot_07_Chariot.jpg"),
    ("a_08_Force", "RWS_Tarot_08_Strength.jpg"),
    ("a_09_Hermite", "RWS_Tarot_09_Hermit.jpg"),
    ("a_10_Roue_de_Fortune", "RWS_Tarot_10_Wheel_of_Fortune.jpg"),
    ("a_11_Justice", "RWS_Tarot_11_Justice.jpg"),
    ("a_12_Pendu", "RWS_Tarot_12_Hanged_Man.jpg"),
    ("a_13_Mort", "RWS_Tarot_13_Death.jpg"),
    ("a_14_Temperance", "RWS_Tarot_14_Temperance.jpg"),
    ("a_15_Diable", "RWS_Tarot_15_Devil.jpg"),
    ("a_16_Tour", "RWS_Tarot_16_Tower.jpg"),
    ("a_17_Etoile", "RWS_Tarot_17_Star.jpg"),
    ("a_18_Lune", "RWS_Tarot_18_Moon.jpg"),
    ("a_19_Soleil", "RWS_Tarot_19_Sun.jpg"),
    ("a_20_Jugement", "RWS_Tarot_20_Judgement.jpg"),
    ("a_21_Monde", "RWS_Tarot_21_World.jpg"),
    
    # Wands (b_01 to b_14) - Bâtons
    ("b_01_As", "Wands01.jpg"),
    ("b_02_Deux", "Wands02.jpg"),
    ("b_03_Trois", "Wands03.jpg"),
    ("b_04_Quatre", "Wands04.jpg"),
    ("b_05_Cinq", "Wands05.jpg"),
    ("b_06_Six", "Wands06.jpg"),
    ("b_07_Sept", "Wands07.jpg"),
    ("b_08_Huit", "Wands08.jpg"),
    ("b_09_Neuf", "Wands09.jpg"),
    ("b_10_Dix", "Wands10.jpg"),
    ("b_11_Valet", "Wands11.jpg"),
    ("b_12_Cavalier", "Wands12.jpg"),
    ("b_13_Reine", "Wands13.jpg"),
    ("b_14_Roi", "Wands14.jpg"),
    
    # Cups (c_01 to c_14) - Coupes
    ("c_01_As", "Cups01.jpg"),
    ("c_02_Deux", "Cups02.jpg"),
    ("c_03_Trois", "Cups03.jpg"),
    ("c_04_Quatre", "Cups04.jpg"),
    ("c_05_Cinq", "Cups05.jpg"),
    ("c_06_Six", "Cups06.jpg"),
    ("c_07_Sept", "Cups07.jpg"),
    ("c_08_Huit", "Cups08.jpg"),
    ("c_09_Neuf", "Cups09.jpg"),
    ("c_10_Dix", "Cups10.jpg"),
    ("c_11_Valet", "Cups11.jpg"),
    ("c_12_Cavalier", "Cups12.jpg"),
    ("c_13_Reine", "Cups13.jpg"),
    ("c_14_Roi", "Cups14.jpg"),
    
    # Swords (d_01 to d_14) - Épées
    ("d_01_As", "Swords01.jpg"),
    ("d_02_Deux", "Swords02.jpg"),
    ("d_03_Trois", "Swords03.jpg"),
    ("d_04_Quatre", "Swords04.jpg"),
    ("d_05_Cinq", "Swords05.jpg"),
    ("d_06_Six", "Swords06.jpg"),
    ("d_07_Sept", "Swords07.jpg"),
    ("d_08_Huit", "Swords08.jpg"),
    ("d_09_Neuf", "Swords09.jpg"),
    ("d_10_Dix", "Swords10.jpg"),
    ("d_11_Valet", "Swords11.jpg"),
    ("d_12_Cavalier", "Swords12.jpg"),
    ("d_13_Reine", "Swords13.jpg"),
    ("d_14_Roi", "Swords14.jpg"),
    
    # Pentacles (e_01 to e_14) - Deniers (Pents = Pentacles)
    ("e_01_As", "Pents01.jpg"),
    ("e_02_Deux", "Pents02.jpg"),
    ("e_03_Trois", "Pents03.jpg"),
    ("e_04_Quatre", "Pents04.jpg"),
    ("e_05_Cinq", "Pents05.jpg"),
    ("e_06_Six", "Pents06.jpg"),
    ("e_07_Sept", "Pents07.jpg"),
    ("e_08_Huit", "Pents08.jpg"),
    ("e_09_Neuf", "Pents09.jpg"),
    ("e_10_Dix", "Pents10.jpg"),
    ("e_11_Valet", "Pents11.jpg"),
    ("e_12_Cavalier", "Pents12.jpg"),
    ("e_13_Reine", "Pents13.jpg"),
    ("e_14_Roi", "Pents14.jpg"),
]

def calculate_file_hash(filepath):
    """Calculate SHA256 hash of a file"""
    sha256_hash = hashlib.sha256()
    try:
        with open(filepath, "rb") as f:
            for byte_block in iter(lambda: f.read(4096), b""):
                sha256_hash.update(byte_block)
        return sha256_hash.hexdigest()
    except Exception as e:
        return None

def get_existing_hashes(cards_dir):
    """Get hashes of existing card images"""
    hashes = {}
    for prefix, wiki_file in CARDS:
        for ext in ['.jpg', '.jpeg']:
            filepath = os.path.join(cards_dir, f"{prefix}{ext}")
            if os.path.exists(filepath):
                file_hash = calculate_file_hash(filepath)
                if file_hash:
                    hashes[prefix] = {
                        'hash': file_hash,
                        'filepath': filepath,
                        'size': os.path.getsize(filepath)
                    }
                break
    return hashes

def get_full_image_url(file_page_url):
    """Get full resolution image URL from Wikipedia file page"""
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        }
        response = requests.get(file_page_url, headers=headers, timeout=10)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Find the full resolution image link
        full_link_div = soup.find('div', class_='fullImageLink')
        if full_link_div and full_link_div.find('a'):
            href = full_link_div.find('a').get('href', '')
            if href:
                if href.startswith('//'):
                    return f"https:{href}"
                elif href.startswith('/'):
                    return f"https://fr.wikipedia.org{href}"
                return href
        
        return None
    except Exception as e:
        return None

def download_image(url):
    """Download image and return content and hash"""
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=15)
        response.raise_for_status()
        
        content = response.content
        sha256_hash = hashlib.sha256()
        sha256_hash.update(content)
        content_hash = sha256_hash.hexdigest()
        
        return {
            'content': content,
            'hash': content_hash,
            'size': len(content)
        }
    except Exception as e:
        return None

def main():
    """Main scraping function"""
    script_dir = Path(__file__).parent
    output_dir = script_dir / "wikipedia_images"
    output_dir.mkdir(exist_ok=True)
    
    cards_dir = Path(__file__).parent.parent.parent / "website" / "cards"
    
    print("=" * 60)
    print("Wikipedia FR Rider-Waite Tarot Image Scraper - FINAL")
    print("=" * 60)
    
    existing_hashes = get_existing_hashes(cards_dir)
    print(f"\nFound {len(existing_hashes)} existing card images")
    
    identical = 0
    different = 0
    missing = 0
    failed = 0
    not_found = 0
    
    for prefix, wiki_file in CARDS:
        # Construct French Wikipedia file page URL
        file_page_url = f"https://fr.wikipedia.org/wiki/Fichier:{wiki_file}"
        
        # Get full image URL
        image_url = get_full_image_url(file_page_url)
        
        if not image_url:
            print(f"✗ Not found: {prefix} ({wiki_file})")
            not_found += 1
            continue
        
        print(f"\n--- {prefix} ({wiki_file}) ---")
        
        if prefix in existing_hashes:
            existing = existing_hashes[prefix]
            print(f"Existing: {existing['size']} bytes, hash: {existing['hash'][:16]}...")
            
            downloaded = download_image(image_url)
            
            if downloaded:
                print(f"Downloaded: {downloaded['size']} bytes, hash: {downloaded['hash'][:16]}...")
                
                if downloaded['hash'] == existing['hash']:
                    print("✓ Identical - skipping download")
                    identical += 1
                else:
                    print("✗ Different - saving new version")
                    filename = f"{prefix}.jpg"
                    filepath = os.path.join(output_dir, filename)
                    with open(filepath, 'wb') as f:
                        f.write(downloaded['content'])
                    different += 1
            else:
                print("✗ Download failed")
                failed += 1
        else:
            print("Not found in existing cards - downloading...")
            downloaded = download_image(image_url)
            
            if downloaded:
                filename = f"{prefix}.jpg"
                filepath = os.path.join(output_dir, filename)
                with open(filepath, 'wb') as f:
                    f.write(downloaded['content'])
                print(f"✓ Downloaded new image: {filename}")
                missing += 1
            else:
                print("✗ Download failed")
                failed += 1
        
        # Delay to avoid rate limiting
        time.sleep(5)
    
    print("\n" + "=" * 60)
    print("Results:")
    print(f"  ✓ Identical (skipped): {identical}")
    print(f"  ✗ Different (downloaded): {different}")
    print(f"  ✓ New (downloaded): {missing}")
    print(f"  ✗ Failed: {failed}")
    print(f"  ✗ Not found on Wikipedia FR: {not_found}")
    print(f"\nNew images saved to: {output_dir}")
    print("=" * 60)

if __name__ == "__main__":
    main()