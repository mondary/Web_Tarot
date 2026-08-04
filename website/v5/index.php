<?php
declare(strict_types=1);

// V5 = frontend V4, données servies par un Vault SQLite côté serveur.
final class Vault {
    private static ?PDO $db = null;

    static function db(): PDO {
        return self::$db ??= new PDO('sqlite:' . __DIR__ . '/vault.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    static function read(string $path): ?string {
        $s = self::db()->prepare('SELECT data FROM vault WHERE path=?');
        $s->execute([$path]);
        $r = $s->fetch();
        return $r ? (string)$r['data'] : null;
    }

    static function json(string $path): ?array {
        $raw = self::read($path);
        return $raw === null ? null : json_decode($raw, true);
    }

    static function image(string $path): never {
        $s = self::db()->prepare('SELECT mime,data FROM vault WHERE path=?');
        $s->execute([$path]);
        $r = $s->fetch();
        if (!$r) { http_response_code(404); exit('Not found'); }
        header('Content-Type: ' . $r['mime']);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . strlen($r['data']));
        echo $r['data'];
        exit;
    }
}

function base_path(): string {
    if ($base = $_SERVER['TAROT_LOCAL_BASE_PATH'] ?? '') return $base;
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $dir === '/' || $dir === '.' ? '' : $dir;
}

$base = base_path();
$path = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
$path = '/' . trim($path, '/');

if (isset($_GET['img'])) Vault::image('/img/' . urldecode((string)$_GET['img']));
if (preg_match('#^/img/(.+)$#', $path, $m)) Vault::image('/img/' . $m[1]);
if (isset($_GET['js']) && $_GET['js'] === 'spreads') {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    readfile(__DIR__ . '/tarot-spreads.js');
    exit;
}
if (isset($_GET['svg']) && preg_match('/^[a-z]+$/', (string)$_GET['svg'])) {
    Vault::image('/svg/' . $_GET['svg'] . '.svg');
}
if ($path !== '/') { http_response_code(404); exit('404'); }

$data = Vault::json('/app-data.json');
$css = Vault::read('/css/app.css') ?: '';
$css .= '.fam-card{position:relative;min-width:0;min-height:0;border-radius:.9rem;overflow:hidden;cursor:pointer;transition:.5s var(--ease);display:flex;flex-direction:column;aspect-ratio:2/3.6;border:1px solid var(--ac);background:var(--bg);box-shadow:0 10px 28px rgba(0,0,0,.35)}.fam-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.5),0 0 30px rgba(201,162,39,.15)}.fam-card-inner{flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1rem;text-align:center;position:relative;gap:.3rem}.fam-card-img{flex:1;min-width:0;display:flex;align-items:center;justify-content:center;width:100%;max-height:55%;overflow:hidden}.glyph-uni{font-family:"Cormorant Garamond",serif;font-size:3.5rem;line-height:1;color:var(--ac)}.fam-card-name{font-family:"Cormorant Garamond",serif;font-weight:400;font-size:clamp(.9rem,1.4vw,1.15rem);line-height:1.1;text-transform:uppercase;letter-spacing:.02em;color:var(--fg)}.fam-card-name em{font-style:italic;color:var(--ac)}.fam-card-elem{font-family:"DM Mono",monospace;font-size:.52rem;letter-spacing:.15em;text-transform:uppercase;color:var(--ac);display:flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border:1px solid var(--ac);border-radius:50px;opacity:.7}.fam-card-count{position:absolute;bottom:.5rem;right:.6rem;font-family:"DM Mono",monospace;font-size:.48rem;letter-spacing:.16em;text-transform:uppercase;color:var(--muted)}';
$css .= '.landing-foot{position:fixed;bottom:1.2rem;left:1.6rem;z-index:200;display:flex;align-items:center;gap:1.1rem;font-family:"DM Mono",monospace;font-size:.64rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);background:rgba(10,9,7,.75);backdrop-filter:blur(8px);padding:.45rem .9rem;border-radius:6px;border:1px solid rgba(255,255,255,.06)}.landing-foot a{color:var(--muted);transition:color .3s;border-bottom:1px solid transparent;padding-bottom:1px}.landing-foot a:hover{color:var(--fg);border-color:var(--ac)}';
$css .= '.associations{margin:2.8rem 0;padding:1.5rem;border:1px solid var(--line);background:rgba(241,237,228,.025)}.associations h2{font-family:"Cormorant Garamond",serif;font-size:1.8rem;font-weight:400;color:var(--ac);margin-bottom:.4rem}.associations .association-intro{color:var(--muted);font-family:"DM Mono",monospace;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1.4rem}.association-section{padding:1.1rem 0;border-top:1px solid var(--line)}.association-section h3{font-family:"DM Mono",monospace;font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;color:var(--fg);margin-bottom:.7rem}.association-section ul{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.6rem}.association-section li{display:flex;gap:.7rem;align-items:flex-start;padding:.6rem;border:1px solid rgba(241,237,228,.06);border-radius:.5rem;background:rgba(241,237,228,.02);transition:border-color .3s,background .3s}.association-section li:hover{border-color:rgba(201,162,39,.4);background:rgba(201,162,39,.04)}.assoc-thumb{flex:0 0 auto;width:42px;aspect-ratio:2/3;border-radius:4px;overflow:hidden;background:var(--mat);display:block;border:1px solid rgba(255,255,255,.1);cursor:pointer}.assoc-thumb img{width:100%;height:100%;object-fit:contain;display:block}.assoc-thumb-empty{background:rgba(241,237,228,.04);cursor:default}.assoc-text{min-width:0;flex:1}.assoc-link{font-family:"Cormorant Garamond",serif;font-size:1rem;font-weight:500;color:var(--fg);line-height:1.1;display:block;margin-bottom:.3rem;cursor:pointer}.assoc-link:hover{color:var(--ac)}.assoc-text p{font-size:.82rem;line-height:1.45;color:#b8b0a2;font-weight:300;margin:0}';
$css .= '.fab-bar{display:flex;gap:.6rem}';
$css .= '.fam-glyph{height:3.4rem;width:auto;display:block;margin:0 auto}';
if (!$data) { http_response_code(500); exit('Vault incomplet'); }

$cards = $data['cards'];
$families = $data['families'];
$es = $data['es'];
$cardsJson = json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$familiesJson = json_encode($families, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$esJson = json_encode($es, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$assocsMap = [];
foreach ($cards as $c) {
    $a = Vault::json('/cards/' . $c['id'] . '/associations.json');
    if ($a) $assocsMap[$c['id']] = $a;
}
$assocsJson = json_encode($assocsMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$mdMap = [];
foreach ($cards as $c) { $mdMap[$c['id']] = (string) Vault::read('/cards/' . $c['id'] . '.md'); }
$mdJson = json_encode($mdMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$baseJson = json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$ver = trim((string)@file_get_contents(__DIR__.'/../../VERSION')) ?: '3.2026.26';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5">
<title>Tarot Divinatoire</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔮</text></svg>">
<meta name="theme-color" content="#050505">
<style><?= $css ?></style>
</head>
<body>
<div id="loader"><div class="pulse"></div></div>
<div class="fx-grain"></div><div class="fx-vignette"></div>
<div class="brand" id="brand"><b>TAROT</b> <em>DIVINATOIRE</em></div>
<div class="landing-foot"><span class="ver">v<?= $ver ?></span><a href="https://github.com/mondary/Web_Tarot" target="_blank" rel="noopener">GitHub</a><a href="https://mondary.design" target="_blank" rel="noopener">mondary.design</a></div>
<div id="landing">
  <div class="full-grid-head"><h1>Tarot <em>Divinatoire</em></h1><div class="sub">78 lames du Tarot de Rider-Waite-Smith — cliquez une carte pour découvrir sa signification.</div></div>
  <div class="full-grid" id="grid"></div>
</div>
<div class="fab-bar" id="fabBar"><button class="search-launch" id="tirages-launch" aria-label="Tirages" onclick="TarotSpreads&&TarotSpreads.open()"><svg viewBox="0 0 162 154" fill="currentColor"><path d="M41.3 12.5C16 18.4 13.9 19.2 10.5 23.2c-3.4 4.1-3.9 8.4-2 17.9 3 14.8 17.5 80.3 18.6 83.6.6 1.8 2.7 4.6 4.6 6.4l3.7 3.2 9.5-.8c16.9-1.3 17.3-1.2 38.1 6 28.4 9.9 35.9 10 43.3.3 5.3-6.9 28.7-72.8 28.7-80.7q0-8.5-7.5-11.7c-2.9-1.2-3.3-1.8-3.3-5.3q-.2-8.3-6.7-11.5a81 81 0 0 0-19-4.6c-1.2 0-2.7-1.3-3.7-3-2.5-4.5-7.3-6-21.5-6.9-12.2-.7-12.6-.8-14.6-3.6a15 15 0 0 0-11.9-5.4c-1.8.1-13.3 2.5-25.5 5.4m32.8 2.6c1 1.3 2 3.5 2.3 4.9 10.5 48.8 18.6 90.1 18.1 92.7a10 10 0 0 1-3.1 5.2C87.5 121 51.2 129 41.3 129c-6.7 0-8.5-2.5-11.7-16.1C21.4 78 12 34.4 12 31.2c0-1.3 1.2-3.6 2.8-5.1 2.3-2.3 5.5-3.4 20.2-6.9l23.5-5.7c7.9-1.9 13.4-1.3 15.6 1.6m22.6 6.3c10.5.6 12.8 1.7 14.4 6.4 1.1 3.5.1 31.1-2.7 72.5-1.5 22.5-2.4 26.5-6.3 28.7a71 71 0 0 1-22.2.3l-6.4-.6 9.5-2.3c8.1-2 10-2.9 12.7-5.8 5.6-6 5.5-6.6-4.6-55.1L82 20.7c0-.3 1.2-.5 2.8-.2zm29.7 11.1c13.6 2.9 14.7 5 11.1 22.4-2.8 13.5-14.1 58.7-17.5 70.1-3.3 10.9-8.5 13.8-20.5 11.1l-3-.7 3.6-.7c4.4-.9 8.4-3.9 10.4-7.7 1-1.9 2-10.1 3-25.7 1.7-26.2 3.5-59.8 3.5-66.1 0-4.8-.2-4.7 9.4-2.7"/></svg></button><button class="search-launch" id="search-launch" aria-label="Parcourir les cartes"><svg viewBox="0 0 135 131" fill="currentColor"><path d="M39.8 8.9A47.5 47.5 0 0 0 7 54c0 13.1 4.3 23.5 13.4 32.5A46 46 0 0 0 54.1 100c11.4 0 21.4-3.6 31.3-11.4.8-.5 7.4 5.5 18.6 16.9 9.6 9.7 18.3 18 19.3 18.6 2.4 1.4 5.9-.7 5.5-3.2-.2-1.1-8.6-10.2-18.8-20.4L91.6 82l1.7-3a58 58 0 0 0 6.4-21.6 46.5 46.5 0 0 0-34.1-49 55 55 0 0 0-25.8.5m29.7 8.9a39 39 0 0 1 22.1 44.1c-6.4 30.8-42.2 41.4-65.7 19.4-18.2-17-13.6-48.8 9-62.1a37 37 0 0 1 34.6-1.4"/></svg></button></div>
<div id="search" role="dialog" aria-modal="true" aria-label="Sélecteur de cartes">
  <div class="picker-sheet"><div class="picker-grip"></div><div class="picker-top"><span class="picker-count" id="pickerCount"></span><button class="s-close" id="s-close" aria-label="Fermer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
    <div class="s-query" id="sQuery"><span class="ph">Tapez une lame…</span></div>
    <div class="picker-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg><input class="s-input" id="sInput" type="text" autocomplete="off" placeholder="Rechercher…" inputmode="search"></div>
    <div class="picker-chips" id="pickerChips"></div><div class="picker-body"><div class="s-grid" id="sGrid"></div></div>
  </div>
</div>
<div class="d-stage" id="detail"><div class="back-btn" id="backBtn">← Retour</div><div class="d-hero"><img class="d-hero-img" id="heroImg" alt=""></div><div class="d-panel"><div class="d-panel-inner" id="dInner"></div></div><div class="d-loop" id="loopBar"></div></div>
<script src="<?= $base ?>/index.php?js=spreads"></script>
<script>
const B=<?= $baseJson ?>, CARDS=<?= $cardsJson ?>, FAMILIES=<?= $familiesJson ?>, ES_MAP=<?= $esJson ?>, ASSOCS=<?= $assocsJson ?>, MD_MAP=<?= $mdJson ?>, IMG_MAP={};
for(const c of CARDS) IMG_MAP[c.id]=B+'/index.php?img='+encodeURIComponent(c.id+'.jpg')+'&v='+<?= (string)@filemtime(__DIR__.'/vault.sqlite') ?>;
let currentIdx=-1, searchState={fam:'',q:'',selIdx:0};
const PICTOS={
 amour:'<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z"/></svg></span>',
 travail:'<span class="content-icon"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg></span>',
 finances:'<span class="content-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4"/></svg></span>',
 guidance:'<span class="content-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z"/></svg></span>',
 interpretation:'<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>',
 signification:'<span class="content-icon"><svg viewBox="0 0 24 24"><path d="m12 3 1.7 5.3H19l-4.3 3.1 1.7 5.3-4.4-3.2-4.4 3.2 1.7-5.3L5 8.3h5.3L12 3Z"/></svg></span>',
 description:'<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M3 5.5c3.7-1.4 6.8-.8 9 1.2 2.2-2 5.3-2.6 9-1.2v13c-3.7-1.4-6.8-.8-9 1.2-2.2-2-5.3-2.6-9-1.2v-13Z"/><path d="M12 6.7v13"/></svg></span>',
 mots:'<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg></span>'
};
function restructureHtml(html){
 const div=document.createElement('div');div.innerHTML=html;
 div.querySelectorAll('h2').forEach(h=>{if(h.querySelector('.content-icon'))return;const t=h.textContent.toLowerCase();let k='';if(t.includes('amour'))k='amour';else if(t.includes('travail'))k='travail';else if(t.includes('finance'))k='finances';else if(t.includes('guidance'))k='guidance';else if(t.includes('interpr'))k='interpretation';else if(t.includes('signification'))k='signification';else if(t.includes('description'))k='description';else if(t.includes('mots-cl'))k='mots';if(k)h.innerHTML=PICTOS[k]+'<span>'+h.innerHTML+'</span>';});
 const mk=div.querySelector('#sec-mots-cles,section[id*="mots"]');if(mk){const hs=mk.querySelectorAll('h3');if(hs.length>=2){const w=document.createElement('div');w.className='kw-cols';hs.forEach(h=>{const c=document.createElement('div');c.className='kw-col';c.appendChild(h.cloneNode(true));let s=h.nextElementSibling;while(s&&(s.tagName==='UL'||s.tagName==='LI')){c.appendChild(s.cloneNode(true));s=s.nextElementSibling;}if(c.querySelector('ul'))w.appendChild(c);});if(w.children.length>=2){hs.forEach(h=>{let s=h.nextElementSibling;while(s&&(s.tagName==='UL'||s.tagName==='LI')){let n=s.nextElementSibling;s.remove();s=n;}h.remove();});mk.appendChild(w);}}}
 const names=['amour','travail','finance','guidance'], secs=[];div.querySelectorAll('section,div.section').forEach(s=>{const h=s.querySelector('h2');if(h&&names.some(n=>h.textContent.toLowerCase().includes(n)))secs.push(s);});if(secs.length>=2){const g=document.createElement('div');g.className='theme-grid';secs[0].replaceWith(g);secs.forEach(s=>g.appendChild(s));}
 return div.innerHTML;
}
function fam(k){return FAMILIES.find(f=>f.key===k)||{};}
function assocCard(t){if(!t)return null;let cs=[t];if(t.indexOf('/')>=0)cs=cs.concat(t.split('/').map(s=>s.trim()));for(const cand of cs){const n=cand.toLowerCase().replace(/[\u2018\u2019]/g,"'");for(const c of CARDS){if(c.name.toLowerCase()===n)return c;}}return null;}
function renderAssociations(rows){if(!rows||!rows.length)return '';const secs={},order=[];let total=0;for(const r of rows){if(!secs[r.section]){secs[r.section]={t:r.section,it:[]};order.push(r.section);}secs[r.section].it.push(r);total++;}let h='<section class="associations"><h2>Associations</h2><p class="association-intro">'+total+' combinaisons avec cette lame</p>';for(const k of order){const s=secs[k];h+='<div class="association-section"><h3>'+s.t+'</h3><ul>';for(const r of s.it){const target=(r.pair||'').indexOf(' + ')>=0?(r.pair.split(' + ')[1]||'').trim():(r.pair||'');const card=assocCard(target);const thumb=card?'<a class="assoc-thumb" onclick="openDetail('+card.sort+')">'+(IMG_MAP[card.id]?'<img src="'+IMG_MAP[card.id]+'">':'')+'</a>':'<span class="assoc-thumb assoc-thumb-empty"></span>';const link=card?'<a class="assoc-link" onclick="openDetail('+card.sort+')">'+target+'</a>':'<span class="assoc-link">'+target+'</span>';h+='<li>'+thumb+'<div class="assoc-text">'+link+'<p>'+(r.descr||'')+'</p></div></li>';}h+='</ul></div>';}return h+'</section>';}
function renderGrid(){let h='',last=null;for(const c of CARDS){if(c.fam!==last){const f=fam(c.fam),group=CARDS.filter(x=>x.fam===c.fam),first=group[0],n=group.length;h+='<div class="fam-card" style="--ac:'+(f.ac||'#c9a227')+'" onclick="openDetail('+first.sort+')"><div class="fam-card-inner"><div class="fam-card-img"><span class="glyph-uni">'+(f.key!=='majors'?'<img class="fam-glyph" src="'+B+'/index.php?svg='+f.key+'">':(f.sym||'✦'))+'</span></div><div class="fam-card-name"><em>'+((f.short||f.name||c.fam))+'</em></div><div class="fam-card-elem"><span>'+(f.sym||'✦')+'</span>'+(f.el||'')+'</div><div class="fam-card-count">'+n+' LAMES</div></div></div>';last=c.fam;}const num=String(c.sort+1).padStart(2,'0');h+='<div class="mini" data-sort="'+c.sort+'" onclick="openDetail('+c.sort+')"><div class="ph"><img src="'+IMG_MAP[c.id]+'" alt="'+c.name+'" loading="lazy"></div><div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+num+'</span></div></div>';}document.getElementById('grid').innerHTML=h;renderChips();}
function renderChips(){let h='<button class="chip active" data-fam="" onclick="setFam(\'\')">Tout<span class="n">'+CARDS.length+'</span></button>';for(const f of FAMILIES){const n=CARDS.filter(c=>c.fam===f.key).length;h+='<button class="chip" data-fam="'+f.key+'" style="--ac:'+f.ac+'" onclick="setFam(\''+f.key+'\')"><span class="sym">'+f.sym+'</span>'+f.short+'<span class="n">'+n+'</span></button>';}document.getElementById('pickerChips').innerHTML=h;}
function openDetail(sort){const i=CARDS.findIndex(c=>c.sort===sort);if(i<0)return;currentIdx=i;const c=CARDS[i],f=fam(c.fam),es=ES_MAP[c.id]||{},inFam=CARDS.filter(x=>x.fam===c.fam),fi=inFam.findIndex(x=>x.id===c.id),num=String(i+1).padStart(2,'0');document.getElementById('heroImg').src=IMG_MAP[c.id];let badges='';if(es.aff)badges+='<span class="es-badge aff">« '+es.aff+' »</span>';if(es.rep)badges+='<span class="es-badge resp">'+es.rep+'</span>';if(badges)badges='<div class="es-badges">'+badges+'</div>';let cod=c.cod?'<div class="card-of-day"><div class="card-of-day-label"><span class="icon">☀</span>Carte du Jour</div><div class="card-of-day-content"><p>'+c.cod+'</p></div></div>':'';let thumbs=inFam.map(x=>'<div class="d-thumb'+(x.id===c.id?' current':'')+'" onclick="openDetail('+x.sort+')"><img src="'+IMG_MAP[x.id]+'"></div>').join('');document.getElementById('dInner').innerHTML='<div class="d-meta"><b>'+num+'</b> / '+CARDS.length+'<span class="sep">·</span>'+f.name+' '+(fi+1)+'/'+inFam.length+'<span class="sep">·</span>'+f.el+'</div><h1 class="d-title"><em>'+c.name+'</em></h1>'+badges+cod+'<div class="prose">'+restructureHtml(c.html||'<p>Pas de description.</p>')+'</div>'+renderAssociations(ASSOCS[c.id])+'<div class="d-thumbs">'+thumbs+'</div>';const p=CARDS[(i-1+CARDS.length)%CARDS.length],n=CARDS[(i+1)%CARDS.length];document.getElementById('loopBar').innerHTML='<a onclick="openDetail('+p.sort+')">← '+p.name+'</a><span class="pos"><b>'+num+'</b> / '+CARDS.length+'</span><a onclick="openDetail('+n.sort+')">'+n.name+' →</a>';document.getElementById('detail').classList.add('open');document.getElementById('detail').scrollTop=0;}
function closeDetail(){document.getElementById('detail').classList.remove('open');currentIdx=-1;}
function setFam(f){searchState.fam=f;document.querySelectorAll('.chip').forEach(c=>c.classList.toggle('active',c.dataset.fam===f));searchRender();}
function syncQuery(){const q=document.getElementById('sQuery');if(!searchState.q)q.innerHTML='<span class="ph">Tapez une lame…</span>';else q.textContent=searchState.q;}
function searchRender(){const out=CARDS.filter(c=>(!searchState.fam||c.fam===searchState.fam)&&(!searchState.q||c.name.toLowerCase().includes(searchState.q)||String(c.num).padStart(2,'0').includes(searchState.q)));document.getElementById('pickerCount').innerHTML='<b>'+out.length+'</b> <span class="ac">'+(out.length>1?'lames':'lame')+'</span>';document.getElementById('sGrid').innerHTML=out.length?out.map((c,i)=>'<div class="mini '+(i===searchState.selIdx?'sel':'')+'" onclick="openDetail('+c.sort+');closeSearch()"><div class="ph"><img src="'+IMG_MAP[c.id]+'"></div><div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+String(c.sort+1).padStart(2,'0')+'</span></div></div>').join(''):'<div class="s-empty">Aucune lame</div>';}
function openSearch(){document.getElementById('search').classList.add('open');document.body.style.overflow='hidden';searchRender();setTimeout(()=>document.getElementById('sInput').focus(),100);}
function closeSearch(){document.getElementById('search').classList.remove('open');document.body.style.overflow='';document.getElementById('sInput').value='';searchState={fam:'',q:'',selIdx:0};syncQuery();setFam('');}
document.getElementById('search-launch').onclick=openSearch;document.getElementById('s-close').onclick=closeSearch;document.getElementById('backBtn').onclick=closeDetail;document.getElementById('search').onclick=e=>{if(e.target.id==='search')closeSearch();};
document.getElementById('sInput').oninput=e=>{searchState.q=e.target.value.trim().toLowerCase();syncQuery();searchRender();};
document.addEventListener('keydown',e=>{if(e.metaKey||e.ctrlKey||e.altKey)return;const tag=e.target?.tagName;if(tag&&/INPUT|TEXTAREA|SELECT/i.test(tag))return;const so=document.getElementById('search').classList.contains('open');if(e.key==='Escape'){if(so)closeSearch();else closeDetail();return;}if(so){if(e.key==='ArrowDown'){searchState.selIdx++;searchRender();}if(e.key==='ArrowUp'){searchState.selIdx=Math.max(0,searchState.selIdx-1);searchRender();}if(e.key==='Backspace'){searchState.q=searchState.q.slice(0,-1);document.getElementById('sInput').value=searchState.q;syncQuery();searchRender();e.preventDefault();return;}if(e.key==='Enter'){document.querySelector('#sGrid .mini.sel')?.click();}if(e.key.length===1&&/\p{L}|\p{N}/u.test(e.key)){searchState.q+=e.key.toLowerCase();document.getElementById('sInput').value=searchState.q;syncQuery();searchRender();e.preventDefault();}return;}if(e.key.length===1&&/\p{L}|\p{N}/u.test(e.key)){openSearch();searchState.q=e.key.toLowerCase();document.getElementById('sInput').value=e.key;syncQuery();searchRender();e.preventDefault();return;}if(currentIdx>=0){if(e.key==='ArrowLeft')openDetail(CARDS[(currentIdx-1+CARDS.length)%CARDS.length].sort);if(e.key==='ArrowRight')openDetail(CARDS[(currentIdx+1)%CARDS.length].sort);return;}});
renderGrid();document.getElementById('loader').classList.add('gone');
function buildTarot(){
  window.TAROT={families:FAMILIES.map(function(f){
    return {
      key:f.key,name:f.name,accent:f.ac||'#c9a227',
      cards:CARDS.filter(function(c){return c.fam===f.key}).map(function(c){
        const es=ES_MAP[c.id]||{};
        return {id:c.id,name:c.name,num:c.num,sort:c.sort,family:c.fam,familyName:f.name,element:f.el||'',file:IMG_MAP[c.id]||'',md:MD_MAP[c.id]||'',es:{reponse:es.rep||'',affirmation:es.aff||''}};
      })
    };
  })};
}
buildTarot();if(window.TarotSpreads)TarotSpreads.init();
window.tarotOpenCard=function(card){if(window.TarotSpreads)TarotSpreads.closeSpread();if(typeof card.sort==='number'&&typeof openDetail==='function')openDetail(card.sort);};
</script>
</body></html>
