const puppeteer = require('puppeteer');
const path = require('path');
const OUT = path.join(__dirname, '..', 'store', 'screenshots-presentation');
const BASE = 'https://mondary.design/pk/tarot3/';

(async () => {
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox','--force-device-scale-factor=2'] });

  // Desktop shots
  const desktop = [
    { name: 'desk-grid.png',        url: BASE },
    { name: 'desk-mort.png',        url: BASE + 'card/a_13_Mort' },
    { name: 'desk-etoile.png',      url: BASE + 'card/a_17_Etoile' },
    { name: 'desk-bateleur.png',    url: BASE + 'card/a_01_Bateleur' },
    { name: 'desk-lune.png',        url: BASE + 'card/a_18_Lune' },
  ];

  for (const s of desktop) {
    try {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 2 });
      await page.goto(s.url, { waitUntil: 'networkidle0', timeout: 20000 });
      await new Promise(r => setTimeout(r, 2500));
      await page.screenshot({ path: path.join(OUT, s.name) });
      console.log('✓', s.name);
      await page.close();
    } catch(e) { console.log('✗', s.name); }
  }

  // Mobile shots
  const mobile = [
    { name: 'mob-grid.png',         url: BASE },
    { name: 'mob-mort.png',         url: BASE + 'card/a_13_Mort' },
    { name: 'mob-etoile.png',       url: BASE + 'card/a_17_Etoile' },
  ];

  for (const s of mobile) {
    try {
      const page = await browser.newPage();
      await page.emulate(puppeteer.devices['iPhone 15 Pro']);
      await page.goto(s.url, { waitUntil: 'networkidle0', timeout: 20000 });
      await new Promise(r => setTimeout(r, 2500));
      await page.screenshot({ path: path.join(OUT, s.name) });
      console.log('✓', s.name);
      await page.close();
    } catch(e) { console.log('✗', s.name); }
  }

  await browser.close();
  console.log('Done.');
})();
