const { createRequire } = require('node:module');
const fs = require('node:fs');
const path = require('node:path');
const req = createRequire(require.resolve('@capacitor/assets'));
const sharp = req('sharp');

const rootIcon = path.resolve(__dirname, '../../icon.png');
const out = path.resolve(__dirname, '../store/assets');
const res = path.resolve(__dirname, '../app/src/main/res');
const densities = { ldpi: 36, mdpi: 48, hdpi: 72, xhdpi: 96, xxhdpi: 144, xxxhdpi: 192 };

(async () => {
  fs.mkdirSync(out, { recursive: true });
  await sharp(rootIcon).resize(512, 512, { fit: 'contain' }).png().toFile(path.join(out, 'icon-512.png'));

  const data = fs.readFileSync(rootIcon).toString('base64');
  const feature = `<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="500">
    <rect width="1024" height="500" fill="#0a0907"/>
    <circle cx="815" cy="250" r="190" fill="none" stroke="#2a2620"/>
    <text x="65" y="94" fill="#c9a227" font-family="sans-serif" font-size="16" letter-spacing="4">TAROT DIVINATOIRE</text>
    <text x="62" y="213" fill="#f1ede4" font-family="Georgia" font-size="58">Un instant pour vous.</text>
    <text x="65" y="284" fill="#c9a227" font-family="Georgia" font-style="italic" font-size="37">Une nouvelle perspective.</text>
    <text x="65" y="390" fill="#8a8174" font-family="sans-serif" font-size="20">78 cartes · 3 jeux · Tirages interactifs</text>
    <image x="695" y="95" width="240" height="240" href="data:image/png;base64,${data}"/>
  </svg>`;
  await sharp(Buffer.from(feature)).png().toFile(path.join(out, 'feature-1024x500.png'));

  for (const [density, size] of Object.entries(densities)) {
    const dir = path.join(res, `mipmap-${density}`);
    fs.mkdirSync(dir, { recursive: true });
    const source = () => sharp(rootIcon).resize(size, size, { fit: 'contain' }).png();
    await source().toFile(path.join(dir, 'ic_launcher.png'));
    await source().toFile(path.join(dir, 'ic_launcher_round.png'));

    // Android adaptive icons use a 108dp canvas. Keep the PK badge within the
    // launcher safe circle instead of cropping the original artwork.
    const canvas = Math.round(size * 2.25);
    const artwork = Math.round(canvas * 58 / 108);
    const inset = Math.floor((canvas - artwork) / 2);
    await sharp(rootIcon).resize(artwork, artwork, { fit: 'contain' })
      .extend({ top: inset, bottom: canvas - artwork - inset, left: inset, right: canvas - artwork - inset,
        background: { r: 0, g: 0, b: 0, alpha: 0 } })
      .png().toFile(path.join(dir, 'ic_launcher_foreground.png'));
    await sharp({ create: { width: canvas, height: canvas, channels: 4, background: '#0a0907' } })
      .png().toFile(path.join(dir, 'ic_launcher_background.png'));
  }
  console.log('Store and Android launcher icons generated from root icon.png.');
})().catch(error => { console.error(error); process.exitCode = 1; });
