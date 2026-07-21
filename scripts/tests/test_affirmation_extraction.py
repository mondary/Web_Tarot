#!/usr/bin/env python3
"""
Test the updated extraction function on a few cards.
"""

import requests
import re
from bs4 import BeautifulSoup

def extract_affirmation_from_html(html_content):
    """Extract affirmation from the HTML content."""
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # Look for the affirmation pattern with both straight and curly quotes
    affirmation_patterns = [
        r'<p><strong>Affirmation:</strong>\s*"([^"]+)"</p>',  # curly quotes
        r'<p><strong>Affirmation:</strong>\s*"([^"]+)"</p>',  # straight quotes
        r'<p><strong>Affirmation:</strong>\s*«([^»]+)»</p>',  # french quotes
        r'<strong>Affirmation:</strong>\s*"([^"]+)"',  # curly quotes
        r'<strong>Affirmation:</strong>\s*"([^"]+)"',  # straight quotes
    ]
    
    for pattern in affirmation_patterns:
        match = re.search(pattern, html_content, re.IGNORECASE)
        if match:
            return match.group(1).strip()
    
    # Try finding through BeautifulSoup
    p_tags = soup.find_all('p')
    for p in p_tags:
        if 'affirmation' in p.get_text().lower():
            text = p.get_text()
            # Try curly quotes first
            match = re.search(r'"([^"]+)"', text)
            if match:
                return match.group(1).strip()
            # Try straight quotes
            match = re.search(r'"([^"]+)"', text)
            if match:
                return match.group(1).strip()
    
    return None

def test_cards():
    """Test extraction on a few different cards."""
    test_urls = [
        ("The Fool", "https://www.thetarotlady.com/tarot-card-by-card-the-fool/"),
        ("The Magician", "https://www.thetarotlady.com/tarot-card-by-card-the-magician/"),
        ("The High Priestess", "https://www.thetarotlady.com/tarot-card-by-card-the-high-priestess/"),
    ]
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate, br',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1',
    }
    
    for card_name, url in test_urls:
        print(f"Testing {card_name}:")
        try:
            response = requests.get(url, headers=headers, timeout=10)
            affirmation = extract_affirmation_from_html(response.text)
            
            if affirmation:
                print(f"  ✓ Affirmation: {affirmation}")
            else:
                print(f"  ✗ No affirmation found")
                
        except Exception as e:
            print(f"  ✗ Error: {e}")
        print()

if __name__ == "__main__":
    test_cards()