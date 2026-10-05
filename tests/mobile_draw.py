"""V9 touch regression. Run against the local PHP server with Python Playwright.

TAROT_URL and CHROME_PATH optionally override the server and browser executable.
Interactions use real hit-tested taps / CDP touch gestures, never DOM .click().
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


def open_draw(page, index=2):
    page.get_by_role('button', name='Tirages', exact=True).tap()
    page.locator('#sp-menu-list .sp-item').nth(index).tap()
    expect(page.locator('#sp-cut')).to_be_visible()


def in_view(page, locator):
    box = locator.bounding_box()
    vp = page.viewport_size
    assert box and box['x'] >= 0 and box['y'] >= 0, box
    assert box['x'] + box['width'] <= vp['width'] + 1, box
    assert box['y'] + box['height'] <= vp['height'] + 1, box


def swipe(page):
    box = page.locator('#sp-fan-scroll').bounding_box()
    x, y = box['width'] * .65, box['y'] + box['height'] * .7
    distance = min(200, box['height'] * .5)
    cdp = page.context.new_cdp_session(page)
    cdp.send('Input.dispatchTouchEvent', {'type': 'touchStart', 'touchPoints': [{'x': x, 'y': y}]})
    for i in range(1, 13):
        cdp.send('Input.dispatchTouchEvent', {'type': 'touchMove', 'touchPoints': [{'x': x, 'y': y-distance*i/12}]})
        page.wait_for_timeout(25)
    cdp.send('Input.dispatchTouchEvent', {'type': 'touchEnd', 'touchPoints': []})
    cdp.detach()


def tap_selected(page):
    box = page.locator('#sp-fan .selected').bounding_box()
    page.touchscreen.tap(box['x']+box['width']/2, box['y']+box['height']/2)


with sync_playwright() as p:
    browser = p.chromium.launch(**({'executable_path': CHROME} if CHROME else {}))
    for width, height in [(320, 568), (390, 844), (430, 932), (844, 390)]:
        ctx = browser.new_context(viewport={'width': width, 'height': height}, is_mobile=True, has_touch=True, device_scale_factor=2)
        page = ctx.new_page()
        errors = []
        page.on('pageerror', lambda e: errors.append(str(e)))
        page.goto(URL)
        open_draw(page)
        in_view(page, page.locator('#sp-cut-go'))
        in_view(page, page.locator('#sp-cut-cancel'))
        page.locator('#sp-cut-cancel').tap()
        expect(page.locator('#sp-cut')).to_be_hidden()
        open_draw(page)
        stack = page.locator('#sp-cut-stack').bounding_box()
        page.touchscreen.tap(stack['x']+stack['width']/2, stack['y']+stack['height']*.3)
        page.locator('#sp-cut-go').tap()
        expect(page.locator('#sp-fan')).to_be_visible()
        expect(page.locator('#sp-fan .selected')).to_have_count(1)
        page.wait_for_timeout(250)
        in_view(page, page.locator('#sp-fan .selected'))
        capture = os.environ.get('CAPTURE_DIR')
        if capture and width == 390:
            Path(capture).mkdir(parents=True, exist_ok=True)
            page.wait_for_timeout(350)
            page.locator('#sp-fan').screenshot(path=str(Path(capture) / '05-tirage-eventail-mobile.png'))
        assert page.locator('#sp-fan-pick').count() == 0
        assert page.locator('#sp-fan .selected').evaluate("el => el.style.getPropertyValue('--out')") == '16px'
        assert page.locator('#sp-fan .selected .sp-fan-back > svg').count() == 1
        assert not page.locator('#sp-fan .selected .sp-fan-edge').is_visible()
        before = page.locator('#sp-fan-count').inner_text()
        swipe(page)
        expect(page.locator('#sp-fan-count')).not_to_have_text(before)
        page.wait_for_timeout(700)
        assert page.locator('#sp-spread .sp-card').count() == 0, 'swiping must never deal'
        before = page.locator('#sp-fan-count').inner_text()
        box = page.locator('#sp-fan-scroll').bounding_box()
        page.mouse.move(width/2, box['y']+box['height']/2)
        page.mouse.wheel(0, 220)
        expect(page.locator('#sp-fan-count')).not_to_have_text(before)
        page.wait_for_timeout(350)
        # Every card, including the two endpoints, is reachable without precision gestures.
        page.locator('#sp-fan-scroll').focus()
        page.keyboard.press('Home')
        expect(page.locator('#sp-fan-prev')).to_be_disabled()
        page.keyboard.press('End')
        expect(page.locator('#sp-fan-next')).to_be_disabled()
        page.keyboard.press('ArrowUp')
        expect(page.locator('#sp-fan-next')).to_be_enabled()
        # A tap picks directly, unlike a swipe. Cancel during the deal animation.
        tap_selected(page)
        page.locator('#sp-fan-x').tap()
        page.wait_for_timeout(900)
        assert page.locator('#sp-spread .sp-card').count() == 0
        expect(page.locator('#sp-fan')).to_be_hidden()
        page.locator('.sp-slot-empty.next').tap()
        if width == 390:
            expect(page.locator('#sp-fan')).to_be_visible()
            page.set_viewport_size({'width': 844, 'height': 390})
            page.wait_for_timeout(300)
            in_view(page, page.locator('#sp-fan .selected'))
            assert page.locator('#sp-fan-pick').count() == 0
            page.set_viewport_size({'width': width, 'height': height})
            page.wait_for_timeout(300)
            in_view(page, page.locator('#sp-fan .selected'))
        for i in range(5):
            expect(page.locator('#sp-fan')).to_be_visible()
            expect(page.locator('#sp-fan-pos')).to_contain_text(f'{i+1} / 5')
            page.locator('#sp-fan-next').tap()
            tap_selected(page)
            tap_selected(page)
            expect(page.locator('#sp-spread .sp-card')).to_have_count(i+1)
        expect(page.locator('#sp-fan')).to_be_hidden()
        ids = page.locator('#sp-spread .sp-card').evaluate_all('(cards)=>cards.map(c=>c.dataset.id)')
        assert len(set(ids)) == 5, ids
        for i in range(5):
            card = page.locator(f'#sp-spread .sp-card[data-idx="{i}"]')
            card.tap()
            expect(page.locator('#sp-spread .sp-card.revealed')).to_have_count(i+1)
            expect(card.locator('img')).to_have_js_property('complete', True)
            assert card.locator('img').evaluate('img=>img.naturalWidth') > 0
            page.wait_for_timeout(300)
            brightness = sum(ImageStat.Stat(Image.open(BytesIO(card.screenshot())).convert('RGB')).mean) / 3
            assert brightness > 70, f'black revealed manual cross card {i}: {brightness:.1f}'
        page.locator('#sp-close-spread').tap()
        open_draw(page, 0)
        page.locator('#sp-cut-go').tap()
        expect(page.locator('#sp-fan')).to_be_visible()
        tap_selected(page)
        expect(page.locator('#sp-spread .sp-card.revealed')).to_have_count(1)
        daily = page.locator('#sp-spread .sp-card').get_attribute('data-id')
        page.locator('#sp-close-spread').tap()
        page.get_by_role('button', name='Tirages', exact=True).tap()
        page.locator('#sp-menu-list .sp-item').nth(0).tap()
        expect(page.locator('#sp-spread .sp-card')).to_have_attribute('data-id', daily)
        page.locator('#sp-close-spread').tap()
        page.get_by_role('button', name='Tirages', exact=True).tap()
        page.locator('#sp-order-toggle [data-o="free"]').tap()
        page.locator('#sp-menu-list .sp-item').nth(2).tap()
        page.locator('#sp-cut-go').tap()
        expect(page.locator('#sp-fan')).to_be_visible()
        page.locator('#sp-fan-x').tap()
        page.locator('.sp-slot-empty[data-idx="3"]').tap()
        if width == 390:
            page.locator('#sp-fan-scroll').focus()
            page.keyboard.press('Enter')
        else:
            tap_selected(page)
        expect(page.locator('#sp-spread .sp-card[data-idx="3"]')).to_have_count(1)
        page.wait_for_timeout(800)
        expect(page.locator('#sp-fan')).to_be_hidden()
        page.locator('#sp-spread .sp-card[data-idx="3"]').tap()
        expect(page.locator('#sp-spread .sp-card.revealed')).to_have_count(1)
        assert not errors, errors
        print(f'PASS {width}x{height}: cut/cancel, native swipe/wheel, endpoints, direct card tap, double tap, 5-card reveal, daily persistence, free order')
        ctx.close()
    browser.close()
