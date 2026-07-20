#!/usr/bin/env python3
"""
Script to scrape tarot card affirmations from thetarotlady.com and create English affirmation files.
"""

import requests
from bs4 import BeautifulSoup
import re
import time
import os

# Target directory
OUTPUT_DIR = "/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website/affirm"

# Complete mapping of all 78 tarot cards
# Format: (number, french_name, english_name, url_slug, file_prefix)
TAROT_CARDS = [
    # Major Arcana (0-21)
    (0, "Fou", "The Fool", "the-fool", "a_00_Fou"),
    (1, "Magicien", "The Magician", "the-magician", "a_01_Magicien"),
    (2, "Papesse", "The High Priestess", "the-high-priestess", "a_02_Papesse"),
    (3, "Impératrice", "The Empress", "the-empress", "a_03_Impératrice"),
    (4, "Empereur", "The Emperor", "the-emperor", "a_04_Empereur"),
    (5, "Pape", "The Hierophant", "the-hierophant", "a_05_Pape"),
    (6, "Amoureux", "The Lovers", "the-lovers", "a_06_Amoureux"),
    (7, "Chariot", "The Chariot", "the-chariot", "a_07_Chariot"),
    (8, "Force", "Strength", "strength", "a_08_Force"),
    (9, "Ermite", "The Hermit", "the-hermit", "a_09_Ermite"),
    (10, "Roue de Fortune", "Wheel of Fortune", "wheel-of-fortune", "a_10_Roue_de_Fortune"),
    (11, "Justice", "Justice", "justice", "a_11_Justice"),
    (12, "Pendu", "The Hanged Man", "the-hanged-man", "a_12_Pendu"),
    (13, "Mort", "Death", "death", "a_13_Mort"),
    (14, "Tempérance", "Temperance", "temperance", "a_14_Tempérance"),
    (15, "Diable", "The Devil", "the-devil", "a_15_Diable"),
    (16, "Tour", "The Tower", "the-tower", "a_16_Tour"),
    (17, "Étoile", "The Star", "the-star", "a_17_Étoile"),
    (18, "Lune", "The Moon", "the-moon", "a_18_Lune"),
    (19, "Soleil", "The Sun", "the-sun", "a_19_Soleil"),
    (20, "Jugement", "Judgment", "judgment", "a_20_Jugement"),
    (21, "Monde", "The World", "the-world", "a_21_Monde"),
    
    # Minor Arcana - Wands (Bâtons) (22-36)
    (22, "As de Bâtons", "Ace of Wands", "ace-of-wands", "a_22_As_de_Bâtons"),
    (23, "Deux de Bâtons", "Two of Wands", "two-of-wands", "a_23_Deux_de_Bâtons"),
    (24, "Trois de Bâtons", "Three of Wands", "three-of-wands", "a_24_Trois_de_Bâtons"),
    (25, "Quatre de Bâtons", "Four of Wands", "four-of-wands", "a_25_Quatre_de_Bâtons"),
    (26, "Cinq de Bâtons", "Five of Wands", "five-of-wands", "a_26_Cinq_de_Bâtons"),
    (27, "Six de Bâtons", "Six of Wands", "six-of-wands", "a_27_Six_de_Bâtons"),
    (28, "Sept de Bâtons", "Seven of Wands", "seven-of-wands", "a_28_Sept_de_Bâtons"),
    (29, "Huit de Bâtons", "Eight of Wands", "eight-of-wands", "a_29_Huit_de_Bâtons"),
    (30, "Neuf de Bâtons", "Nine of Wands", "nine-of-wands", "a_30_Neuf_de_Bâtons"),
    (31, "Dix de Bâtons", "Ten of Wands", "ten-of-wands", "a_31_Dix_de_Bâtons"),
    (32, "Valet de Bâtons", "Page of Wands", "page-of-wands", "a_32_Valet_de_Bâtons"),
    (33, "Cavalier de Bâtons", "Knight of Wands", "knight-of-wands", "a_33_Cavalier_de_Bâtons"),
    (34, "Reine de Bâtons", "Queen of Wands", "queen-of-wands", "a_34_Reine_de_Bâtons"),
    (35, "Roi de Bâtons", "King of Wands", "king-of-wands", "a_35_Roi_de_Bâtons"),
    
    # Minor Arcana - Cups (Coupes) (36-50)
    (36, "As de Coupes", "Ace of Cups", "ace-of-cups", "a_36_As_de_Coupes"),
    (37, "Deux de Coupes", "Two of Cups", "two-of-cups", "a_37_Deux_de_Coupes"),
    (38, "Trois de Coupes", "Three of Cups", "three-of-cups", "a_38_Trois_de_Coupes"),
    (39, "Quatre de Coupes", "Four of Cups", "four-of-cups", "a_39_Quatre_de_Coupes"),
    (40, "Cinq de Coupes", "Five of Cups", "five-of-cups", "a_40_Cinq_de_Coupes"),
    (41, "Six de Coupes", "Six of Cups", "six-of-cups", "a_41_Six_de_Coupes"),
    (42, "Sept de Coupes", "Seven of Cups", "seven-of-cups", "a_42_Sept_de_Coupes"),
    (43, "Huit de Coupes", "Eight of Cups", "eight-of-cups", "a_43_Huit_de_Coupes"),
    (44, "Neuf de Coupes", "Nine of Cups", "nine-of-cups", "a_44_Neuf_de_Coupes"),
    (45, "Dix de Coupes", "Ten of Cups", "ten-of-cups", "a_45_Dix_de_Coupes"),
    (46, "Valet de Coupes", "Page of Cups", "page-of-cups", "a_46_Valet_de_Coupes"),
    (47, "Cavalier de Coupes", "Knight of Cups", "knight-of-cups", "a_47_Cavalier_de_Coupes"),
    (48, "Reine de Coupes", "Queen of Cups", "queen-of-cups", "a_48_Reine_de_Coupes"),
    (49, "Roi de Coupes", "King of Cups", "king-of-cups", "a_49_Roi_de_Coupes"),
    
    # Minor Arcana - Swords (Épées) (50-64)
    (50, "As d'Épées", "Ace of Swords", "ace-of-swords", "a_50_As_d'Épées"),
    (51, "Deux d'Épées", "Two of Swords", "two-of-swords", "a_51_Deux_d'Épées"),
    (52, "Trois d'Épées", "Three of Swords", "three-of-swords", "a_52_Trois_d'Épées"),
    (53, "Quatre d'Épées", "Four of Swords", "four-of-swords", "a_53_Quatre_d'Épées"),
    (54, "Cinq d'Épées", "Five of Swords", "five-of-swords", "a_54_Cinq_d'Épées"),
    (55, "Six d'Épées", "Six of Swords", "six-of-swords", "a_55_Six_d'Épées"),
    (56, "Sept d'Épées", "Seven of Swords", "seven-of-swords", "a_56_Sept_d'Épées"),
    (57, "Huit d'Épées", "Eight of Swords", "eight-of-swords", "a_57_Huit_d'Épées"),
    (58, "Neuf d'Épées", "Nine of Swords", "nine-of-swords", "a_58_Neuf_d'Épées"),
    (59, "Dix d'Épées", "Ten of Swords", "ten-of-swords", "a_59_Dix_d'Épées"),
    (60, "Valet d'Épées", "Page of Swords", "page-of-swords", "a_60_Valet_d'Épées"),
    (61, "Cavalier d'Épées", "Knight of Swords", "knight-of-swords", "a_61_Cavalier_d'Épées"),
    (62, "Reine d'Épées", "Queen of Swords", "queen-of-swords", "a_62_Reine_d'Épées"),
    (63, "Roi d'Épées", "King of Swords", "king-of-swords", "a_63_Roi_d'Épées"),
    
    # Minor Arcana - Pentacles (Deniers) (64-78)
    (64, "As de Deniers", "Ace of Pentacles", "ace-of-pentacles", "a_64_As_de_Deniers"),
    (65, "Deux de Deniers", "Two of Pentacles", "two-of-pentacles", "a_65_Deux_de_Deniers"),
    (66, "Trois de Deniers", "Three of Pentacles", "three-of-pentacles", "a_66_Trois_de_Deniers"),
    (67, "Quatre de Deniers", "Four of Pentacles", "four-of-pentacles", "a_67_Quatre_de_Deniers"),
    (68, "Cinq de Deniers", "Five of Pentacles", "five-of-pentacles", "a_68_Cinq_de_Deniers"),
    (69, "Six de Deniers", "Six of Pentacles", "six-of-pentacles", "a_69_Six_de_Deniers"),
    (70, "Sept de Deniers", "Seven of Pentacles", "seven-of-pentacles", "a_70_Sept_de_Deniers"),
    (71, "Huit de Deniers", "Eight of Pentacles", "eight-of-pentacles", "a_71_Huit_de_Deniers"),
    (72, "Neuf de Deniers", "Nine of Pentacles", "nine-of-pentacles", "a_72_Neuf_de_Deniers"),
    (73, "Dix de Deniers", "Ten of Pentacles", "ten-of-pentacles", "a_73_Dix_de_Deniers"),
    (74, "Valet de Deniers", "Page of Pentacles", "page-of-pentacles", "a_74_Valet_de_Deniers"),
    (75, "Cavalier de Deniers", "Knight of Pentacles", "knight-of-pentacles", "a_75_Cavalier_de_Deniers"),
    (76, "Reine de Deniers", "Queen of Pentacles", "queen-of-pentacles", "a_76_Reine_de_Deniers"),
    (77, "Roi de Deniers", "King of Pentacles", "king-of-pentacles", "a_77_Roi_de_Deniers"),
]

def extract_affirmation_from_html(html_content):
    """Extract affirmation from the HTML content."""
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # Look for the affirmation pattern with both straight and curly quotes
    # Unicode curly quotes: "" (U+201C) and "" (U+201D)
    affirmation_patterns = [
        r'<p><strong>Affirmation:</strong>\s*"([^"]+)"</p>',  # curly quotes
        r'<p><strong>Affirmation:</strong>\s*"([^"]+)"</p>',  # straight quotes
        r'<p><strong>Affirmation:</strong>\s*«([^»]+)»</p>',  # french quotes
        r'<strong>Affirmation:</strong>\s*"([^"]+)"',  # curly quotes
        r'<strong>Affirmation:</strong>\s*"([^"]+)"',  # straight quotes
        r'Affirmation:\s*"([^"]+)"',  # curly quotes in text
        r'Affirmation:\s*"([^"]+)"',  # straight quotes in text
    ]
    
    for pattern in affirmation_patterns:
        match = re.search(pattern, html_content, re.IGNORECASE)
        if match:
            return match.group(1).strip()
    
    # Try finding through BeautifulSoup
    p_tags = soup.find_all('p')
    for p in p_tags:
        text = p.get_text()
        if 'affirmation:' in text.lower():
            # Extract text between quotes (both curly and straight)
            # Try curly quotes first (U+201C and U+201D)
            match = re.search(r'"([^"]+)"', text)
            if match:
                return match.group(1).strip()
            # Try straight quotes
            match = re.search(r'"([^"]+)"', text)
            if match:
                return match.group(1).strip()
    
    return None

def scrape_tarot_affirmation(card_number, french_name, english_name, url_slug, file_prefix):
    """Scrape affirmation for a single tarot card."""
    url = f"https://www.thetarotlady.com/tarot-card-by-card-{url_slug}/"
    
    print(f"Processing {english_name} ({card_number}): {url}")
    
    # Add headers to make request look more like a legitimate browser
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate, br',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1',
    }
    
    try:
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
        
        affirmation = extract_affirmation_from_html(response.text)
        
        if affirmation:
            # Create the markdown content
            content = f"# {card_number} — {english_name.upper()}\n\n"
            content += f"**Card Name:** {english_name.upper()}\n\n"
            content += f"**Affirmation:**\n\n"
            content += f'> "{affirmation}"\n'
            
            # Write to file
            filename = f"{file_prefix}_affirmation_ENG.md"
            filepath = os.path.join(OUTPUT_DIR, filename)
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            
            print(f"  ✓ Created: {filename}")
            print(f"    Affirmation: {affirmation}")
            return True
        else:
            print(f"  ✗ No affirmation found for {english_name}")
            return False
            
    except requests.RequestException as e:
        print(f"  ✗ Error fetching {url}: {e}")
        return False
    except Exception as e:
        print(f"  ✗ Error processing {english_name}: {e}")
        return False

def main():
    """Main function to scrape all tarot card affirmations."""
    print("Starting to scrape 78 tarot card affirmations from thetarotlady.com")
    print(f"Output directory: {OUTPUT_DIR}")
    print("-" * 80)
    
    success_count = 0
    fail_count = 0
    
    for card_data in TAROT_CARDS:
        card_number, french_name, english_name, url_slug, file_prefix = card_data
        
        if scrape_tarot_affirmation(card_number, french_name, english_name, url_slug, file_prefix):
            success_count += 1
        else:
            fail_count += 1
        
        # Be polite to the server - add a small delay
        time.sleep(1)
    
    print("-" * 80)
    print(f"Scraping complete!")
    print(f"Successfully processed: {success_count}/78 cards")
    print(f"Failed: {fail_count}/78 cards")

if __name__ == "__main__":
    main()