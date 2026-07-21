#!/usr/bin/env python3

import requests
import os
import re
from urllib.parse import quote

def download_tanot_cards():
    base_url = "https://www.thetarotlady.com/tarot-card-by-card-{card_name}/"
    output_dir = "/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website/images_thetarotlady/"
    
    os.makedirs(output_dir, exist_ok=True)
    
    major_arcana = [
        ("the-fool", "a_00_Fou.jpg"),
        ("the-magician", "a_01_Magicien.jpg"),
        ("the-high-priestess", "a_02_Papesse.jpg"),
        ("the-empress", "a_03_Impératrice.jpg"),
        ("the-emperor", "a_04_Empereur.jpg"),
        ("the-hierophant", "a_05_Pape.jpg"),
        ("the-lovers", "a_06_Amoureux.jpg"),
        ("the-chariot", "a_07_Chariot.jpg"),
        ("strength", "a_08_Force.jpg"),
        ("the-hermit", "a_09_Ermite.jpg"),
        ("wheel-of-fortune", "a_10_Roue_de_Fortune.jpg"),
        ("justice", "a_11_Justice.jpg"),
        ("the-hanged-man", "a_12_Pendu.jpg"),
        ("death", "a_13_Mort.jpg"),
        ("temperance", "a_14_Tempérance.jpg"),
        ("the-devil", "a_15_Diable.jpg"),
        ("the-tower", "a_16_Tour.jpg"),
        ("the-star", "a_17_Étoile.jpg"),
        ("the-moon", "a_18_Lune.jpg"),
        ("the-sun", "a_19_Soleil.jpg"),
        ("judgment", "a_20_Jugement.jpg"),
        ("the-world", "a_21_Monde.jpg"),
    ]
    
    wands = [
        ("ace-of-wands", "b_01_As_de_Bâton.jpg"),
        ("two-of-wands", "b_02_Deux_de_Bâton.jpg"),
        ("three-of-wands", "b_03_Trois_de_Bâton.jpg"),
        ("four-of-wands", "b_04_Quatre_de_Bâton.jpg"),
        ("five-of-wands", "b_05_Cinq_de_Bâton.jpg"),
        ("six-of-wands", "b_06_Six_de_Bâton.jpg"),
        ("seven-of-wands", "b_07_Sept_de_Bâton.jpg"),
        ("eight-of-wands", "b_08_Huit_de_Bâton.jpg"),
        ("nine-of-wands", "b_09_Neuf_de_Bâton.jpg"),
        ("ten-of-wands", "b_10_Dix_de_Bâton.jpg"),
        ("page-of-wands", "b_11_Valet_de_Bâton.jpg"),
        ("knight-of-wands", "b_12_Cavalier_de_Bâton.jpg"),
        ("queen-of-wands", "b_13_Reine_de_Bâton.jpg"),
        ("king-of-wands", "b_14_Roi_de_Bâton.jpg"),
    ]
    
    cups = [
        ("ace-of-cups", "c_01_As_de_Coupe.jpg"),
        ("two-of-cups", "c_02_Deux_de_Coupe.jpg"),
        ("three-of-cups", "c_03_Trois_de_Coupe.jpg"),
        ("four-of-cups", "c_04_Quatre_de_Coupe.jpg"),
        ("five-of-cups", "c_05_Cinq_de_Coupe.jpg"),
        ("six-of-cups", "c_06_Six_de_Coupe.jpg"),
        ("seven-of-cups", "c_07_Sept_de_Coupe.jpg"),
        ("eight-of-cups", "c_08_Huit_de_Coupe.jpg"),
        ("nine-of-cups", "c_09_Neuf_de_Coupe.jpg"),
        ("ten-of-cups", "c_10_Dix_de_Coupe.jpg"),
        ("page-of-cups", "c_11_Valet_de_Coupe.jpg"),
        ("knight-of-cups", "c_12_Cavalier_de_Coupe.jpg"),
        ("queen-of-cups", "c_13_Reine_de_Coupe.jpg"),
        ("king-of-cups", "c_14_Roi_de_Coupe.jpg"),
    ]
    
    pentacles = [
        ("ace-of-pentacles", "d_01_As_de_Denier.jpg"),
        ("two-of-pentacles", "d_02_Deux_de_Denier.jpg"),
        ("three-of-pentacles", "d_03_Trois_de_Denier.jpg"),
        ("four-of-pentacles", "d_04_Quatre_de_Denier.jpg"),
        ("five-of-pentacles", "d_05_Cinq_de_Denier.jpg"),
        ("six-of-pentacles", "d_06_Six_de_Denier.jpg"),
        ("seven-of-pentacles", "d_07_Sept_de_Denier.jpg"),
        ("eight-of-pentacles", "d_08_Huit_de_Denier.jpg"),
        ("nine-of-pentacles", "d_09_Neuf_de_Denier.jpg"),
        ("ten-of-pentacles", "d_10_Dix_de_Denier.jpg"),
        ("page-of-pentacles", "d_11_Valet_de_Denier.jpg"),
        ("knight-of-pentacles", "d_12_Cavalier_de_Denier.jpg"),
        ("queen-of-pentacles", "d_13_Reine_de_Denier.jpg"),
        ("king-of-pentacles", "d_14_Roi_de_Denier.jpg"),
    ]
    
    swords = [
        ("ace-of-swords", "e_01_As_de_Déepe.jpg"),
        ("two-of-swords", "e_02_Deux_de_Déepe.jpg"),
        ("three-of-swords", "e_03_Trois_de_Déepe.jpg"),
        ("four-of-swords", "e_04_Quatre_de_Déepe.jpg"),
        ("five-of-swords", "e_05_Cinq_de_Déepe.jpg"),
        ("six-of-swords", "e_06_Six_de_Déepe.jpg"),
        ("seven-of-swords", "e_07_Sept_de_Déepe.jpg"),
        ("eight-of-swords", "e_08_Huit_de_Déepe.jpg"),
        ("nine-of-swords", "e_09_Neuf_de_Déepe.jpg"),
        ("ten-of-swords", "e_10_Dix_de_Déepe.jpg"),
        ("page-of-swords", "e_11_Valet_de_Déepe.jpg"),
        ("knight-of-swords", "e_12_Cavalier_de_Déepe.jpg"),
        ("queen-of-swords", "e_13_Reine_de_Déepe.jpg"),
        ("king-of-swords", "e_14_Roi_de_Déepe.jpg"),
    ]
    
    all_cards = major_arcana + wands + cups + pentacles + swords
    
    success_count = 0
    failed_cards = []
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    
    for card_name_slug, filename in all_cards:
        try:
            # Construct the page URL
            page_url = f"https://www.thetarotlady.com/tarot-card-by-card-{card_name_slug}/"
            
            print(f"Fetching: {page_url}")
            
            response = requests.get(page_url, headers=headers, timeout=10)
            
            if response.status_code == 200:
                # Try to find the image URL using multiple patterns
                image_url = None
                
                # Pattern 1: og:image meta tag
                og_pattern = r'<meta property="og:image" content="([^"]+)"'
                match = re.search(og_pattern, response.text)
                if match:
                    image_url = match.group(1)
                    print(f"  Found og:image: {image_url}")
                
                # Pattern 2: img tags with tarot card in alt or src
                if not image_url:
                    img_pattern = r'<img[^>]+src="([^"]+)"[^>]*>'
                    matches = re.findall(img_pattern, response.text)
                    for img_src in matches:
                        if 'tarot' in img_src.lower() or 'card' in img_src.lower():
                            image_url = img_src
                            print(f"  Found card image: {image_url}")
                            break
                
                # Pattern 3: wp-content/uploads images
                if not image_url:
                    upload_pattern = r'https://www\.thetarotlady\.com/wp-content/uploads/[^"]+\.jpg'
                    match = re.search(upload_pattern, response.text)
                    if match:
                        image_url = match.group(1)
                        print(f"  Found upload image: {image_url}")
                
                if image_url:
                    # Download the image
                    print(f"  Downloading: {image_url}")
                    img_response = requests.get(image_url, headers=headers, timeout=10)
                    
                    if img_response.status_code == 200 and len(img_response.content) > 1000:
                        file_path = os.path.join(output_dir, filename)
                        with open(file_path, 'wb') as f:
                            f.write(img_response.content)
                        print(f"✓ Downloaded: {filename} ({len(img_response.content)} bytes)")
                        success_count += 1
                    else:
                        failed_cards.append((filename, card_name_slug, f"Image download failed: {img_response.status_code}"))
                        print(f"✗ Image download failed for {filename}")
                else:
                    failed_cards.append((filename, card_name_slug, "No image URL found"))
                    print(f"✗ No image URL found for {filename}")
            else:
                failed_cards.append((filename, card_name_slug, f"Page failed: {response.status_code}"))
                print(f"✗ Page failed for {filename}: {response.status_code}")
                    
        except Exception as e:
            failed_cards.append((filename, card_name_slug, f"Error: {str(e)}"))
            print(f"✗ Error for {filename}: {str(e)}")
        
        # Add a small delay to avoid overwhelming the server
        import time
        time.sleep(1)
    
    print(f"\n{'='*50}")
    print(f"Download complete: {success_count}/{len(all_cards)} cards downloaded")
    
    if failed_cards:
        print(f"\n{len(failed_cards)} cards failed to download:")
        for filename, slug, reason in failed_cards:
            print(f"  - {filename} ({slug}): {reason}")

if __name__ == "__main__":
    download_tanot_cards()