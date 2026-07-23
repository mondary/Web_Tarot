/* organize-decks.js — Organise les images sources par deck dans benchmarks/decks/.
   Source : website/cards/{prefix}_{num}_{slug}{_suffix}.{ext}
   Destination : benchmarks/decks/{deck}/{prefix}_{num}_{slug}.{ext}
   Decks : marseille, rider_waite (image principale), wiki, fr */
const fs = require('fs');
const path = require('path');

const CARDS = path.join(__dirname, '..', 'website', 'cards');
const DEST  = path.join(__dirname, '..', 'benchmarks', 'decks');

const DECKS = {
  marseille:   '_marseille',
  rider_waite: '',          // image principale (pas de suffixe)
  wiki:        '_wiki',
  fr:          '_fr',
};

Object.keys(DECKS).forEach(d => fs.mkdirSync(path.join(DEST, d), { recursive: true }));

// Index canonique : prefix_num -> slug
const canon = {};
fs.readdirSync(CARDS).forEach(f => {
  const m = f.match(/^([abced]_\d{2})_(.+)\.jpg$/);
  if (m && !/_wiki|_fr|_marseille/.test(f)) canon[m[1]] = m[2];
});

let stats = {};
Object.keys(DECKS).forEach(d => stats[d] = 0);

Object.entries(canon).forEach(([key, slug]) => {
  Object.entries(DECKS).forEach(([deck, suffix]) => {
    // Cherche .jpg puis .png (le _fr peut être en .png)
    const candidates = suffix === ''
      ? [key + '_' + slug + '.jpg']
      : [key + '_' + slug + suffix + '.jpg', key + '_' + slug + suffix + '.png'];
    for (const name of candidates) {
      const src = path.join(CARDS, name);
      if (fs.existsSync(src)) {
        const ext = path.extname(name);
        fs.copyFileSync(src, path.join(DEST, deck, key + '_' + slug + ext));
        stats[deck]++;
        break;
      }
    }
  });
});

console.log('Organisation benchmarks/decks/ :');
Object.entries(stats).forEach(([d, n]) => console.log(`  ${d}/ : ${n} images`));
