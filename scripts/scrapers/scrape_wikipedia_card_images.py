#!/usr/bin/env python3
"""
Scrape Rider-Waite Tarot Card Images from Wikipedia
Downloads all 78 card images and compares hashes with existing images
"""

import requests
from bs4 import BeautifulSoup
import os
import time
import hashlib
from pathlib import Path

# Card mappings matching the existing naming convention - ALL 78 CARDS
CARDS = [
    # Major Arcana (0-21)
    ("a_00_Fou", "The Fool"),
    ("a_01_Bateleur", "The Magician"),
    ("a_02_Papesse", "The High Priestess"),
    ("a_03_Impératrice", "The Empress"),
    ("a_04_Emperor", "The Emperor"),
    ("a_05_Pape", "The Hierophant"),
    ("a_06_Amoureux", "The Lovers"),
    ("a_07_Chariot", "The Chariot"),
    ("a_08_Force", "Strength"),
    ("a_09_Hermite", "The Hermit"),
    ("a_10_Roue_de_Fortune", "Wheel of Fortune"),
    ("a_11_Justice", "Justice"),
    ("a_12_Pendu", "The Hanged Man"),
    ("a_13_Mort", "Death"),
    ("a_14_Temperance", "Temperance"),
    ("a_15_Diable", "The Devil"),
    ("a_16_Tour", "The Tower"),
    ("a_17_Etoile", "The Star"),
    ("a_18_Lune", "The Moon"),
    ("a_19_Soleil", "The Sun"),
    ("a_20_Jugement", "Judgment"),
    ("a_21_Monde", "The World"),
    
    # Wands (b_01 to b_14) - Bâtons
    ("b_01_As", "Ace of Wands"),
    ("b_02_Deux", "Two of Wands"),
    ("b_03_Trois", "Three of Wands"),
    ("b_04_Quatre", "Four of Wands"),
    ("b_05_Cinq", "Five of Wands"),
    ("b_06_Six", "Six of Wands"),
    ("b_07_Sept", "Seven of Wands"),
    ("b_08_Huit", "Eight of Wands"),
    ("b_09_Neuf", "Nine of Wands"),
    ("b_10_Dix", "Ten of Wands"),
    ("b_11_Valet", "Page of Wands"),
    ("b_12_Cavalier", "Knight of Wands"),
    ("b_13_Reine", "Queen of Wands"),
    ("b_14_Roi", "King of Wands"),
    
    # Cups (c_01 to c_14) - Coupes
    ("c_01_As", "Ace of Cups"),
    ("c_02_Deux", "Two of Cups"),
    ("c_03_Trois", "Three of Cups"),
    ("c_04_Quatre", "Four of Cups"),
    ("c_05_Cinq", "Five of Cups"),
    ("c_06_Six", "Six of Cups"),
    ("c_07_Sept", "Seven of Cups"),
    ("c_08_Huit", "Eight of Cups"),
    ("c_09_Neuf", "Nine of Cups"),
    ("c_10_Dix", "Ten of Cups"),
    ("c_11_Valet", "Page of Cups"),
    ("c_12_Cavalier", "Knight of Cups"),
    ("c_13_Reine", "Queen of Cups"),
    ("c_14_Roi", "King of Cups"),
    
    # Swords (d_01 to d_14) - Épées
    ("d_01_As", "Ace of Swords"),
    ("d_02_Deux", "Two of Swords"),
    ("d_03_Trois", "Three of Swords"),
    ("d_04_Quatre", "Four of Swords"),
    ("d_05_Cinq", "Five of Swords"),
    ("d_06_Six", "Six of Swords"),
    ("d_07_Sept", "Seven of Swords"),
    ("d_08_Huit", "Eight of Swords"),
    ("d_09_Neuf", "Nine of Swords"),
    ("d_10_Dix", "Ten of Swords"),
    ("d_11_Valet", "Page of Swords"),
    ("d_12_Cavalier", "Knight of Swords"),
    ("d_13_Reine", "Queen of Swords"),
    ("d_14_Roi", "King of Swords"),
    
    # Pentacles (e_01 to e_14) - Deniers
    ("e_01_As", "Ace of Pentacles"),
    ("e_02_Deux", "Two of Pentacles"),
    ("e_03_Trois", "Three of Pentacles"),
    ("e_04_Quatre", "Four of Pentacles"),
    ("e_05_Cinq", "Five of Pentacles"),
    ("e_06_Six", "Six of Pentacles"),
    ("e_07_Sept", "Seven of Pentacles"),
    ("e_08_Huit", "Eight of Pentacles"),
    ("e_09_Neuf", "Nine of Pentacles"),
    ("e_10_Dix", "Ten of Pentacles"),
    ("e_11_Valet", "Page of Pentacles"),
    ("e_12_Cavalier", "Knight of Pentacles"),
    ("e_13_Reine", "Queen of Pentacles"),
    ("e_14_Roi", "King of Pentacles"),
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
    
    for prefix, name in CARDS:
        # Try both .jpg and .jpeg extensions
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

def get_wikipedia_images():
    """Construct Wikipedia image URLs directly for all 78 cards"""
    images = {}
    
    # Wikipedia file naming pattern: RWS_Tarot_[Number]_[Name].jpg
    for prefix, name in CARDS:
        # Convert name to Wikipedia file format
        # "The Fool" -> "00_Fool", "Ace of Wands" -> "Ace_of_Wands"
        file_name = name.replace(' ', '_').replace("'", '')
        
        # Construct file page URL
        file_url = f"https://en.wikipedia.org/wiki/File:RWS_Tarot_{file_name}.jpg"
        
        # Try to get the full image URL
        full_image_url = get_full_image_url(file_url)
        if full_image_url:
            images[prefix] = {
                'url': full_image_url,
                'name': name,
                'file_url': file_url
            }
            print(f"Found: {name} -> {prefix}")
        else:
            print(f"✗ Not found: {name} ({prefix})")
    
    return images

def get_full_image_url(file_page_url):
    """Get full resolution image URL from Wikipedia file page"""
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        }
        response = requests.get(file_page_url, headers=headers, timeout=10)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Find the full resolution image link (usually in a div with class 'fullImageLink')
        full_link_div = soup.find('div', class_='fullImageLink')
        if full_link_div and full_link_div.find('a'):
            href = full_link_div.find('a').get('href', '')
            if href:
                # Construct full URL
                if href.startswith('//'):
                    return f"https:{href}"
                elif href.startswith('/'):
                    return f"https://en.wikipedia.org{href}"
                return href
        
        # Fallback: look for the original file link
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
        print(f"Error getting full image URL from {file_page_url}: {e}")
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
        
        # Calculate hash of downloaded content
        sha256_hash = hashlib.sha256()
        sha256_hash.update(content)
        content_hash = sha256_hash.hexdigest()
        
        return {
            'content': content,
            'hash': content_hash,
            'size': len(content)
        }
        
    except Exception as e:
        print(f"✗ Failed to download from {url}: {e}")
        return None

def main():
    """Main scraping function"""
    script_dir = Path(__file__).parent
    output_dir = script_dir / "wikipedia_images"
    output_dir.mkdir(exist_ok=True)
    
    cards_dir = Path(__file__).parent.parent.parent / "website" / "cards"
    
    print("=" * 60)
    print("Wikipedia Rider-Waite Tarot Image Scraper")
    print("=" * 60)
    
    # Get existing hashes
    existing_hashes = get_existing_hashes(cards_dir)
    print(f"\nFound {len(existing_hashes)} existing card images in {cards_dir}")
    
    # Get Wikipedia images
    wiki_images = get_wikipedia_images()
    
    if not wiki_images:
        print("No images found on Wikipedia page")
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