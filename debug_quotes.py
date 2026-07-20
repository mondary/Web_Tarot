#!/usr/bin/env python3
"""
Debug script to find exact quote characters used.
"""

import requests
import re
from bs4 import BeautifulSoup

def debug_quotes():
    """Debug the exact quote characters used in the HTML."""
    url = "https://www.thetarotlady.com/tarot-card-by-card-the-fool/"
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate, br',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1',
    }
    
    response = requests.get(url, headers=headers, timeout=10)
    
    # Find the affirmation paragraph
    soup = BeautifulSoup(response.text, 'html.parser')
    
    for p in soup.find_all('p'):
        text = p.get_text()
        # Look specifically for "Affirmation:" with a colon
        if 'affirmation:' in text.lower():
            print("Found affirmation paragraph:")
            print(f"Text: {text}")
            print(f"HTML: {str(p)}")
            
            # Find the quote characters by showing character codes
            print("Character codes in text:")
            for i, char in enumerate(text):
                if ord(char) > 127:  # Non-ASCII characters
                    print(f"Position {i}: '{char}' (ord: {ord(char)}, hex: {hex(ord(char))})")
            
            # Try to extract with different patterns
            print("\nTrying different quote patterns:")
            
            # Pattern 1: straight quotes
            match = re.search(r'"([^"]+)"', text)
            if match:
                print(f"Straight quotes pattern worked: {match.group(1)}")
            else:
                print("Straight quotes pattern failed")
            
            # Pattern 2: specific Unicode curly quotes
            match = re.search(r'"([^"]+)"', text)
            if match:
                print(f"Curly quotes pattern worked: {match.group(1)}")
            else:
                print("Curly quotes pattern failed")
                
            # Pattern 3: any non-ASCII quotes
            match = re.search(r'["""]([^"""]+)["""]', text)
            if match:
                print(f"Any quote pattern worked: {match.group(1)}")
            else:
                print("Any quote pattern failed")
            
            # Pattern 4: match text between Affirmation: and end
            match = re.search(r'Affirmation:\s*["""]([^"""]+)["""]', text, re.IGNORECASE)
            if match:
                print(f"Affirmation pattern worked: {match.group(1)}")
            else:
                print("Affirmation pattern failed")
                
            break

if __name__ == "__main__":
    debug_quotes()