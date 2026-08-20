#!/usr/bin/env python3
"""
Test script to find the exact affirmation format in the HTML.
"""

import requests
import re
from bs4 import BeautifulSoup

def find_affirmation_format():
    """Find the exact format of the affirmation in the HTML."""
    url = "https://www.thetarotlady.com/tarot-card-by-card-the-fool/"
    
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
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Find all paragraphs that might contain affirmation
        all_p_tags = soup.find_all('p')
        
        print("Searching for affirmation in all paragraphs:")
        print("=" * 80)
        
        for i, p in enumerate(all_p_tags):
            text = p.get_text()
            if 'affirmation' in text.lower():
                print(f"Paragraph {i}: {text}")
                print(f"HTML: {str(p)[:200]}")
                print("-" * 80)
                
                # Try to extract quote
                quotes = re.findall(r'"([^"]+)"', text)
                if quotes:
                    print(f"Found quotes: {quotes}")
                    print("-" * 80)
        
        # Also look for strong tags with "Affirmation"
        print("\nSearching for <strong>Affirmation</strong> tags:")
        print("=" * 80)
        strong_tags = soup.find_all('strong')
        for strong in strong_tags:
            if 'affirmation' in strong.get_text().lower():
                print(f"Found: {strong}")
                # Get the parent paragraph
                parent = strong.find_parent('p')
                if parent:
                    print(f"Parent paragraph: {parent.get_text()}")
                    print(f"Parent HTML: {str(parent)}")
                    
                    # Extract text between quotes
                    quotes = re.findall(r'"([^"]+)"', parent.get_text())
                    if quotes:
                        print(f"Extracted affirmation: {quotes[0]}")
                
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    find_affirmation_format()