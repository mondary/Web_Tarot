"""Desktop hover, cross artwork and dedicated name-entry screen regressions.

Run with the V9 PHP server at TAROT_URL (default http://127.0.0.1:8772/).
"""
import os
from io import BytesIO
from pathlib import Path
from PIL import Image, ImageStat
from playwright.sync_api import sync_playwright, expect

URL = os.environ.get('TAROT_URL', 'http://127.0.0.1:8772/')
CHROME = os.environ.get('CHROME_PATH')
local_chrome = Path.home() / 'Library/Caches/ms-playwright/chromium-1228/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'
if not CHROME and local_chrome.exists():
    CHROME = str(local_chrome)


def menu(page, mobile):
    button = page.get_by_role('button', name='Tirages', exact=True)
    button.tap() if mobile else button.click()


def artwork(page, mobile):
    for i in range(5):
        card = page.locator(f'#sp-spread .sp-layout-cross .sp-card[data-idx="{i}"]')
        card.tap() if mobile else card.click()
        expect(card).to_contain_class('revealed')
        image = card.locator('img')
        expect(image).to_be_visible()
        expect(image).to_have_js_property('complete', True)
        assert image.evaluate('img=>img.naturalWidth') > 0
        assert card.locator('.sp-card-front').evaluate('el=>getComputedStyle(el).visibility') == 'visible'
        page.wait_for_timeout(350)
        im = Image.open(BytesIO(card.screenshot())).convert('RGB')
        brightness = sum(ImageStat.Stat(im).mean) / 3
        assert brightness > 70, f'black revealed cross card at {i}: {brightness:.1f}'


with sync_playwright() as p:
    browser = p.chromium.launch(**({'executable_path': CHROME} if CHROME else {}))
    for mobile in (False, True):
        ctx = browser.new_context(viewport={'width': 390 if mobile else 1440, 'height': 844 if mobile else 900}, is_mobile=mobile, has_touch=mobile)
        page = ctx.new_page()
        errors = []
        page.on('pageerror', lambda e: errors.append(str(e)))
        page.goto(URL)
        # The name entry must not be embedded in the spreads list.
        menu(page, mobile)
        assert page.locator('#sp-menu #sp-name-input').count() == 0
        entry = page.locator('#sp-menu-list .sp-item').last
        entry.tap() if mobile else entry.click()
        expect(page.locator('#sp-name-screen')).to_be_visible()
        expect(page.locator('#sp-menu')).to_be_hidden()
        assert page.evaluate('document.activeElement?.id') == 'sp-name-input'
        page.keyboard.press('Escape')
        expect(page.locator('#sp-name-screen')).to_be_hidden()
        expect(page.locator('#sp-menu')).to_be_visible()
        if mobile:
            entry.tap()
            page.locator('#sp-name-back').tap()
        else:
            entry.click()
            page.locator('#sp-name-back').click()
        expect(page.locator('#sp-menu')).to_be_visible()
        entry.tap() if mobile else entry.click()
        capture = os.environ.get('CAPTURE_DIR')
        if capture and not mobile:
            Path(capture).mkdir(parents=True, exist_ok=True)
            page.locator('#sp-name-screen').screenshot(path=str(Path(capture) / '06-tirage-prenom.png'))
        page.locator('#sp-name-input').fill('  ')
        page.locator('#sp-name-form').press('Enter')
        expect(page.locator('#sp-name-screen')).to_be_visible()
        expect(page.locator('#sp-name-error')).to_contain_text('au moins deux lettres')
        expect(page.locator('#sp-name-input')).to_have_attribute('aria-invalid', 'true')
        page.locator('#sp-name-input').fill(' ALICE ')
        page.locator('#sp-name-form').press('Enter')
        expect(page.locator('#sp-name-screen')).to_be_hidden()
        expect(page.locator('#sp-cut')).to_be_visible()
        page.locator('#sp-cut-cancel').tap() if mobile else page.locator('#sp-cut-cancel').click()
        # After cancelling the cut, start a cross in quick mode to test all five artworks.
        page.evaluate("TarotSpreads.setDrawMode('quick')")
        menu(page, mobile)
        cross = page.locator('#sp-menu-list .sp-item').nth(2)
        cross.tap() if mobile else cross.click()
        artwork(page, mobile)
        if capture and not mobile:
            page.locator('#sp-spread').screenshot(path=str(Path(capture) / '07-croix-revelee.png'))
        assert not errors, errors
        print('PASS', 'mobile' if mobile else 'desktop', 'dedicated name screen/back/Escape, five revealed cross images')
        ctx.close()

    page = browser.new_page(viewport={'width': 1440, 'height': 900})
    page.goto(URL)
    menu(page, False)
    page.locator('#sp-menu-list .sp-item').nth(2).click()
    page.locator('#sp-cut-go').click()
    expect(page.locator('#sp-fan')).to_be_visible()
    page.mouse.move(710, 100)
    page.wait_for_timeout(250)
    assert page.locator('#sp-fan .lift').count() == 0
    page.mouse.move(710, 340)
    page.wait_for_timeout(250)
    lift = page.locator('#sp-fan .lift')
    expect(lift).to_have_count(1)
    idx = lift.get_attribute('data-i')
    raised = lift.bounding_box()['y']
    page.mouse.move(710, 100)
    page.wait_for_timeout(250)
    rest = page.locator(f'#sp-fan .sp-fan-card[data-i="{idx}"]').bounding_box()['y']
    assert 12 <= rest - raised <= 20, (rest, raised)
    page.mouse.move(710, 340)
    page.wait_for_timeout(250)
    expect(page.locator('#sp-fan .lift')).to_have_count(1)
    assert page.locator('#sp-fan .lift').get_attribute('data-i') == idx
    capture = os.environ.get('CAPTURE_DIR')
    if capture:
        page.locator('#sp-fan').screenshot(path=str(Path(capture) / '04-tirage-eventail.png'))
    print('PASS desktop: actual mouse hover lifts one card ~16px, clears off deck, stable target')
    browser.close()
