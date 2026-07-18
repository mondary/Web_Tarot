const fs = require('fs');
const path = require('path');

const CARDS_DIR = path.join(__dirname, 'cards');

const FAMILY_META = {
  a: { key: 'majors', name: 'Arcanes Majeurs', short: 'Majeurs', element: 'Éther', accent: '#c9a227', entryNum: 0 },
  b: { key: 'batons', name: 'Bâtons', short: 'Bâtons', element: 'Feu', accent: '#c45a2e', entryNum: 1 },
  e: { key: 'epees', name: 'Épées', short: 'Épées', element: 'Air', accent: '#8fa3b5', entryNum: 3 },
  c: { key: 'coupes', name: 'Coupes', short: 'Coupes', element: 'Eau', accent: '#5b8fa3', entryNum: 2 },
  d: { key: 'deniers', name: 'Deniers', short: 'Deniers', element: 'Terre', accent: '#7a9b5e', entryNum: 4 },
};

const MAJOR_NAMES = {
  '00': 'Le Fou', '01': 'Le Bateleur', '02': 'La Papesse', '03': "L'Impératrice",
  '04': "L'Empereur", '05': 'Le Pape', '06': 'Les Amoureux', '07': 'Le Chariot',
  '08': 'La Force', '09': "L'Ermite", '10': 'La Roue de Fortune', '11': 'La Justice',
  '12': 'Le Pendu', '13': "L'Arcane Sans Nom", '14': 'Tempérance', '15': 'Le Diable',
  '16': 'La Maison Dieu', '17': "L'Étoile", '18': 'La Lune', '19': 'Le Soleil',
  '20': 'Le Jugement', '21': 'Le Monde',
};

const MINOR_NAMES = {
  '01': 'As', '02': 'Deux', '03': 'Trois', '04': 'Quatre', '05': 'Cinq',
  '06': 'Six', '07': 'Sept', '08': 'Huit', '09': 'Neuf', '10': 'Dix',
  '11': 'Valet', '12': 'Cavalier', '13': 'Reine', '14': 'Roi',
};

const ORDER = ['a', 'b', 'e', 'c', 'd'];

const families = {};
ORDER.forEach(prefix => {
  const meta = FAMILY_META[prefix];
  families[meta.key] = { ...meta, cards: [] };
});

const mdFiles = fs.readdirSync(CARDS_DIR).filter(f => f.endsWith('.md')).sort();

mdFiles.forEach(file => {
  const match = file.match(/^([abecd])_(\d{2})_(.+)\.md$/);
  if (!match) return;
  const [, prefix, num, slug] = match;
  const meta = FAMILY_META[prefix];
  const base = `${prefix}_${num}_${slug}`;
  const content = fs.readFileSync(path.join(CARDS_DIR, file), 'utf8');

  let name;
  if (prefix === 'a') {
    name = MAJOR_NAMES[num] || slug;
  } else {
    name = `${MINOR_NAMES[num] || slug} de ${meta.name}`;
  }

  families[meta.key].cards.push({
    id: base,
    file: base + '.jpg',
    name,
    family: meta.key,
    familyName: meta.name,
    element: meta.element,
    num: parseInt(num, 10),
    md: content,
  });
});

Object.values(families).forEach(f => f.cards.sort((a, b) => a.num - b.num));

const entryCards = ORDER.map(p => {
  const meta = FAMILY_META[p];
  return families[meta.key].cards[0].id;
});

const output = {
  families: ORDER.map(p => {
    const meta = FAMILY_META[p];
    const f = families[meta.key];
    return {
      key: f.key,
      name: f.name,
      short: f.short,
      element: f.element,
      accent: f.accent,
      count: f.cards.length,
      entryCard: f.cards[0].id,
      cards: f.cards,
    };
  }),
};

const json = JSON.stringify(output, null, 0);
const out = `/* Auto-généré par build_data.js — ne pas éditer manuellement */\nconst TAROT = ${json};\n`;
fs.writeFileSync(path.join(__dirname, 'data.js'), out);

let total = 0;
output.families.forEach(f => total += f.cards.length);
console.log(`data.js généré : ${output.families.length} familles, ${total} cartes.`);
