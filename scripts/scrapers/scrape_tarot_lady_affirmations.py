#!/usr/bin/env python3
"""
Tarot Card Affirmation Scraper
Scrapes affirmations from thetarotlady.com for all 78 tarot cards
"""

import re
import time
from pathlib import Path

# Card definitions with URLs and file mappings
CARDS = [
    # Major Arcana
    {
        "file_prefix": "a_00_Fou",
        "card_name": "THE FOOL",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-fool/",
        "number": "0"
    },
    {
        "file_prefix": "a_01_Magicien", 
        "card_name": "THE MAGICIAN",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-magician/",
        "number": "1"
    },
    {
        "file_prefix": "a_02_Papesse",
        "card_name": "THE HIGH PRIESTESS", 
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-high-priestess/",
        "number": "2"
    },
    {
        "file_prefix": "a_03_Impératrice",
        "card_name": "THE EMPRESS",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-empress/",
        "number": "3"
    },
    {
        "file_prefix": "a_04_Empereur",
        "card_name": "THE EMPEROR",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-emperor/",
        "number": "4"
    },
    {
        "file_prefix": "a_05_Pape",
        "card_name": "THE HIEROPHANT",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-hierophant/",
        "number": "5"
    },
    {
        "file_prefix": "a_06_Amoureux",
        "card_name": "THE LOVERS",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-lovers/",
        "number": "6"
    },
    {
        "file_prefix": "a_07_Chariot",
        "card_name": "THE CHARIOT",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-chariot/",
        "number": "7"
    },
    {
        "file_prefix": "a_08_Force",
        "card_name": "STRENGTH",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-strength/",
        "number": "8"
    },
    {
        "file_prefix": "a_09_Ermite",
        "card_name": "THE HERMIT",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-hermit/",
        "number": "9"
    },
    {
        "file_prefix": "a_10_Roue_de_Fortune",
        "card_name": "WHEEL OF FORTUNE",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-wheel-of-fortune/",
        "number": "10"
    },
    {
        "file_prefix": "a_11_Justice",
        "card_name": "JUSTICE",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-justice/",
        "number": "11"
    },
    {
        "file_prefix": "a_12_Pendu",
        "card_name": "THE HANGED MAN",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-hanged-man/",
        "number": "12"
    },
    {
        "file_prefix": "a_13_Mort",
        "card_name": "DEATH",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-death/",
        "number": "13"
    },
    {
        "file_prefix": "a_14_Tempérance",
        "card_name": "TEMPERANCE",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-temperance/",
        "number": "14"
    },
    {
        "file_prefix": "a_15_Diable",
        "card_name": "THE DEVIL",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-devil/",
        "number": "15"
    },
    {
        "file_prefix": "a_16_Tour",
        "card_name": "THE TOWER",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-tower/",
        "number": "16"
    },
    {
        "file_prefix": "a_17_Étoile",
        "card_name": "THE STAR",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-star/",
        "number": "17"
    },
    {
        "file_prefix": "a_18_Lune",
        "card_name": "THE MOON",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-moon/",
        "number": "18"
    },
    {
        "file_prefix": "a_19_Soleil",
        "card_name": "THE SUN",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-sun/",
        "number": "19"
    },
    {
        "file_prefix": "a_20_Jugement",
        "card_name": "JUDGMENT",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-judgment/",
        "number": "20"
    },
    {
        "file_prefix": "a_21_Monde",
        "card_name": "THE WORLD",
        "url": "https://www.thetarotlady.com/tarot-card-by-card-the-world/",
        "number": "21"
    },
]

def extract_affirmation(text_content):
    """Extract affirmation from the text content"""
    # Look for affirmation pattern
    patterns = [
        r'Affirmation:\s*["\']?([^"\']+)["\']?',
        r'Affirmation\s*[:\-]\s*["\']?([^"\']+)["\']?',
        r'AFFIRMATION[:\s]*["\']?([^"\']+)["\']?',
    ]
    
    for pattern in patterns:
        match = re.search(pattern, text_content, re.IGNORECASE)
        if match:
            affirmation = match.group(1).strip()
            # Clean up the affirmation
            affirmation = affirmation.strip('"\'')
            return affirmation
    
    return None

def create_affirmation_file(card_data, affirmation):
    """Create the affirmation markdown file"""
    if not affirmation:
        return False
    
    content = f"# {card_data['number']} — {card_data['card_name']}\n\n"
    content += f"**Card Name:** {card_data['card_name']}\n\n"
    content += "**Affirmation:**\n\n"
    content += f"> \"{affirmation}\"\n"
    
    filename = f"/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website/affirm/{card_data['file_prefix']}_affirmation_ENG.md"
    
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    except Exception as e:
        print(f"Error creating file {filename}: {e}")
        return False

# Display the cards we'll be scraping
print("Tarot Card Affirmation Scraper")
print("=" * 40)
print(f"Total cards to scrape: {len(CARDS)}")
print("\nMajor Arcana Cards:")
for i, card in enumerate(CARDS[:22], 1):
    print(f"{i}. {card['card_name']}")

print("\nNote: This script requires the webfetch tool to scrape the website.")
print("Please run the scraping process using the available tools.")