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
    page.get_by_role('button', name="Réglages d'affichage").tap()
    normal_size = page.evaluate('getComputedStyle(document.documentElement).fontSize')
    page.get_by_role('button', name='Grand', exact=True).tap()
    expect(page.get_by_role('button', name='Grand', exact=True)).to_have_attribute('aria-pressed', 'true')
    assert float(page.evaluate('parseFloat(getComputedStyle(document.documentElement).fontSize)')) > float(normal_size[:-2])
    assert page.evaluate("localStorage.getItem('tarotTextSize')") == 'large'
    page.reload()
    expect(page.locator('html')).to_have_attribute('data-text-size', 'large')
    page.get_by_role('button', name="Réglages d'affichage").tap()
    page.get_by_role('button', name='Petit', exact=True).tap()
    expect(page.locator('html')).to_have_attribute('data-text-size', 'small')
    page.get_by_role('button', name='Normal', exact=True).tap()
    assert page.locator('html').get_attribute('data-text-size') is None
    page.get_by_role('button', name='Grand', exact=True).tap()
    page.set_viewport_size({'width': 320, 'height': 568})
    panel = page.locator('#setPanel').bounding_box()
    assert panel and panel['x'] >= 0 and panel['x'] + panel['width'] <= 320, panel
    page.set_viewport_size({'width': 390, 'height': 844})
    page.get_by_role('button', name='Normal', exact=True).tap()
    page.keyboard.press('Escape')
    page.get_by_role('button', name='Tirages', exact=True).tap()
    expect(page.locator('#sp-menu')).to_be_visible()
    page.keyboard.press('Escape')
    assert page.locator('#iosInstallBtn').get_attribute('hidden') is not None
    page.evaluate('openDetail(0)')
    expect(page.locator('#assocsCount')).not_to_have_text('Associations')
    assert page.locator('#heroImg').evaluate('(i) => i.naturalWidth') > 0
    shared = page.evaluate('''async () => {
        let url = '';
        Object.defineProperty(navigator, 'share', { configurable: true, value: (data) => { url = data.url; return Promise.resolve(); } });
        shareCurrent();
        return url;
    }''')
    assert shared.startswith('https://mondary.design/pk/-Games-cards/tarot/?carte=a_00_Fou'), shared
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
    ios_ctx = browser.new_context(viewport={'width': 390, 'height': 844}, is_mobile=True, has_touch=True,
                                  user_agent='Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1')
    ios_page = ios_ctx.new_page()
    ios_page.goto(URL)
    ios_page.get_by_role('button', name="Réglages d'affichage").tap()
    expect(ios_page.locator('#iosInstallBtn')).to_be_visible()
    ios_page.locator('#iosInstallBtn').tap()
    expect(ios_page.locator('#iosInstallSteps')).to_be_visible()
    assert 'accueil' in ios_page.locator('#iosInstallSteps').inner_text()
    ios_ctx.close()
    ctx.close()
    browser.close()
print('PASS mobile: website design, persistent text sizes, iOS install hint, spreads, associations, alternate decks, offline assets')
