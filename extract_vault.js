#!/usr/bin/env node
/**
 * extract_vault.js — Extrait tous les fichiers depuis tarot.sqlite
 * 
 * Usage: 
 *   node extract_vault.js              → extrait tout dans extracted/
 *   node extract_vault.js --filter .md → extrait seulement les .md
 *   node extract_vault.js --path website/cards/a_00_Fou.md → un fichier
 */

const Database = require('better-sqlite3');
const fs = require('fs');
const path = require('path');

const DB_PATH = path.join(__dirname, 'website', 'v4', 'tarot.sqlite');
const OUT_DIR = path.join(__dirname, 'extracted');

const db = new Database(DB_PATH, { readonly: true });

// Parse args
const args = process.argv.slice(2);
let filter = null;
let singlePath = null;

for (let i = 0; i < args.length; i++) {
  if (args[i] === '--filter' && args[i + 1]) filter = args[i + 1];
  if (args[i] === '--path' && args[i + 1]) singlePath = args[i + 1];
}

if (singlePath) {
  // Extract single file
  const row = db.prepare('SELECT * FROM vault WHERE path = ?').get(singlePath);
  if (!row) {
    console.error(`Fichier non trouvé: ${singlePath}`);
    process.exit(1);
  }
  const outPath = path.join(OUT_DIR, row.path);
  fs.mkdirSync(path.dirname(outPath), { recursive: true });
  fs.writeFileSync(outPath, row.content);
  
  // Verify checksum
  const crypto = require('crypto');
  const hash = crypto.createHash('sha256').update(row.content).digest('hex');
  const ok = hash === row.sha256 ? '✅' : '❌';
  console.log(`${ok} ${row.path} (${row.size} bytes)`);
} else {
  // Extract all (or filtered)
  let rows;
  if (filter) {
    rows = db.prepare('SELECT * FROM vault WHERE path LIKE ?').all(`%${filter}%`);
  } else {
    rows = db.prepare('SELECT * FROM vault ORDER BY path').all();
  }

  console.log(`📦 Extraction de ${rows.length} fichiers...\n`);

  let extracted = 0;
  let verified = 0;
  let failed = 0;
  const crypto = require('crypto');

  for (const row of rows) {
    const outPath = path.join(OUT_DIR, row.path);
    fs.mkdirSync(path.dirname(outPath), { recursive: true });
    fs.writeFileSync(outPath, row.content);
    
    // Verify checksum
    const hash = crypto.createHash('sha256').update(row.content).digest('hex');
    if (hash === row.sha256) {
      verified++;
    } else {
      failed++;
      console.log(`❌ CHECKSUM FAIL: ${row.path}`);
    }
    extracted++;
  }

  // Also extract card images
  const cards = db.prepare('SELECT id, name, img FROM cards WHERE img IS NOT NULL').all();
  for (const card of cards) {
    const outPath = path.join(OUT_DIR, 'website', 'cards', `${card.id}_fr.jpg`);
    fs.mkdirSync(path.dirname(outPath), { recursive: true });
    fs.writeFileSync(outPath, card.img);
    extracted++;
  }

  console.log(`\n✅ ${extracted} fichiers extraits dans extracted/`);
  console.log(`   Vérifiés: ${verified}/${rows.length}`);
  if (failed > 0) console.log(`   ❌ Échecs: ${failed}`);
}

db.close();
