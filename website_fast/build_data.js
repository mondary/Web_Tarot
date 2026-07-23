const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const CARDS_DIR = path.join(__dirname, 'cards');
const IMG_PREFIX = 'cards/';

// Empreinte monochrome compacte pour le scanner local (32x48 grayscale).
function scanReference(imgPath){
  try{
    const buf = execFileSync('magick', [imgPath, '-auto-orient', '-resize', '32x48!', '-colorspace', 'Gray', '-depth', '8', 'gray:-'], {maxBuffer: 1024*1024});
    return buf.toString('base64');
  }catch(e){ return null; }
}

const VS = '\uFE0E';
const FAMILY_META = {
  a: { key:'majors', name:'Arcanes Majeurs', short:'Majeurs', element:'Éther', elementSym:'✦'+VS,
       accent:'#c9a227', entryNum:0, signs:[],
       titleFull:'Arcanes Majeurs',
       elementLine:'les 22 arcanes du chemin initiatique',
       desc:"Les 22 arcanes majeurs racontent les grandes étapes et leçons de la vie. Ils parlent du destin, du cheminement spirituel et de l'accomplissement de soi." },
  b: { key:'batons', name:'Bâtons', short:'Bâtons', element:'Feu', elementSym:'🜂',
       accent:'#c45a2e', entryNum:1,
       signs:[{n:'Bélier',s:'♈'+VS},{n:'Lion',s:'♌'+VS},{n:'Sagittaire',s:'♐'+VS}],
       titleFull:'Les Bâtons',
       elementLine:"associés à l'élément Feu",
       desc:"Les Lames des Bâtons symbolisent ce qui motive et dynamise. Les Bâtons vous parlent de vos désirs et de votre élan vital." },
  e: { key:'epees', name:'Épées', short:'Épées', element:'Air', elementSym:'🜁',
       accent:'#8fa3b5', entryNum:3,
       signs:[{n:'Gémeaux',s:'♊'+VS},{n:'Balance',s:'♎'+VS},{n:'Verseau',s:'♒'+VS}],
       titleFull:'Les Épées',
       elementLine:"associées à l'élément Air",
       desc:"Les Lames des Épées symbolisent les idées et l'intellect. Les Épées évoquent votre esprit rationnel, la réflexion, le « Mental ». Elles sont aussi associées à la communication." },
  c: { key:'coupes', name:'Coupes', short:'Coupes', element:'Eau', elementSym:'🜄',
       accent:'#5b8fa3', entryNum:2,
       signs:[{n:'Cancer',s:'♋'+VS},{n:'Scorpion',s:'♏'+VS},{n:'Poissons',s:'♓'+VS}],
       titleFull:'Les Coupes',
       elementLine:"associées à l'élément Eau",
       desc:"Les Lames des Coupes symbolisent les émotions et les sentiments. Les Coupes vous parlent de ce que vous ressentez et de vos relations avec vos proches." },
  d: { key:'deniers', name:'Deniers', short:'Deniers', element:'Terre', elementSym:'🜃',
       accent:'#7a9b5e', entryNum:4,
       signs:[{n:'Taureau',s:'♉'+VS},{n:'Vierge',s:'♍'+VS},{n:'Capricorne',s:'♑'+VS}],
       titleFull:'Les Deniers',
       elementLine:"associés à l'élément Terre",
       desc:"Les Lames des Deniers évoquent le plan matériel de l'existence, notamment l'argent et les possessions matérielles. Elles représentent aussi tout ce qui a de la valeur pour vous : compétences, Énergie, santé…" },
};

const MAJOR_NAMES = {
  '00': 'Le Fou', '01': 'Le Bateleur', '02': 'La Papesse', '03': "L'Impératrice",
  '04': "L'Empereur", '05': 'Le Pape', '06': 'Les Amoureux', '07': 'Le Chariot',
  '08': 'La Force', '09': "L'Ermite", '10': 'La Roue de Fortune', '11': 'La Justice',
  '12': 'Le Pendu', '13': 'La Mort', '14': 'Tempérance', '15': 'Le Diable',
  '16': 'La Maison Dieu', '17': "L'Étoile", '18': 'La Lune', '19': 'Le Soleil',
  '20': 'Le Jugement', '21': 'Le Monde',
};

const MAJOR_NAMES_MARSEILLE = {
  ...MAJOR_NAMES,
  '06': "L'Amoureux",
  '09': "L'Hermite",
  '13': "L'Arcane sans nom",
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

const SOURCE_DIR = path.join(__dirname, '..', 'website_opti', 'cards');

const mdFiles = fs.readdirSync(SOURCE_DIR)
  .filter(f => f.endsWith('.md') && !f.endsWith('_symbols.md') && !f.endsWith('_symboles_pcd.md') && !f.endsWith('_associations.md') && !f.endsWith('_ES.md') && !f.endsWith('_affirmations.md'))
  .sort();

mdFiles.forEach(file => {
  const match = file.match(/^([abecd])_(\d{2})_(.+)\.md$/);
  if (!match) return;
  const [, prefix, num, slug] = match;
  const meta = FAMILY_META[prefix];
  const base = `${prefix}_${num}_${slug}`;
  const content = fs.readFileSync(path.join(SOURCE_DIR, file), 'utf8');

  let name;
  let nameM;
  if (prefix === 'a') {
    name = MAJOR_NAMES[num] || slug;
    nameM = MAJOR_NAMES_MARSEILLE[num] || name;
  } else {
    const suiteName = meta.name;
    const article = /^[AEIOUYÉÈÊÀ]/i.test(suiteName) ? 'd\'' : 'de ';
    name = `${MINOR_NAMES[num] || slug} ${article}${suiteName}`;
    nameM = name;
  }

  // Image paths (NO base64 — served as static files)
  const imgPath = IMG_PREFIX + base + '.jpg';
  const marseillePath = fs.existsSync(path.join(CARDS_DIR, base + '_marseille.jpg'))
    ? IMG_PREFIX + base + '_marseille.jpg' : null;

  // Données ES (réponse, affirmation, mots-clés) — en français
  let esData = null;
  const esFile = path.join(SOURCE_DIR, base + '_ES.md');
  try {
    const esContent = fs.readFileSync(esFile, 'utf8');
    const reponseMatch = esContent.match(/\*\*RÉPONSE\s*:\*\*\s*(.+)/i);
    const affirmationMatch = esContent.match(/\*\*Affirmation\s*:\*\*\s*>?\s*(.+)/i);
    const keywordsMatch = esContent.match(/\*\*Mots-clés\s*\(à l'endroit\)\s*:\*\*\s*\n([\s\S]*?)(?=\n\n|\n\*\*|$)/i);
    let espKeywords = [];
    if (keywordsMatch && keywordsMatch[1]) {
      espKeywords = keywordsMatch[1].trim().split(',').map(k => k.trim()).filter(k => k.length > 0);
    }
    esData = {
      reponse: reponseMatch ? reponseMatch[1].trim() : null,
      affirmation: affirmationMatch ? affirmationMatch[1].trim() : null,
      espKeywords: espKeywords.length ? espKeywords : null,
    };
  } catch (e) { /* pas de fichier ES */ }

  families[meta.key].cards.push({
    id: base,
    file: imgPath,
    marseille: marseillePath,
    scan: scanReference(path.join(CARDS_DIR, base + '.jpg')),
    name,
    nameM,
    family: meta.key,
    familyName: meta.name,
    element: meta.element,
    num: parseInt(num, 10),
    md: content,
    es: esData,
  });
});

Object.values(families).forEach(f => f.cards.sort((a, b) => a.num - b.num));

const VERSION = fs.readFileSync(path.join(__dirname, '..', 'VERSION'), 'utf8').trim();

const output = {
  version: VERSION,
  families: ORDER.map(p => {
    const meta = FAMILY_META[p];
    const f = families[meta.key];
    return {
      key: f.key,
      name: f.name,
      titleFull: meta.titleFull,
      short: f.short,
      element: f.element,
      elementSym: meta.elementSym,
      elementLine: meta.elementLine,
      desc: meta.desc,
      signs: meta.signs,
      accent: f.accent,
      count: f.cards.length,
      entryCard: f.cards[0].id,
      cards: f.cards,
    };
  }),
};

const json = JSON.stringify(output);
const out = `/* Auto-généré par build_data.js — ne pas éditer manuellement */\nconst TAROT = ${json};\n`;
fs.writeFileSync(path.join(__dirname, 'data.js'), out);

let total = 0;
output.families.forEach(f => total += f.cards.length);
const sizeKB = Math.round(Buffer.byteLength(out) / 1024);
console.log(`data.js généré : ${output.families.length} familles, ${total} cartes, ${sizeKB} KB`);
