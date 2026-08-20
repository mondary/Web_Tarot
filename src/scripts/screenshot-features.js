const puppeteer = require('puppeteer');
const path = require('path');
const OUT = path.join(__dirname, '..', 'store', 'screenshots-presentation');
const V1 = 'https://mondary.design/pk/tarot/';
const V3 = 'https://mondary.design/pk/tarot3/';

(async () => {
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });

  async function shoot(name, url, opts = {}) {
    try {
      const page = await browser.newPage();
      await page.setViewport({ width: opts.w || 900, height: opts.h || 560, deviceScaleFactor: 2 });
      if (opts.mobile) await page.emulate(puppeteer.devices['iPhone 15 Pro']);
      await page.goto(url, { waitUntil: 'networkidle0', timeout: 20000 });
      await new Promise(r => setTimeout(r, opts.delay || 2000));

      if (opts.click) { try { await page.click(opts.click); await new Promise(r=>setTimeout(r,1200)); } catch(e){} }
      if (opts.type) { try { await page.keyboard.type(opts.type); await new Promise(r=>setTimeout(r,800)); } catch(e){} }
      if (opts.key) { try { await page.keyboard.press(opts.key); await new Promise(r=>setTimeout(r,800)); } catch(e){} }

      await page.screenshot({ path: path.join(OUT, name) });
      console.log('✓', name);
      await page.close();
    } catch(e) { console.log('✗', name, e.message.slice(0,50)); }
  }

  // 4 views from V1/V2
  await shoot('feat-classique.png', V1 + 'index.html#grid');
  await shoot('feat-immersive.png', V1 + 'index_full.html#grid');
  await shoot('feat-detail.png', V1 + 'index_detail.html#grid');
  await shoot('feat-quick.png', V1 + 'index_quick.html#grid');

  // V3 card content (full scroll)
  await shoot('feat-content.png', V3 + 'card/a_08_Force', { h: 600 });

  // V3 search overlay
  await shoot('feat-search.png', V3, { key: 'KeyM' });

  // V1 draws overlay
  await shoot('feat-draws.png', V1 + 'index.html#grid', { click: '#draws-launch' });

  // V1 quick view with oui/non (open a card)
  await shoot('feat-oui-non.png', V1 + 'index_quick.html#lame=a_06_Amoureux', { h: 600 });

  // V1 scanner already exists as 06-scanner.png
  // V1 keyboard nav - just show grid with selected
  await shoot('feat-keyboard.png', V1 + 'index_classic.html#grid');

  await browser.close();
  console.log('Done.');
})();
