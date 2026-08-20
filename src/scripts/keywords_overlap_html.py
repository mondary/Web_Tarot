# -*- coding: utf-8 -*-
"""Génère src/benchmarks/keywords/analyse.html — interface autonome d'analyse
des superpositions de mots-clés entre les 78 lames (vault v9).
Usage: python3 src/scripts/keywords_overlap_html.py
"""
import json, sqlite3, unicodedata
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DB = ROOT / 'src/website/v9/vault.sqlite'
OUT = ROOT / 'benchmarks/keywords/analyse.html'

FAMNAMES = {'majors': 'Majeurs', 'batons': 'Bâtons', 'epees': 'Épées', 'coupes': 'Coupes', 'deniers': 'Deniers'}
ACCENTS = {'majors': '#c9a227', 'batons': '#c45a2e', 'epees': '#8fa3b5', 'coupes': '#5b8fa3', 'deniers': '#7a9b5e'}


def norm(s):
    s = s.strip().lower()
    s = unicodedata.normalize('NFD', s)
    return ''.join(c for c in s if unicodedata.category(c) != 'Mn')


db = sqlite3.connect(DB)
app = json.loads(db.execute("SELECT data FROM vault WHERE path='/app-data.json'").fetchone()[0])

cards_src = app['cards']
index = defaultdict(list)   # norm -> [(cid, pos, orig)]
card_kws = {}               # cid -> {E:[norm], V:[norm]}

for c in cards_src:
    cid = c['id']
    card_kws[cid] = {'E': [], 'V': []}
    for pos, field in (('E', 'keywords_up'), ('V', 'keywords_down')):
        for kw in (c.get(field) or '').split(','):
            kw = kw.strip()
            if kw:
                n = norm(kw)
                index[n].append((cid, pos, kw))
                card_kws[cid][pos].append(n)

shared = {k for k, v in index.items() if len({o[0] for o in v}) > 1}

pair_kws = defaultdict(set)
for k in shared:
    occ = index[k]
    ids = sorted({o[0] for o in occ})
    for i in range(len(ids)):
        for j in range(i + 1, len(ids)):
            pair_kws[(ids[i], ids[j])].add(k)

card_by_id = {c['id']: c for c in cards_src}

# ── cartes ──
cards_out = []
for c in cards_src:
    cid = c['id']
    d = card_kws[cid]
    tot = len(d['E']) + len(d['V'])
    allk = set(d['E']) | set(d['V'])
    sh = len(allk & shared)

    def kw_objs(pos):
        out = []
        for rank, n in enumerate(d[pos], 1):
            others = [(card_by_id[o[0]]['name'], o[1]) for o in index[n] if o[0] != cid]
            out.append({'o': index[n][0][2] if n in shared else next(o[2] for o in index[n] if o[0] == cid),
                        'n': n, 'rank': rank, 'tot': tot,
                        'shared': sorted(set(others)) if others else []})
        return out

    overlaps = defaultdict(set)
    for (a, b), kws in pair_kws.items():
        if a == cid: overlaps[b] |= kws
        if b == cid: overlaps[a] |= kws
    ov = sorted([{'id': o, 'name': card_by_id[o]['name'], 'fam': FAMNAMES[card_by_id[o]['fam']],
                  'count': len(k), 'kws': sorted(k)} for o, k in overlaps.items()],
                key=lambda x: -x['count'])
    cards_out.append({'id': cid, 'name': c['name'], 'fam': c['fam'], 'famName': FAMNAMES[c['fam']],
                      'accent': ACCENTS[c['fam']], 'E': kw_objs('E'), 'V': kw_objs('V'),
                      'tot': tot, 'sh': sh, 'pct': round(100 * sh / tot) if tot else 0, 'overlaps': ov})

# ── mots-clés partagés ──
kws_out = []
for n in shared:
    occ = []
    for cid, pos, orig in index[n]:
        if any(o['id'] == cid for o in occ):
            continue
        rank = card_kws[cid][pos].index(n) + 1
        tot = len(card_kws[cid]['E']) + len(card_kws[cid]['V'])
        occ.append({'id': cid, 'name': card_by_id[cid]['name'], 'fam': FAMNAMES[card_by_id[cid]['fam']],
                    'pos': pos, 'rank': rank, 'tot': tot})
    orig = next(o[2] for o in index[n])
    kws_out.append({'n': n, 'o': orig, 'nb': len(occ), 'cards': occ})
kws_out.sort(key=lambda x: (-x['nb'], x['n']))

# ── paires ──
pairs_out = sorted([{'a': card_by_id[a]['name'], 'b': card_by_id[b]['name'],
                     'fa': FAMNAMES[card_by_id[a]['fam']], 'fb': FAMNAMES[card_by_id[b]['fam']],
                     'count': len(k), 'kws': sorted(k)} for (a, b), k in pair_kws.items()],
                   key=lambda x: -x['count'])

nb_occ = sum(len(v) for v in index.values())
data = {
    'stats': {'cards': len(cards_src), 'occ': nb_occ, 'uniq': len(index), 'shared': len(shared),
              'excl': len(index) - len(shared), 'pairs': len(pair_kws)},
    'cards': cards_out, 'kws': kws_out, 'pairs': pairs_out,
}

HTML = """<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Superpositions mots-clés — 78 lames</title>
<style>
:root{--bg:#0a0907;--panel:#14120e;--panel2:#1a1712;--line:#2a2620;--fg:#f1ede4;--muted:#8a8174;--ac:#c9a227}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--fg);font:15px/1.5 Georgia,serif}
h1{font-weight:400;font-size:1.6rem;margin:0}h1 em{color:var(--ac)}
header{padding:1.2rem 1.5rem;border-bottom:1px solid var(--line);position:sticky;top:0;background:rgba(10,9,7,.95);backdrop-filter:blur(8px);z-index:10}
.stats{display:flex;gap:.6rem;flex-wrap:wrap;margin:.8rem 0}
.st{background:var(--panel);border:1px solid var(--line);border-radius:8px;padding:.35rem .7rem;font-size:.75rem;color:var(--muted)}
.st b{color:var(--fg);font-size:1rem;margin-right:.3rem}
nav{display:flex;gap:.4rem;margin-top:.6rem;flex-wrap:wrap}
nav button,#q{font-family:ui-monospace,monospace;font-size:.7rem;letter-spacing:.1em;text-transform:uppercase}
nav button{background:transparent;border:1px solid var(--line);color:var(--muted);padding:.45rem .9rem;border-radius:40px;cursor:pointer}
nav button.on{color:var(--fg);border-color:var(--ac);background:rgba(201,162,39,.15)}
#q{background:var(--panel);border:1px solid var(--line);color:var(--fg);padding:.45rem .9rem;border-radius:40px;width:220px;float:right}
table{width:100%;border-collapse:collapse;font-size:.85rem}
th{font-family:ui-monospace,monospace;font-size:.6rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);text-align:left;padding:.5rem .6rem;border-bottom:1px solid var(--line)}
td{padding:.45rem .6rem;border-bottom:1px solid rgba(42,38,32,.5);vertical-align:top}
tr.c{cursor:pointer}tr.c:hover{background:var(--panel2)}
.dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:.5rem}
.pctbar{display:inline-block;width:52px;height:7px;background:var(--panel2);border-radius:4px;overflow:hidden;vertical-align:middle;margin-right:.4rem}
.pctbar i{display:block;height:100%;background:var(--ac)}
.pct{font-family:ui-monospace,monospace;font-size:.72rem;color:var(--muted)}
tr.det>td{background:var(--panel);padding:1rem .8rem}
.chip{display:inline-block;border-radius:40px;padding:.15rem .65rem;margin:.12rem;font-size:.78rem;border:1px solid var(--line)}
.chip.up{background:rgba(90,140,90,.13);border-color:rgba(90,140,90,.4)}
.chip.dn{background:rgba(160,70,70,.13);border-color:rgba(160,70,70,.4)}
.chip.sh{border-color:var(--ac);cursor:help}
.chip small{color:var(--muted);font-size:.65rem;font-family:ui-monospace,monospace}
.lbl{font-family:ui-monospace,monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);display:block;margin:.6rem 0 .2rem}
.ovline{display:flex;gap:.5rem;align-items:baseline;padding:.25rem 0;border-bottom:1px solid rgba(42,38,32,.4);font-size:.82rem}
.ovline b{min-width:170px}
.ovline span{color:var(--muted);font-size:.72rem;font-family:ui-monospace,monospace}
.rank{display:inline-block;width:60px;height:6px;background:var(--panel2);border-radius:3px;overflow:hidden;vertical-align:middle;margin-right:.4rem}
.rank i{display:block;height:100%;background:#5b8fa3}
main{max-width:1100px;margin:0 auto;padding:1rem 1.5rem 4rem}
.pill{font-family:ui-monospace,monospace;font-size:.62rem;border-radius:4px;padding:.1rem .4rem;margin-left:.4rem}
.pill.E{background:rgba(90,140,90,.15);color:#9dc39d}.pill.V{background:rgba(160,70,70,.15);color:#cf9a9a}
.fam{font-size:.72rem;color:var(--muted)}
@media(max-width:700px){#q{float:none;width:100%;margin-top:.5rem}main{padding:1rem .6rem 3rem}.ovline b{min-width:0}}
</style></head><body>
<header>
<h1>Superpositions des <em>mots-clés</em> — 78 lames</h1>
<div class="stats" id="stats"></div>
<input id="q" placeholder="rechercher une lame ou un mot…">
<nav>
<button data-v="cards" class="on">Par lame</button>
<button data-v="kws">Par mot-clé (193)</button>
<button data-v="pairs">Paires confondables</button>
</nav>
</header>
<main><table id="tbl"></table></main>
<script>
const D = __DATA__;
let view='cards', open={};

document.getElementById('stats').innerHTML =
  `<span class="st"><b>${D.stats.occ}</b>occurrences</span>
   <span class="st"><b>${D.stats.uniq}</b>mots uniques</span>
   <span class="st"><b>${D.stats.shared}</b>partagés</span>
   <span class="st"><b>${D.stats.excl}</b>exclusifs</span>
   <span class="st"><b>${D.stats.pairs}</b>paires</span>`;

const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;');
const match = (s) => !Q || s.toLowerCase().includes(Q);
let Q='';

function chip(k, pos){
  const sh = k.shared.length ? ' sh' : '';
  const t = k.shared.length ? ` title="aussi sur : ${k.shared.map(o=>o[0]+' ('+(o[1]==='E'?'endroit':'envers')+')').join(', ')}"` : '';
  return `<span class="chip ${pos}${sh}"${t}>${esc(k.o)}<small> ${k.rank}/${k.tot}</small></span>`;
}
function rankBar(r,tot){const p=Math.round(100*(1-(r-1)/Math.max(tot-1,1)));return `<span class="rank"><i style="width:${p}%"></i></span>`}

function renderCards(){
  let h='<tr><th>Lame</th><th>Fam</th><th>E</th><th>V</th><th>Partagés</th><th>%</th><th>Recouvre le plus avec</th></tr>';
  const rows=[...D.cards].sort((a,b)=>b.pct-a.pct);
  for(const c of rows){
    if(!match(c.name) && !c.E.concat(c.V).some(k=>match(k.o))) continue;
    const top=c.overlaps.slice(0,3).map(o=>`${esc(o.name)} (${o.count})`).join(', ')||'—';
    h+=`<tr class="c" data-k="c:${c.id}"><td><span class="dot" style="background:${c.accent}"></span>${esc(c.name)}</td>
    <td class="fam">${c.famName}</td><td>${c.E.length}</td><td>${c.V.length}</td>
    <td><b>${c.sh}</b>/${c.tot}</td>
    <td><span class="pctbar"><i style="width:${c.pct}%"></i></span><span class="pct">${c.pct}%</span></td>
    <td class="fam">${top}</td></tr>`;
    if(open['c:'+c.id]){
      h+=`<tr class="det"><td colspan="7">
        <span class="lbl">À l'endroit (${c.E.length})</span>${c.E.map(k=>chip(k,'up')).join('')}
        <span class="lbl">À l'envers (${c.V.length})</span>${c.V.map(k=>chip(k,'dn')).join('')}
        <span class="lbl">Recouvrements (${c.overlaps.length} lames)</span>` +
        c.overlaps.map(o=>`<div class="ovline"><b>${esc(o.name)}</b><span>${o.count} communs :</span><span>${o.kws.map(esc).join(', ')}</span></div>`).join('') +
      `</td></tr>`;
    }
  }
  return h;
}

function renderKws(){
  let h='<tr><th>Mot-clé</th><th>Nb lames</th><th>Occurrences (lame · position · rang) — la barre pleine = mot fondateur de la lame</th></tr>';
  for(const k of D.kws){
    if(!match(k.o) && !k.cards.some(c=>match(c.name))) continue;
    h+=`<tr class="c" data-k="k:${k.n}"><td><b>${esc(k.o)}</b></td><td>${k.nb}</td>
    <td class="fam">${k.cards.map(c=>esc(c.name)).slice(0,3).join(', ')}${k.nb>3?'…':''}</td></tr>`;
    if(open['k:'+k.n]){
      h+=`<tr class="det"><td colspan="3">` +
      k.cards.map(c=>`<div class="ovline"><b>${esc(c.name)}<span class="pill ${c.pos}">${c.pos==='E'?'endroit':'envers'}</span></b>
        <span>${rankBar(c.rank,c.tot)} rang ${c.rank}/${c.tot}</span></div>`).join('') +
      `</td></tr>`;
    }
  }
  return h;
}

function renderPairs(){
  let h='<tr><th>#</th><th>Paire</th><th>Communs</th><th>Lesquels</th></tr>';
  D.pairs.forEach((p,i)=>{
    if(!match(p.a) && !match(p.b) && !p.kws.some(k=>match(k))) return;
    h+=`<tr><td class="fam">${i+1}</td><td><b>${esc(p.a)}</b> ↔ <b>${esc(p.b)}</b><br><span class="fam">${p.fa} ↔ ${p.fb}</span></td>
    <td><b>${p.count}</b></td><td>${p.kws.map(k=>`<span class="chip">${esc(k)}</span>`).join('')}</td></tr>`;
  });
  return h;
}

function render(){
  const t=document.getElementById('tbl');
  t.innerHTML = view==='cards'?renderCards(): view==='kws'?renderKws(): renderPairs();
  t.querySelectorAll('tr.c').forEach(r=>r.onclick=()=>{const k=r.dataset.k;open[k]=!open[k];render()});
}
document.querySelectorAll('nav button').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('nav button').forEach(x=>x.classList.remove('on'));
  b.classList.add('on');view=b.dataset.v;render();
});
document.getElementById('q').oninput=e=>{Q=e.target.value.trim().toLowerCase();render()};
render();
</script></body></html>"""

OUT.write_text(HTML.replace('__DATA__', json.dumps(data, ensure_ascii=False)), encoding='utf-8')
print(f'OK — {OUT} ({OUT.stat().st_size // 1024} KB, {len(cards_out)} lames, {len(kws_out)} mots partagés, {len(pairs_out)} paires)')
