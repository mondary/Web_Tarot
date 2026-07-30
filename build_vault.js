#!/usr/bin/env node
/**
 * build_vault.js — Construit tarot.sqlite (coffre fort)
 * 
 * Usage: node build_vault.js
 * Output: website/v4/tarot.sqlite
 */

const Database = require('better-sqlite3');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const ROOT = __dirname;
const OUT = path.join(ROOT, 'website', 'v4', 'tarot.sqlite');

// ── Helpers ──────────────────────────────────────────────

function sha256(buf) {
  return crypto.createHash('sha256').update(buf).digest('hex');
}

function mimeFor(ext) {
  const map = {
    '.md': 'text/markdown',
    '.html': 'text/html',
    '.js': 'application/javascript',
    '.json': 'application/json',
    '.css': 'text/css',
    '.php': 'application/x-httpd-php',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.png': 'image/png',
    '.webp': 'image/webp',
    '.svg': 'image/svg+xml',
    '.txt': 'text/plain',
    '.sqlite': 'application/x-sqlite3',
    '.env': 'text/plain',
    '.gitignore': 'text/plain',
  };
  return map[ext.toLowerCase()] || 'application/octet-stream';
}

function walkDir(dir, base = '') {
  const results = [];
  if (!fs.existsSync(dir)) return results;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name.startsWith('.') && entry.name !== '.gitignore' && entry.name !== '.htaccess') continue;
    if (entry.name === 'node_modules' || entry.name === '.git') continue;
    const fullPath = path.join(dir, entry.name);
    const relPath = base ? `${base}/${entry.name}` : entry.name;
    if (entry.isDirectory()) {
      results.push(...walkDir(fullPath, relPath));
    } else {
      results.push({ fullPath, relPath });
    }
  }
  return results;
}

// ── Card parsing ─────────────────────────────────────────

function extractCardOfDay(mdContent) {
  const match = mdContent.match(/## Carte du Jour\s*\n\s*([\s\S]*?)(?=## |$)/);
  return match ? match[1].trim() : null;
}

function mdToHtml(mdContent) {
  let html = mdContent;
  const sections = [];
  const sectionRegex = /^## (.+)$/gm;
  let lastIndex = 0;
  let currentTitle = null;
  let currentStart = 0;
  let match;

  while ((match = sectionRegex.exec(html)) !== null) {
    if (currentTitle) {
      const content = html.slice(currentStart, match.index).trim();
      if (currentTitle !== 'Carte du Jour' && currentTitle !== 'Conclusion') {
        sections.push(`<h2>${currentTitle}</h2>\n${content}`);
      }
    }
    currentTitle = match[1];
    currentStart = sectionRegex.lastIndex;
  }
  if (currentTitle && currentTitle !== 'Carte du Jour' && currentTitle !== 'Conclusion') {
    const content = html.slice(currentStart).trim();
    sections.push(`<h2>${currentTitle}</h2>\n${content}`);
  }

  return sections.join('\n\n') || `<p>${html}</p>`;
}

// ── Build ────────────────────────────────────────────────

console.log('🔨 Construction du coffre fort...\n');

// Remove old DB
if (fs.existsSync(OUT)) fs.unlinkSync(OUT);
fs.mkdirSync(path.dirname(OUT), { recursive: true });

const db = new Database(OUT);
db.pragma('journal_mode = WAL');

// ── 1. VAULT TABLE (coffre fort — tous les fichiers) ─────

db.exec(`
  CREATE TABLE IF NOT EXISTS vault (
    path     TEXT PRIMARY KEY,
    content  BLOB,
    mime     TEXT,
    size     INTEGER,
    sha256   TEXT,
    modified TEXT
  );
  CREATE INDEX IF NOT EXISTS idx_vault_mime ON vault(mime);
`);

const insertVault = db.prepare(`
  INSERT OR REPLACE INTO vault (path, content, mime, size, sha256, modified)
  VALUES (@path, @content, @mime, @size, @sha256, @modified)
`);

const vaultDirs = [
  { dir: 'website', label: 'website' },
  { dir: 'archives', label: 'archives' },
  { dir: 'benchmarks', label: 'benchmarks' },
  { dir: 'scripts', label: 'scripts' },
];

const imgExts = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp'];

let vaultCount = 0;
for (const { dir, label } of vaultDirs) {
  const fullDir = path.join(ROOT, dir);
  const files = walkDir(fullDir, dir);
  let stored = 0;
  for (const { fullPath, relPath } of files) {
    const ext = path.extname(fullPath).toLowerCase();
    // Skip images (they're stored as BLOBs in cards.img)
    if (imgExts.includes(ext)) continue;
    // Skip tarot.sqlite itself
    if (relPath.endsWith('.sqlite') || relPath.endsWith('.bak')) continue;
    const stat = fs.statSync(fullPath);
    const content = fs.readFileSync(fullPath);
    insertVault.run({
      path: relPath,
      content: content,
      mime: mimeFor(ext),
      size: content.length,
      sha256: sha256(content),
      modified: stat.mtime.toISOString(),
    });
    vaultCount++;
    stored++;
  }
  console.log(`  📁 ${label}: ${stored} fichiers texte`);
}

// Fichiers root
const rootFiles = ['README.md', 'CHANGELOG.md', 'VERSION', 'LICENSE', '.gitignore', 'package.json'];
for (const f of rootFiles) {
  const fp = path.join(ROOT, f);
  if (fs.existsSync(fp)) {
    const stat = fs.statSync(fp);
    const content = fs.readFileSync(fp);
    insertVault.run({
      path: f,
      content: content,
      mime: mimeFor(path.extname(f)),
      size: content.length,
      sha256: sha256(content),
      modified: stat.mtime.toISOString(),
    });
    vaultCount++;
  }
}

console.log(`  📦 Vault: ${vaultCount} fichiers stockés\n`);

// ── 2. APP TABLES (pour le web app) ──────────────────────

db.exec(`
  CREATE TABLE IF NOT EXISTS families (
    key         TEXT PRIMARY KEY,
    name        TEXT,
    title_full  TEXT,
    short       TEXT,
    element     TEXT,
    element_sym TEXT,
    element_line TEXT,
    desc        TEXT,
    accent      TEXT,
    sort_order  INTEGER
  );

  CREATE TABLE IF NOT EXISTS cards (
    id          TEXT PRIMARY KEY,
    family_key  TEXT,
    name        TEXT,
    num         INTEGER,
    sort_global INTEGER,
    html        TEXT,
    card_of_day TEXT,
    img         BLOB
  );

  CREATE TABLE IF NOT EXISTS card_es (
    card_id     TEXT PRIMARY KEY,
    affirmation TEXT,
    reponse     TEXT
  );

  CREATE TABLE IF NOT EXISTS card_associations (
    card_id TEXT,
    section TEXT,
    pair    TEXT,
    descr   TEXT
  );

  CREATE TABLE IF NOT EXISTS spreads (
    id    TEXT PRIMARY KEY,
    name  TEXT,
    html  TEXT
  );

  CREATE INDEX IF NOT EXISTS idx_cards_family ON cards(family_key);
  CREATE INDEX IF NOT EXISTS idx_cards_sort ON cards(sort_global);
  CREATE INDEX IF NOT EXISTS idx_asso_card ON card_associations(card_id);
`);

// ── Families ─────────────────────────────────────────────

const families = [
  { key: 'majors', name: 'Arcanes Majeurs', title_full: 'Arcanes Majeurs', short: 'Majeurs', element: 'Éther', element_sym: '✦︎', element_line: 'les 22 arcanes du chemin initiatique', desc: 'Les 22 arcanes majeurs racontent les grandes étapes et leçons de la vie.', accent: '#c9a227', sort_order: 0 },
  { key: 'wands', name: 'Bâtons', title_full: 'Bâtons', short: 'Bâtons', element: 'Feu', element_sym: '△', element_line: "l'action, la passion et la créativité", desc: 'Les bâtons représentent l\'action, la passion et la créativité.', accent: '#e85d3a', sort_order: 1 },
  { key: 'cups', name: 'Coupes', title_full: 'Coupes', short: 'Coupes', element: 'Eau', element_sym: '▽', element_line: "les émotions, l'amour et l'intuition", desc: 'Les coupes représentent les émotions et les relations.', accent: '#3a8de8', sort_order: 2 },
  { key: 'pentacles', name: 'Deniers', title_full: 'Deniers', short: 'Deniers', element: 'Terre', element_sym: '◯', element_line: 'le monde matériel et la finance', desc: 'Les deniers représentent le monde matériel et le travail.', accent: '#c9a227', sort_order: 3 },
  { key: 'swords', name: 'Épées', title_full: 'Épées', short: 'Épées', element: 'Air', element_sym: '△', element_line: "l'intellect et la communication", desc: 'Les épées représentent l\'intellect et les décisions.', accent: '#7eb8d4', sort_order: 4 },
];

const insertFam = db.prepare(`INSERT OR REPLACE INTO families VALUES (@key,@name,@title_full,@short,@element,@element_sym,@element_line,@desc,@accent,@sort_order)`);
for (const f of families) insertFam.run(f);
console.log(`  Familles: ${families.length}`);

// ── Cards ────────────────────────────────────────────────

const familyMap = { a: 'majors', b: 'wands', c: 'cups', d: 'pentacles', e: 'swords' };

const frFiles = walkDir(path.join(ROOT, 'benchmarks', 'cards_alt'), 'benchmarks/cards_alt')
  .filter(f => f.relPath.endsWith('_unlimitedFR.md'))
  .filter(f => !path.basename(f.relPath).startsWith('t_'));

let sortGlobal = 0;
let cardCount = 0;

const insertCard = db.prepare(`INSERT OR REPLACE INTO cards (id,family_key,name,num,sort_global,html,card_of_day,img) VALUES (@id,@family_key,@name,@num,@sort_global,@html,@card_of_day,@img)`);

for (const { fullPath, relPath } of frFiles) {
  const basename = path.basename(relPath).replace('_unlimitedFR.md', '');

  // Extraire prefix et nom
  const parts = basename.split('_');
  const letter = parts[0];
  const num = parseInt(parts[1], 10);
  const familyKey = familyMap[letter] || 'majors';

  // Nom affichable
  let displayName;
  if (letter === 'a') {
    displayName = parts.slice(2).join(' ');
    displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1);
    if (!displayName.startsWith('Le ') && !displayName.startsWith('La ') && !['Fou', 'Mat', 'Monde', 'Soleil', 'Lune', 'Diable', 'Pendu', 'Jugement'].includes(displayName)) {
      // pas de prefix
    }
  } else {
    const rankNames = { '01': 'As', '11': 'Valet', '12': 'Cavalier', '13': 'Reine', '14': 'Roi' };
    const rank = parts[1];
    const rankName = rankNames[rank] || ['', 'As', 'Deux', 'Trois', 'Quatre', 'Cinq', 'Six', 'Sept', 'Huit', 'Neuf', 'Dix'][parseInt(rank)];
    const suitName = { b: 'de Bâton', c: 'de Coupe', d: 'de Denier', e: "d'Épée" }[letter];
    displayName = `${rankName} ${suitName}`;
  }

  // Lire le contenu FR
  const mdContent = fs.readFileSync(fullPath, 'utf8');
  const html = mdToHtml(mdContent);
  const cardOfDay = extractCardOfDay(mdContent);

  // Trouver l'image
  const cardId = basename;
  let imgPath = null;
  const imgCandidates = [
    path.join(ROOT, 'website', 'cards', `${cardId}_fr.jpg`),
    path.join(ROOT, 'website', 'cards', `${cardId}.jpg`),
    path.join(ROOT, 'benchmarks', 'cards_alt', `${cardId}.jpg`),
  ];
  for (const p of imgCandidates) {
    if (fs.existsSync(p) && fs.statSync(p).size > 100) {
      imgPath = p;
      break;
    }
  }
  const img = imgPath ? fs.readFileSync(imgPath) : null;

  insertCard.run({
    id: cardId,
    family_key: familyKey,
    name: displayName,
    num: num,
    sort_global: sortGlobal++,
    html: html,
    card_of_day: cardOfDay,
    img: img,
  });
  cardCount++;
}
console.log(`  Cartes: ${cardCount}`);

// ── ES Data (affirmations/réponses) ──────────────────────

const esFiles = walkDir(path.join(ROOT, 'website', 'cards'), 'website/cards')
  .filter(f => f.relPath.endsWith('_ES.md'));

const insertEs = db.prepare(`INSERT OR REPLACE INTO card_es (card_id, affirmation, reponse) VALUES (@card_id, @affirmation, @reponse)`);
let esCount = 0;

for (const { fullPath } of esFiles) {
  const basename = path.basename(fullPath).replace('_ES.md', '');
  const content = fs.readFileSync(fullPath, 'utf8');

  let affirmation = '';
  let reponse = '';

  const affMatch = content.match(/\*\*Affirmation\s*:?\*\*\s*\n*\s*(.+)/);
  if (affMatch) affirmation = affMatch[1].trim().replace(/^>\s*/, '');

  const repMatch = content.match(/\*\*R[ÉE]PONSE\s*:?\*\*\s*(.+)/);
  if (repMatch) reponse = repMatch[1].trim();

  if (affirmation || reponse) {
    insertEs.run({ card_id: basename, affirmation, reponse });
    esCount++;
  }
}
console.log(`  ES data: ${esCount}`);

// ── Associations ─────────────────────────────────────────

const assocPath = path.join(ROOT, 'website', 'associations.js');
if (fs.existsSync(assocPath)) {
  const raw = fs.readFileSync(assocPath, 'utf8');
  try {
    const vm = require('vm');
    const sandbox = { CARD_ASSOCIATIONS: {}, TAROT: { families: [] } };
    vm.createContext(sandbox);
    vm.runInContext(raw, sandbox, { timeout: 5000 });
    const entries = Object.entries(sandbox.CARD_ASSOCIATIONS);
    const insertAssoc = db.prepare(`INSERT INTO card_associations (card_id, section, pair, descr) VALUES (?,?,?,?)`);
    const insertMany = db.transaction(() => {
      for (const [cardId, mdText] of entries) {
        const sections = mdText.split(/^## /m);
        for (const sec of sections) {
          if (!sec.trim()) continue;
          const secTitle = sec.split('\n')[0].trim();
          const lines = sec.split('\n').filter(l => l.startsWith('- **'));
          for (const line of lines) {
            const pairMatch = line.match(/^\- \*\*(.+?)\*\*\s*:\s*(.+)/);
            if (pairMatch) {
              insertAssoc.run(cardId, secTitle, pairMatch[1], pairMatch[2]);
            }
          }
        }
      }
    });
    insertMany();
    const count = db.prepare('SELECT COUNT(*) as c FROM card_associations').get();
    console.log(`  Associations: ${count.c} combinaisons (${entries.length} cartes)`);
  } catch (e) {
    console.log(`  Associations: skip (${e.message})`);
  }
}

// ── Metadata ────────────────────────────────────────────

db.exec(`
  CREATE TABLE IF NOT EXISTS meta (
    key   TEXT PRIMARY KEY,
    value TEXT
  );
`);

const insertMeta = db.prepare(`INSERT OR REPLACE INTO meta VALUES (?, ?)`);
insertMeta.run('version', require('./package.json').version || '4.0.0');
insertMeta.run('built', new Date().toISOString());
insertMeta.run('vault_files', String(vaultCount));
insertMeta.run('cards', String(cardCount));

// ── Summary ──────────────────────────────────────────────

const sizeMB = (fs.statSync(OUT).size / 1024 / 1024).toFixed(1);
console.log(`\n✅ Coffre fort construit: ${OUT}`);
console.log(`   Taille: ${sizeMB} MB`);
console.log(`   Fichiers vault: ${vaultCount}`);
console.log(`   Cartes: ${cardCount}`);

db.close();
