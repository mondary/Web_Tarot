#!/usr/bin/env python3

import requests
from bs4 import BeautifulSoup
import time
import json
import re

# Card mappings from French to English
major_arcana = [
    ("a_00_Fou", "The Fool", "LE FOU / THE FOOL"),
    ("a_01_Magicien", "The Magician", "LE MAGICIEN / THE MAGICIAN"),
    ("a_02_Papesse", "The High Priestess", "LA PAPESSE / THE HIGH PRIESTESS"),
    ("a_03_Impératrice", "The Empress", "L'IMPÉRATRICE / THE EMPRESS"),
    ("a_04_Empereur", "The Emperor", "L'EMPEREUR / THE EMPEROR"),
    ("a_05_Pape", "The Hierophant", "LE PAPE / THE HIEROPHANT"),
    ("a_06_Amoureux", "The Lovers", "LES AMOUREUX / THE LOVERS"),
    ("a_07_Chariot", "The Chariot", "LE CHARIOT / THE CHARIOT"),
    ("a_08_Force", "Strength", "LA FORCE / STRENGTH"),
    ("a_09_Ermite", "The Hermit", "L'ERMITE / THE HERMIT"),
    ("a_10_Roue_de_Fortune", "Wheel of Fortune", "LA ROUE DE FORTUNE / WHEEL OF FORTUNE"),
    ("a_11_Justice", "Justice", "LA JUSTICE / JUSTICE"),
    ("a_12_Pendu", "The Hanged Man", "LE PENDU / THE HANGED MAN"),
    ("a_13_Mort", "Death", "LA MORT / DEATH"),
    ("a_14_Tempérance", "Temperance", "LA TEMPÉRANCE / TEMPERANCE"),
    ("a_15_Diable", "The Devil", "LE DIABLE / THE DEVIL"),
    ("a_16_Tour", "The Tower", "LA TOUR / THE TOWER"),
    ("a_17_Étoile", "The Star", "L'ÉTOILE / THE STAR"),
    ("a_18_Lune", "The Moon", "LA LUNE / THE MOON"),
    ("a_19_Soleil", "The Sun", "LE SOLEIL / THE SUN"),
    ("a_20_Jugement", "Judgment", "LE JUGEMENT / JUDGMENT"),
    ("a_21_Monde", "The World", "LE MONDE / THE WORLD"),
]

# Card number mappings for minor arcana
numbers = [
    ("01", "Ace", "As"),
    ("02", "Two", "Deux"),
    ("03", "Three", "Trois"),
    ("04", "Four", "Quatre"),
    ("05", "Five", "Cinq"),
    ("06", "Six", "Six"),
    ("07", "Seven", "Sept"),
    ("08", "Eight", "Huit"),
    ("09", "Nine", "Neuf"),
    ("10", "Ten", "Dix"),
    ("11", "Page", "Valet"),
    ("12", "Knight", "Cavalier"),
    ("13", "Queen", "Reine"),
    ("14", "King", "Roi"),
]

suits = [
    ("b", "Wands", "Bâton", "BÂTON"),
    ("c", "Cups", "Coupe", "COUPE"),
    ("d", "Pentacles", "Denier", "DENIER"),
    ("e", "Swords", "Épée", "ÉPÉE"),
]

def scrape_card_affirmation(card_name):
    """Scrape affirmation from a tarot card page"""
    # Convert card name to URL format
    url_name = card_name.lower().replace(" ", "-")
    url = f"https://www.thetarotlady.com/tarot-card-by-card-{url_name}/"
    
    try:
        response = requests.get(url, timeout=10)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Try to find affirmation section
        # Look for patterns like "Affirmation:" or the word "Affirmation" followed by text
        affirmation = None
        
        # Method 1: Look for the word "Affirmation" in the content
        text_content = soup.get_text()
        
        # Search for affirmation patterns
        patterns = [
            r'Affirmation:\s*["\']?([^"\']+)["\']?',
            r'Affirmation\s*[:\-]\s*["\']?([^"\']+)["\']?',
            r'AFFIRMATION[:\s]*["\']?([^"\']+)["\']?',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text_content, re.IGNORECASE)
            if match:
                affirmation = match.group(1).strip()
                break
        
        if not affirmation:
            # Method 2: Look for affirmation in specific HTML structure
            # Try to find the affirmation section in the page
            for element in soup.find_all(['p', 'h3', 'h4', 'strong']):
                if 'affirmation' in element.get_text().lower():
                    # Get the next sibling or parent text
                    parent = element.parent
                    if parent:
                        parent_text = parent.get_text()
                        # Extract text after "Affirmation:"
                        if ':' in parent_text:
                            parts = parent_text.split(':', 1)
                            if len(parts) > 1:
                                affirmation = parts[1].strip().strip('"\'')
                                break
        
        return affirmation
        
    except Exception as e:
        print(f"Error scraping {card_name}: {e}")
        return None

def main():
    all_cards = []
    
    # Add Major Arcana
    for file_prefix, english_name, title in major_arcana:
        all_cards.append({
            'file_prefix': file_prefix,
            'english_name': english_name,
            'title': title,
            'number': str(major_arcana.index((file_prefix, english_name, title)))
        })
    
    # Add Minor Arcana
    for suit_prefix, english_suit, french_suit, french_suit_upper in suits:
        for num_prefix, english_num, french_num in numbers:
            file_prefix = f"{suit_prefix}_{num_prefix}_de_{french_suit}"
            english_name = f"{english_num} of {english_suit}"
            title = f"{french_num.upper()} DE {french_suit_upper} / {english_num.upper()} OF {english_suit.upper()}"
            
            all_cards.append({
                'file_prefix': file_prefix,
                'english_name': english_name,
                'title': title,
                'number': num_prefix
            })
    
    print(f"Total cards to scrape: {len(all_cards)}")
    
    # Scrape each card
    results = []
    for i, card in enumerate(all_cards, 1):
        print(f"[{i}/{len(all_cards)}] Scraping: {card['english_name']}")
        
        affirmation = scrape_card_affirmation(card['english_name'])
        
        result = {
            'file_prefix': card['file_prefix'],
            'english_name': card['english_name'],
            'title': card['title'],
            'number': card['number'],
            'affirmation': affirmation
        }
        
        results.append(result)
        print(f"  Affirmation: {affirmation}")
        
        # Be respectful to the server
        time.sleep(1)
    
    # Save results to JSON
    with open('/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/affirmations.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    
    # Create markdown files
    for result in results:
        if result['affirmation']:
            content = f"# {result['number']} — {result['title']}\n\n"
            content += f"**Card Name:** {result['english_name']}\n\n"
            content += "**Affirmation:**\n\n"
            content += f"> \"{result['affirmation']}\"\n"
            
            filename = f"/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website/affirm/{result['file_prefix']}_affirmation_ENG.md"
            
            with open(filename, 'w', encoding='utf-8') as f:
                f.write(content)
            
            print(f"Created: {filename}")
        else:
            print(f"Skipping {result['english_name']} - no affirmation found")

if __name__ == "__main__":
    main()