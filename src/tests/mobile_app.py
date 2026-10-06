"""Mobile app regression: unified offline UI + real spreads engine.

Serves src/mobile/www statically and drives the app the way the Capacitor
WebView would: mobile viewport, touch taps, no network beyond the bundle.
"""
import os
from io import BytesIO
from pathlib import Path
from PIL import Image, ImageStat
from playwright.sync_api import sync_playwright, expect

ROOT = Path(__file__).resolve().parents[2]
WWW = ROOT / 'src/mobile/www'
PORT = int(os.environ.get('APP_PORT', '8773'))
CHROME = os.environ.get('CHROME_PATH')
local_chrome = Path.home() / 'Library/Caches/ms-playwright/chromium-1228/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'
if not CHROME and local_chrome.exists():
    CHROME = str(local_chrome)

with sync_playwright() as p:
    browser = p.chromium.launch(**({'executable_path': CHROME} if CHROME else {}))
    ctx = browser.new_context(viewport={'width': 390, 'height': 844}, is_mobile=True, has_touch=True)
    page = ctx.new_page()
    errors, csp = [], []
    page.on('pageerror', lambda e: errors.append(str(e)))
    page.on('console', lambda m: csp.append(m.text) if 'Content Security Policy' in m.text else None)
    page.goto(f'http://127.0.0.1:{PORT}/')
    # Library: 78 cards offline
    page.locator('nav.bottom [data-view="bibliotheque"]').tap()
    expect(page.locator('#grid .card')).to_have_count(78, timeout=15000)
    page.locator('#grid .card').first.tap()
    expect(page.locator('#detail[open]')).to_be_visible()
    page.locator('#close-detail').tap()
    # Daily ritual
    page.locator('nav.bottom [data-view="rituel"]').tap()
    page.locator('#daily').tap()
    expect(page.locator('#detail[open]')).to_be_visible()
    assert page.locator('#reading .reading-card img').first.evaluate('i=>i.naturalWidth') > 0
    page.locator('#close-detail').tap()
    # Real spreads engine: menu from the dedicated entry
    assert page.evaluate('!!window.TarotSpreads && !!window.TAROT')
    assert page.evaluate('window.TAROT.families.flatMap(f=>f.cards).length') == 78
    page.locator('#spread-start').tap()
    expect(page.locator('#sp-menu')).to_be_visible()
    # Quick cross: full flow inside the app shell
    page.evaluate("TarotSpreads.setDrawMode('quick')")
    page.locator('#sp-menu-list .sp-item').nth(2).tap()
    expect(page.locator('#sp-spread')).to_be_visible()
    page.locator('#sp-reveal-all').tap()
    expect(page.locator('#sp-spread .sp-card.revealed')).to_have_count(5)
    page.wait_for_timeout(400)
    for i in range(5):
        card = page.locator(f'#sp-spread .sp-card[data-idx="{i}"]')
        assert card.locator('img').evaluate('i=>i.naturalWidth') > 0
        brightness = sum(ImageStat.Stat(Image.open(BytesIO(card.screenshot())).convert('RGB')).mean) / 3
        assert brightness > 70, f'black revealed card {i}: {brightness:.1f}'
    page.locator('#sp-close-spread').tap()
    expect(page.locator('#sp-spread')).to_be_hidden()
    # Module FAB: above the bottom navigation, hidden on the rituel view
    page.locator('nav.bottom [data-view="bibliotheque"]').tap()
    fab = page.locator('#app-fab.sp-fab')
    expect(fab).to_be_visible()
    nav = page.locator('nav.bottom')
    assert fab.bounding_box()['y'] < nav.bounding_box()['y']
    fab.tap()
    expect(page.locator('#sp-menu')).to_be_visible()
    page.locator('#sp-menu-close').tap()
    page.locator('nav.bottom [data-view="rituel"]').tap()
    expect(page.locator('#app-fab')).to_be_hidden()
    # Journal still works after the module ran
    page.locator('nav.bottom [data-view="rituel"]').tap()
    page.locator('#daily').tap()
    page.locator('#note').fill('Test intégration')
    page.locator('#save').tap()
    expect(page.locator('#journal')).to_be_visible()
    assert page.locator('#entries .entry').count() == 1
    assert not errors, errors
    assert not csp, csp
    print('PASS app mobile unifiée: 78 cartes, rituel, moteur de tirages complet, FAB au-dessus de la nav, journal, zéro erreur/CSP')
    ctx.close()
    browser.close()
