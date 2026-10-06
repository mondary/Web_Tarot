"""Offline mobile bundle must be the real website, not an alternative UI.

Run `php -n -S 127.0.0.1:8773 -t src/mobile/www` before this test.
Optionally set WEBSITE_URL to a running PHP copy for a pixel-level comparison.
"""
import os
from pathlib import Path
from io import BytesIO

from PIL import Image, ImageChops, ImageStat
from playwright.sync_api import sync_playwright, expect

ROOT = Path(__file__).resolve().parents[2]
WWW = ROOT / 'src/mobile/www'
URL = os.environ.get('APP_URL', 'http://127.0.0.1:8773/')
CHROME = os.environ.get('CHROME_PATH')
local_chrome = Path.home() / 'Library/Caches/ms-playwright/chromium-1228/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'
if not CHROME and local_chrome.exists():
    CHROME = str(local_chrome)

assert (WWW / 'tarot-spreads.js').read_bytes() == (ROOT / 'src/website/tarot-spreads.js').read_bytes()
assert (WWW / 'index.html').read_text().count('id="detail"') == 1
assert not (WWW / 'app.css').exists(), 'The old alternative mobile UI must not be bundled'
assert len(list((WWW / 'assocs').glob('*.json'))) == 78

with sync_playwright() as p:
    browser = p.chromium.launch(**({'executable_path': CHROME} if CHROME else {}))
    ctx = browser.new_context(viewport={'width': 390, 'height': 844}, is_mobile=True, has_touch=True)
    page = ctx.new_page()
    errors, bad = [], []
    page.on('pageerror', lambda e: errors.append(str(e)))
    page.on('response', lambda r: bad.append((r.status, r.url)) if r.status >= 400 else None)
    page.goto(URL)
    expect(page.get_by_role('button', name='Tirages', exact=True)).to_be_visible()
    expect(page.locator('#detail')).to_have_count(1)
    page.get_by_role('button', name='Tirages', exact=True).tap()
    expect(page.locator('#sp-menu')).to_be_visible()
    page.keyboard.press('Escape')
    page.evaluate('openDetail(0)')
    expect(page.locator('#assocsCount')).not_to_have_text('Associations')
    assert page.locator('#heroImg').evaluate('(i) => i.naturalWidth') > 0
    for deck in ('clm', 'marseille'):
        page.evaluate('(deck) => { DECK = deck; applyDeck() }', deck)
        expect(page.locator('#heroImg')).to_have_js_property('complete', True)
        assert page.locator('#heroImg').evaluate('(i) => i.naturalWidth') > 0, deck
    assert not errors, errors
    assert not bad, bad

    site = os.environ.get('WEBSITE_URL')
    if site:
        def screenshot(url):
            comparison = ctx.new_page()
            comparison.goto(url)
            comparison.evaluate('document.fonts.ready')
            comparison.wait_for_timeout(800)
            image = Image.open(BytesIO(comparison.screenshot())).convert('RGB')
            comparison.close()
            return image
        source, mobile = screenshot(site), screenshot(URL)
        difference = ImageStat.Stat(ImageChops.difference(source, mobile)).mean
        assert sum(difference) / 3 < 3, f'Website/mobile visual drift: {difference}'
    ctx.close()
    browser.close()
print('PASS mobile: production website design, spreads, associations, alternate decks, offline assets')
