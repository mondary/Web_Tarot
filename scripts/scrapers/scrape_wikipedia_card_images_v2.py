#!/usr/bin/env python3
"""
Scrape Rider-Waite Tarot Card Images from Wikipedia - ENHANCED VERSION
Uses multiple strategies to find all 78 card images
"""

import requests
from bs4 import BeautifulSoup
import os
import time
import hashlib
from pathlib import Path
import re

# Card mappings matching the existing naming convention - ALL 78 CARDS
CARDS = [
    # Major Arcana (0-21)
    ("a_00_Fou", "The Fool", "00_Fool"),
    ("a_01_Bateleur", "The Magician", "01_Magician"),
    ("a_02_Papesse", "The High Priestess", "02_High_Priestess"),
    ("a_03_Impératrice", "The Empress", "03_Empress"),
    ("a_04_Emperor", "The Emperor", "04_Emperor"),
    ("a_05_Pape", "The Hierophant", "05_Hierophant"),
    ("a_06_Amoureux", "The Lovers", "06_Lovers"),
    ("a_07_Chariot", "The Chariot", "07_Chariot"),
    ("a_08_Force", "Strength", "08_Strength"),
    ("a_09_Hermite", "The Hermit", "09_Hermit"),
    ("a_10_Roue_de_Fortune", "Wheel of Fortune", "10_Wheel_of_Fortune"),
    ("a_11_Justice", "Justice", "11_Justice"),
    ("a_12_Pendu", "The Hanged Man", "12_Hanged_Man"),
    ("a_13_Mort", "Death", "13_Death"),
    ("a_14_Temperance", "Temperance", "14_Temperance"),
    ("a_15_Diable", "The Devil", "15_Devil"),
    ("a_16_Tour", "The Tower", "16_Tower"),
    ("a_17_Etoile", "The Star", "17_Star"),
    ("a_18_Lune", "The Moon", "18_Moon"),
    ("a_19_Soleil", "The Sun", "19_Sun"),
    ("a_20_Jugement", "Judgment", "20_Judgment"),
    ("a_21_Monde", "The World", "21_World"),
    
    # Wands (b_01 to b_14) - Bâtons
    ("b_01_As", "Ace of Wands", "Wands_01"),
    ("b_02_Deux", "Two of Wands", "Wands_02"),
    ("b_03_Trois", "Three of Wands", "Wands_03"),
    ("b_04_Quatre", "Four of Wands", "Wands_04"),
    ("b_05_Cinq", "Five of Wands", "Wands_05"),
    ("b_06_Six", "Six of Wands", "Wands_06"),
    ("b_07_Sept", "Seven of Wands", "Wands_07"),
    ("b_08_Huit", "Eight of Wands", "Wands_08"),
    ("b_09_Neuf", "Nine of Wands", "Wands_09"),
    ("b_10_Dix", "Ten of Wands", "Wands_10"),
    ("b_11_Valet", "Page of Wands", "Wands_11"),
    ("b_12_Cavalier", "Knight of Wands", "Wands_12"),
    ("b_13_Reine", "Queen of Wands", "Wands_13"),
    ("b_14_Roi", "King of Wands", "Wands_14"),
    
    # Cups (c_01 to c_14) - Coupes
    ("c_01_As", "Ace of Cups", "Cups_01"),
    ("c_02_Deux", "Two of Cups", "Cups_02"),
    ("c_03_Trois", "Three of Cups", "Cups_03"),
    ("c_04_Quatre", "Four of Cups", "Cups_04"),
    ("c_05_Cinq", "Five of Cups", "Cups_05"),
    ("c_06_Six", "Six of Cups", "Cups_06"),
    ("c_07_Sept", "Seven of Cups", "Cups_07"),
    ("c_08_Huit", "Eight of Cups", "Cups_08"),
    ("c_09_Neuf", "Nine of Cups", "Cups_09"),
    ("c_10_Dix", "Ten of Cups", "Cups_10"),
    ("c_11_Valet", "Page of Cups", "Cups_11"),
    ("c_12_Cavalier", "Knight of Cups", "Cups_12"),
    ("c_13_Reine", "Queen of Cups", "Cups_13"),
    ("c_14_Roi", "King of Cups", "Cups_14"),
    
    # Swords (d_01 to d_14) - Épées
    ("d_01_As", "Ace of Swords", "Swords_01"),
    ("d_02_Deux", "Two of Swords", "Swords_02"),
    ("d_03_Trois", "Three of Swords", "Swords_03"),
    ("d_04_Quatre", "Four of Swords", "Swords_04"),
    ("d_05_Cinq", "Five of Swords", "Swords_05"),
    ("d_06_Six", "Six of Swords", "Swords_06"),
    ("d_07_Sept", "Seven of Swords", "Swords_07"),
    ("d_08_Huit", "Eight of Swords", "Swords_08"),
    ("d_09_Neuf", "Nine of Swords", "Swords_09"),
    ("d_10_Dix", "Ten of Swords", "Swords_10"),
    ("d_11_Valet", "Page of Swords", "Swords_11"),
    ("d_12_Cavalier", "Knight of Swords", "Swords_12"),
    ("d_13_Reine", "Queen of Swords", "Swords_13"),
    ("d_14_Roi", "King of Swords", "Swords_14"),
    
    # Pentacles (e_01 to e_14) - Deniers
    ("e_01_As", "Ace of Pentacles", "Pentacles_01"),
    ("e_02_Deux", "Two of Pentacles", "Pentacles_02"),
    ("e_03_Trois", "Three of Pentacles", "Pentacles_03"),
    ("e_04_Quatre", "Four of Pentacles", "Pentacles_04"),
    ("e_05_Cinq", "Five of Pentacles", "Pentacles_05"),
    ("e_06_Six", "Six of Pentacles", "Pentacles_06"),
    ("e_07_Sept", "Seven of Pentacles", "Pentacles_07"),
    ("e_08_Huit", "Eight of Pentacles", "Pentacles_08"),
    ("e_09_Neuf", "Nine of Pentacles", "Pentacles_09"),
    ("e_10_Dix", "Ten of Pentacles", "Pentacles_10"),
    ("e_11_Valet", "Page of Pentacles", "Pentacles_11"),
    ("e_12_Cavalier", "Knight of Pentacles", "Pentacles_12"),
    ("e_13_Reine", "Queen of Pentacles", "Pentacles_13"),
    ("e_14_Roi", "King of Pentacles", "Pentacles_14"),
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
        print(f"Error calculating hash for {filepath}: {e}")
        return None

def get_existing_hashes(cards_dir):
    """Get hashes of existing card images"""
    hashes = {}
    
    for prefix, name, wiki_name in CARDS:
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
                    return f"https://en.wikipedia.org{href}"
                return href
        
        # Fallback: look for upload.wikimedia.org links
        for link in soup.find_all('a', href=True):
            href = link.get('href', '')
            if 'upload.wikimedia.org' in href and 'jpg' in href.lower():
                if href.startswith('//'):
                    return f"https:{href}"
                elif href.startswith('/'):
                    return f"https:{href}"
                return href
        
        return None
        
    except Exception as e:
        return None

def get_wikipedia_images():
    """Construct Wikipedia image URLs using known patterns"""
    images = {}
    
    # Try different tarot deck naming patterns on Wikipedia
    patterns = [
        "RWS_Tarot_{name}.jpg",
        "1JJ_Tarot_-_{name}.jpg", 
        "Rider-Waite_{name}.jpg",
    ]
    
    for prefix, name, wiki_name in CARDS:
        image_url = None
        
        for pattern in patterns:
            test_name = wiki_name.replace('_', ' ')
            file_url = f"https://en.wikipedia.org/wiki/File:{pattern.format(name=test_name)}"
            
            # Try to get the full image URL
            image_url = get_full_image_url(file_url)
            if image_url:
                images[prefix] = {
                    'url': image_url,
                    'name': name,
                    'wiki_name': wiki_name,
                    'file_url': file_url
                }
                print(f"✓ Found: {name} -> {prefix}")
                break
        
        if not image_url:
            print(f"✗ Not found: {name} ({prefix})")
    
    return images

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
        print(f"  ✗ Download failed: {e}")
        return None

def main():
    """Main scraping function"""
    script_dir = Path(__file__).parent
    output_dir = script_dir / "wikipedia_images"
    output_dir.mkdir(exist_ok=True)
    
    cards_dir = Path(__file__).parent.parent.parent / "website" / "cards"
    
    print("=" * 60)
    print("Wikipedia Rider-Waite Tarot Image Scraper - ENHANCED")
    print("=" * 60)
    
    # Get existing hashes
    existing_hashes = get_existing_hashes(cards_dir)
    print(f"\nFound {len(existing_hashes)} existing card images")
    
    # Get Wikipedia images
    wiki_images = get_wikipedia_images()
    
    if not wiki_images:
        print("\nNo images found on Wikipedia")
        return
    
    print(f"\nFound {len(wiki_images)} card images on Wikipedia")
    print(f"Output directory: {output_dir}")
    
    # Compare and download
    identical = 0
    different = 0
    missing = 0
    failed = 0
    
    for prefix, data in wiki_images.items():
        print(f"\n--- {prefix} ({data['name']}) ---")
        
        if prefix in existing_hashes:
            existing = existing_hashes[prefix]
            print(f"Existing: {existing['size']} bytes, hash: {existing['hash'][:16]}...")
            
            downloaded = download_image(data['url'])
            
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
                failed += 1
        else:
            print("Not found in existing cards - downloading...")
            downloaded = download_image(data['url'])
            
            if downloaded:
                filename = f"{prefix}.jpg"
                filepath = os.path.join(output_dir, filename)
                with open(filepath, 'wb') as f:
                    f.write(downloaded['content'])
                print(f"✓ Downloaded new image: {filename}")
                missing += 1
            else:
                failed += 1
        
        # Longer delay between downloads to avoid rate limiting
        time.sleep(5)
    
    print("\n" + "=" * 60)
    print("Results:")
    print(f"  ✓ Identical (skipped): {identical}")
    print(f"  ✗ Different (downloaded): {different}")
    print(f"  ✓ New (downloaded): {missing}")
    print(f"  ✗ Failed: {failed}")
    print(f"\nNew images saved to: {output_dir}")
    print("=" * 60)

if __name__ == "__main__":
    main()