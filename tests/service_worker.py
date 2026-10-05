"""V9 service worker: fresh code online and explicit image fallback offline."""
import os
import re
from pathlib import Path
from playwright.sync_api import sync_playwright, expect

URL = os.environ.get('TAROT_URL', 'http://127.0.0.1:8772/')
CHROME = os.environ.get('CHROME_PATH')
local_chrome = Path.home() / 'Library/Caches/ms-playwright/chromium-1228/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'
if not CHROME and local_chrome.exists():
    CHROME = str(local_chrome)

source = (Path(__file__).resolve().parents[1] / 'src/website/sw.js').read_text()
CACHE = re.search(r"const CACHE = '([^']+)'", source).group(1)
VERSION = CACHE.removeprefix('tarot-v')

with sync_playwright() as p:
    browser = p.chromium.launch(**({'executable_path': CHROME} if CHROME else {}))
    context = browser.new_context(service_workers='allow')
    page = context.new_page()
    errors = []
    page.on('pageerror', lambda error: errors.append(str(error)))
    page.goto(URL + f'?cache-test={VERSION}')
    page.evaluate('navigator.serviceWorker.ready')
    page.reload()
    page.wait_for_function('!!navigator.serviceWorker.controller')
    assert VERSION in page.locator('.brand .v').inner_text()
    assert f'v={VERSION}' in page.locator('script[src*="js=spreads"]').get_attribute('src')
    page.evaluate('''async()=>{
      const cache = await caches.open(%r);
      await cache.put(location.href, new Response('STALE PAGE'));
      await cache.put(document.querySelector('script[src*="js=spreads"]').src,
        new Response('STALE SCRIPT'));
    }''' % CACHE)
    page.reload()
    assert VERSION in page.locator('.brand .v').inner_text()
    assert page.locator('#sp-name-screen').count() == 1
    for card in ('b_01_As', 'b_03_Trois', 'b_09_Neuf', 'b_11_Valet', 'c_04_Quatre', 'd_11_Valet'):
        artwork = page.evaluate('''async card=>{
          const response = await fetch(`index.php?img=${card}.jpg&v=1791106273`);
          return {status:response.status, type:response.headers.get('content-type'),
            size:(await response.arrayBuffer()).byteLength};
        }''', card)
        assert artwork['status'] == 200 and artwork['type'] == 'image/jpeg' and artwork['size'] > 1000, (card, artwork)
    context.set_offline(True)
    result = page.evaluate('''async()=>{
      const response = await fetch('index.php?img=b_11_Valet.jpg&offline-test=uncached');
      return {status:response.status, type:response.headers.get('content-type'),
        body:await response.text()};
    }''')
    assert result['status'] == 200
    assert result['type'].startswith('image/svg+xml')
    assert 'Image indisponible' in result['body']
    assert not errors, errors
    print('PASS service worker: versioned script, online artwork, offline image fallback, no rejected fetch')
    browser.close()
