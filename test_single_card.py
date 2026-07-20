#!/usr/bin/env python3
"""
Simple test script to check if we can access the tarot website with headers.
"""

import requests
import time
import re
from bs4 import BeautifulSoup

def test_single_card():
    """Test scraping a single card to see if the headers work."""
    url = "https://www.thetarotlady.com/tarot-card-by-card-the-fool/"
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate, br',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1',
    }
    
    print(f"Testing access to: {url}")
    
    try:
        response = requests.get(url, headers=headers, timeout=10)
        print(f"Status code: {response.status_code}")
        
        if response.status_code == 200:
            print("✓ Successfully accessed the page!")
            
            # Look for affirmation in the HTML
            affirmation_pattern = r'<p><strong>Affirmation:</strong>\s*"([^"]+)"</p>'
            match = re.search(affirmation_pattern, response.text, re.IGNORECASE)
            
            if match:
                affirmation = match.group(1).strip()
                print(f"✓ Found affirmation: {affirmation}")
            else:
                print("✗ Could not find affirmation pattern in the page")
                
                # Try a broader search
                if "affirmation" in response.text.lower():
                    print("✓ The word 'affirmation' is found in the page")
                    # Find the context around affirmation
                    soup = BeautifulSoup(response.text, 'html.parser')
                    p_tags = soup.find_all('p')
                    for i, p in enumerate(p_tags):
                        if 'affirmation' in p.get_text().lower():
                            print(f"Found affirmation paragraph {i}: {p.get_text()[:200]}")
                            break
                else:
                    print("✗ The word 'affirmation' is NOT found in the page")
                    
        else:
            print(f"✗ Failed with status code: {response.status_code}")
            
    except Exception as e:
        print(f"✗ Error: {e}")

if __name__ == "__main__":
    test_single_card()