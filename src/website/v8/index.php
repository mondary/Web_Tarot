<?php
declare(strict_types=1);

// V8 — refonte éditoriale. Hero (image + loupe) conservé, reste refait.
// Couche données portée de V7 (vault SQLite). Layout 100 % neuf.

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
$baseJson = json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$portraits = json_decode((string) @file_get_contents(__DIR__ . '/portraits.json'), true) ?: [];
$portraitsJson = json_encode($portraits, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5">
<title>Tarot Divinatoire</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔮</text></svg>">
<meta name="theme-color" content="#0a0907">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0907;--panel:#14120e;--panel2:#1a1712;--line:#2a2620;--fg:#f1ede4;--muted:#8a8174;--ac:#c9a227;--ac-dim:rgba(201,162,39,.18);--mat:#100e0b;--ease:cubic-bezier(.22,1,.36,1)}
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:var(--bg);color:var(--fg);-webkit-font-smoothing:antialiased}
body{font-family:"Cormorant Garamond",Georgia,serif;font-size:1.06rem;line-height:1.55;min-height:100vh;overflow-x:hidden}
img{max-width:100%;display:block}
.mono{font-family:"DM Mono",monospace;letter-spacing:.12em;text-transform:uppercase}
em{font-style:italic;color:var(--ac)}
a{color:inherit}

/* loader + grain + vignette */
#loader{position:fixed;inset:0;background:var(--bg);z-index:2000;display:flex;align-items:center;justify-content:center;transition:opacity .6s var(--ease)}
#loader.gone{opacity:0;pointer-events:none}
#loader .pulse{width:14px;height:14px;border-radius:50%;background:var(--ac);box-shadow:0 0 0 0 var(--ac-dim);animation:pulse 1.6s var(--ease) infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(201,162,39,.5)}70%{box-shadow:0 0 0 22px rgba(201,162,39,0)}100%{box-shadow:0 0 0 0 rgba(201,162,39,0)}}
.fx-grain{position:fixed;inset:0;pointer-events:none;z-index:1500;opacity:.035;mix-blend-mode:overlay;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.fx-vignette{position:fixed;inset:0;pointer-events:none;z-index:1400;background:radial-gradient(120% 100% at 50% 0%,transparent 50%,rgba(0,0,0,.55) 100%)}

/* brand + topnav */
.brand{position:fixed;top:1.2rem;left:1.5rem;z-index:1600;font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted)}
.brand b{color:var(--fg);font-weight:500} .brand em{font-style:italic;color:var(--ac);letter-spacing:.04em}
.topnav{position:fixed;top:1rem;right:1.5rem;z-index:1600;display:flex;gap:.4rem}
.topnav button{background:transparent;border:1px solid var(--line);color:var(--muted);font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;padding:.5rem .8rem;border-radius:40px;cursor:pointer;transition:.3s var(--ease)}
.topnav button:hover{color:var(--ac);border-color:var(--ac)}

/* ===== LANDING : les 78 lames directement ===== */
#landing{max-width:1200px;margin:0 auto;padding:7rem 1.5rem 5rem;position:relative;z-index:100}
.landing-head{text-align:center;margin-bottom:3rem}
.seuil-title{font-family:"Cormorant Garamond",serif;font-weight:400;font-size:clamp(3rem,11vw,8rem);line-height:.95;letter-spacing:-.01em;margin:0}
.seuil-title em{font-style:italic}
.seuil-sub{color:var(--muted);font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;margin:1.4rem 0 0}
@media(max-width:640px){#landing{padding:6rem .8rem 4rem}.landing-head{margin-bottom:2rem}.full-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem}.mini .cap .nm{font-size:.68rem}.topnav{right:.7rem}.brand{left:.8rem}}

/* ===== overlays génériques ===== */
.overlay{position:fixed;inset:0;z-index:1700;background:rgba(5,5,5,.92);backdrop-filter:blur(10px);display:none;overflow-y:auto}
.overlay.open{display:block}
.overlay-sheet{max-width:1100px;margin:auto;padding:5rem 1.5rem 3rem;position:relative}
.overlay-close{position:fixed;top:1.2rem;right:1.5rem;background:transparent;border:1px solid var(--line);color:var(--muted);width:38px;height:38px;border-radius:50%;font-size:1.2rem;cursor:pointer;z-index:1800;transition:.3s}
.overlay-close:hover{color:var(--ac);border-color:var(--ac)}

/* picker chips + grille */
.picker-chips{display:flex;gap:.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:2rem}
.chip{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border:1px solid var(--line);border-radius:40px;background:transparent;color:var(--muted);font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;transition:.3s var(--ease)}
.chip .sym{color:var(--ac)} .chip .n{opacity:.5}
.chip:hover{color:var(--fg);border-color:var(--ac)} .chip.active{color:var(--fg);border-color:var(--ac);background:var(--ac-dim)}
.full-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(108px,1fr));gap:.8rem}
.mini{cursor:pointer;transition:.4s var(--ease)}
.mini .ph{aspect-ratio:2/3.6;border-radius:6px;overflow:hidden;border:1px solid var(--line);background:var(--mat);transition:.4s var(--ease)}
.mini .ph img{width:100%;height:100%;object-fit:contain}
.mini:hover .ph{transform:translateY(-4px);border-color:var(--ac);box-shadow:0 14px 30px rgba(0,0,0,.5),0 0 24px var(--ac-dim)}
.mini .cap{display:flex;justify-content:space-between;align-items:baseline;margin-top:.4rem;font-family:"DM Mono",monospace;font-size:.52rem;letter-spacing:.08em;text-transform:uppercase}
.mini .cap .nm{color:var(--fg);font-family:"Cormorant Garamond",serif;font-size:.78rem;text-transform:none;letter-spacing:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mini .cap .no{color:var(--muted)}
.family-intro .ph{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.45rem;padding:.8rem;text-align:center;border-color:color-mix(in srgb,var(--family) 55%,var(--line));background:var(--panel)}
.family-intro .family-glyph{font-family:"Cormorant Garamond",serif;font-size:clamp(2rem,5vw,3.6rem);line-height:1;color:var(--family)}
.family-intro .family-name{font-family:"Cormorant Garamond",serif;font-style:italic;font-size:clamp(.9rem,2vw,1.2rem);line-height:1;color:var(--fg)}
.family-intro .family-element{font-family:"DM Mono",monospace;font-size:.48rem;letter-spacing:.14em;text-transform:uppercase;color:var(--family)}
.family-intro .cap .nm{color:var(--family)}
.fam-card{grid-column:1/-1;display:flex;align-items:center;gap:1rem;padding:1.2rem 0;border-top:1px solid var(--line);margin-top:.5rem}
.fam-card:first-child{border-top:none}
.fam-card .g{font-size:2rem;color:var(--ac)} .fam-card .fn{font-family:"Cormorant Garamond",serif;font-size:1.6rem;font-style:italic}
.fam-card .fc{font-family:"DM Mono",monospace;font-size:.56rem;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-left:auto}

/* ===== SEARCH overlay ===== */
.s-query{font-family:"Cormorant Garamond",serif;font-style:italic;font-size:1.4rem;color:var(--muted);text-align:center;margin:.4rem 0 1rem;min-height:1.6rem}
.s-query .ph{opacity:.5}
.s-input-wrap{display:flex;justify-content:center;margin-bottom:1.6rem}
.s-input{background:var(--panel);border:1px solid var(--line);border-radius:50px;color:var(--fg);font-family:"Cormorant Garamond",serif;font-size:1.05rem;padding:.8rem 1.4rem;width:min(440px,90vw);text-align:center;outline:none}
.s-input:focus{border-color:var(--ac)}
.s-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.7rem}
.s-empty{text-align:center;color:var(--muted);padding:3rem;font-style:italic}
.mini.sel .ph{border-color:var(--ac);box-shadow:0 0 0 2px var(--ac)}

/* ===== nuances ===== */
.nuances-body .nuc-cat{margin-bottom:1.8rem}
.nuances-body h3{font-family:"DM Mono",monospace;font-size:.66rem;letter-spacing:.14em;text-transform:uppercase;color:var(--ac);margin:0 0 .8rem;padding-bottom:.4rem;border-bottom:1px solid var(--line)}
.nuances-body ul{list-style:none;padding:0;margin:0}
.nuances-body li{display:flex;gap:.7rem;align-items:center;padding:.5rem 0;border-bottom:1px solid rgba(241,237,228,.04)}
.nuc-thumb{flex:0 0 auto;width:30px;aspect-ratio:2/3;border-radius:3px;overflow:hidden;border:1px solid var(--line);cursor:pointer}
.nuc-thumb img{width:100%;height:100%;object-fit:contain}
.nuc-card{color:var(--fg);font-weight:500} .nuc-key{color:var(--ac);font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.06em}
.nuc-desc{color:var(--muted);font-size:.92rem}

/* ===== DETAIL ===== */
.d-stage{position:fixed;inset:0;z-index:900;background:var(--bg);overflow-y:auto;overflow-x:hidden;display:none}
.d-stage.open{display:block}
.back-btn{position:fixed;top:1.2rem;left:1.5rem;z-index:950;font-family:"DM Mono",monospace;font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);cursor:pointer;background:rgba(10,9,7,.7);backdrop-filter:blur(8px);padding:.5rem .9rem;border-radius:40px;border:1px solid var(--line);transition:.3s}
.back-btn:hover{color:var(--ac);border-color:var(--ac)}

/* HERO conservé */
.d-hero{position:relative;height:70vh;min-height:420px;width:100%;overflow:hidden;background:#0a0a0a}
.d-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;filter:saturate(1.05) contrast(1.02)}
.d-hero::before{content:'';position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(180deg,rgba(0,0,0,.28) 0%,transparent 22%,transparent 45%,rgba(10,10,10,.55) 72%,#0a0a0a 100%)}
.d-hero-loupe{position:absolute;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(201,162,39,.1),transparent 65%);border:1.5px solid rgba(201,162,39,.25);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity .35s ease-out;z-index:5;box-shadow:0 0 40px rgba(201,162,39,.08)}
.d-hero-loupe.active{opacity:1}
@media(hover:none){.d-hero-loupe{display:none}}
@media(max-width:640px){.d-hero{height:55vh;min-height:300px}}

.d-panel{position:relative;z-index:2;background:#0a0a0a;margin-top:-28px;border-radius:26px 26px 0 0;padding:34px 6% 120px;min-height:60vh}
.d-panel-inner{max-width:760px;margin:0 auto}
.d-meta{font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-bottom:.6rem}
.d-meta b{color:var(--ac)}
.d-title{font-family:"Cormorant Garamond",serif;font-weight:300;font-size:clamp(2.4rem,5.5vw,4.6rem);line-height:1;text-transform:uppercase;margin:0 0 22px}
.d-title em{font-style:italic;color:var(--ac);font-weight:400}

/* bandeau identité */
.d-identite{border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--bg));border-radius:14px;padding:2rem 1.8rem;margin-bottom:2.6rem;position:relative}
.d-idee{font-family:"Cormorant Garamond",serif;font-style:italic;font-weight:500;font-size:clamp(1.5rem,3.6vw,2rem);line-height:1.15;color:var(--fg);margin:0 0 .8rem}
.d-realite{color:#b8b0a2;font-weight:300;font-size:1.02rem;margin:0 0 1.2rem}
.d-badges{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center}
.d-key{font-family:"DM Mono",monospace;font-size:.66rem;font-weight:500;letter-spacing:.1em;color:var(--ac);border:1px solid var(--ac);border-radius:40px;padding:.4rem .8rem;background:var(--ac-dim)}
.d-answer{font-family:"DM Mono",monospace;font-size:.6rem;font-weight:700;letter-spacing:.12em;border-radius:40px;padding:.4rem .8rem;border:1px solid}
.ans-oui{color:#81c784;border-color:rgba(102,187,106,.4);background:rgba(102,187,106,.1)}
.ans-non{color:#e57373;border-color:rgba(239,83,80,.4);background:rgba(239,83,80,.1)}
.ans-pas-encore{color:#ffb74d;border-color:rgba(255,167,38,.4);background:rgba(255,167,38,.1)}
.ans-peut-etre{color:var(--ac);border-color:rgba(201,162,39,.4);background:rgba(201,162,39,.1)}
.d-cod{display:flex;gap:.7rem;align-items:flex-start;margin-top:1.4rem;padding-top:1.4rem;border-top:1px solid var(--line)}
.d-cod-ic{flex:0 0 auto;color:var(--ac);font-size:1rem;line-height:1.5}
.d-cod p{margin:0;color:#b8b0a2;font-size:.96rem;line-height:1.5}

/* domaines 2×2 */
.d-section-label{font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin:2.6rem 0 1rem;display:flex;align-items:center;gap:.6rem}
.d-section-label::after{content:"";flex:1;height:1px;background:var(--line)}
.d-domains{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.d-domain{border:1px solid var(--line);background:var(--panel);border-radius:12px;padding:1.3rem 1.2rem;transition:.3s var(--ease)}
.d-domain:hover{border-color:rgba(201,162,39,.35)}
.d-domain h3{display:flex;align-items:center;gap:.5rem;font-family:"Cormorant Garamond",serif;font-style:italic;font-weight:500;font-size:1.15rem;color:var(--ac);margin:0 0 .7rem}
.d-domain h3 svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:1.6}
.d-domain p{color:#bdb5a4;font-weight:300;font-size:.93rem;line-height:1.5;margin:0 0 .6rem}
.d-domain p:last-child{margin-bottom:0}
@media(max-width:620px){.d-domains{grid-template-columns:1fr}}

/* colonnes signification + description */
.d-cols{display:grid;grid-template-columns:1fr 1fr;gap:1.6rem}
.d-col h3{display:flex;align-items:center;gap:.5rem;font-family:"DM Mono",monospace;font-size:.66rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ac);margin:0 0 .8rem}
.d-col h3 svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.6}
.d-col p{color:#bdb5a4;font-weight:300;font-size:.95rem;line-height:1.55;margin:0 0 .5rem}
@media(max-width:620px){.d-cols{grid-template-columns:1fr}}

/* citation */
.d-citation{font-family:"Cormorant Garamond",serif;font-style:italic;font-size:1.2rem;color:var(--fg);text-align:center;margin:2.8rem auto;max-width:560px;line-height:1.4;padding:0 1rem}

/* associations tiroir */
.d-assocs{margin-top:2.6rem;border-top:1px solid var(--line);padding-top:1.6rem}
.d-assocs-toggle{width:100%;background:transparent;border:none;color:var(--muted);font-family:"DM Mono",monospace;font-size:.66rem;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.6rem;padding:.6rem;transition:.3s}
.d-assocs-toggle:hover{color:var(--ac)}
.d-assocs-toggle .arr{transition:transform .3s}
.d-assocs.open .d-assocs-toggle .arr{transform:rotate(180deg)}
.d-assocs-body{max-height:0;overflow:hidden;transition:max-height .5s var(--ease)}
.d-assocs.open .d-assocs-body{max-height:6000px}
.association-section{padding:1rem 0;border-top:1px solid var(--line)}
.association-section:first-child{border-top:none}
.association-section h3{font-family:"DM Mono",monospace;font-size:.64rem;letter-spacing:.12em;text-transform:uppercase;color:var(--fg);margin:0 0 .8rem}
.association-section ul{list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.6rem}
.association-section li{display:flex;gap:.7rem;padding:.6rem;border:1px solid rgba(241,237,228,.06);border-radius:.5rem;background:var(--panel);transition:.3s}
.association-section li:hover{border-color:rgba(201,162,39,.35)}
.assoc-thumb{flex:0 0 auto;width:38px;aspect-ratio:2/3;border-radius:4px;overflow:hidden;border:1px solid var(--line);cursor:pointer}
.assoc-thumb img{width:100%;height:100%;object-fit:contain}
.assoc-link{font-family:"Cormorant Garamond",serif;font-size:1rem;color:var(--fg);cursor:pointer;display:block;margin-bottom:.2rem}
.assoc-link:hover{color:var(--ac)}
.assoc-text p{color:#9c9486;font-size:.82rem;line-height:1.4;margin:0;font-weight:300}

/* nav famille */
.d-thumbs{display:flex;gap:.5rem;overflow-x:auto;padding:2rem 0 1rem;margin-top:2rem;border-top:1px solid var(--line);scrollbar-width:thin}
.d-thumb{flex:0 0 auto;width:54px;aspect-ratio:2/3.4;border-radius:4px;overflow:hidden;border:1px solid var(--line);cursor:pointer;opacity:.5;transition:.3s var(--ease)}
.d-thumb img{width:100%;height:100%;object-fit:contain}
.d-thumb:hover{opacity:1}
.d-thumb.current{opacity:1;border-color:var(--ac);box-shadow:0 0 0 2px var(--ac)}
.d-loop{max-width:760px;margin:0 auto;padding:0 1.5rem 3rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
.d-loop a{cursor:pointer;transition:.3s} .d-loop a:hover{color:var(--ac)}
.d-loop .pos b{color:var(--ac)}
.content-icon{display:inline-flex;width:20px;height:20px;vertical-align:middle;margin-right:.4rem}
.content-icon svg{width:100%;height:100%;fill:none;stroke:var(--ac);stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
</style>
</head>
<body>
<div id="loader"><div class="pulse"></div></div>
<div class="fx-grain"></div><div class="fx-vignette"></div>
<div class="brand"><b>TAROT</b> <em>DIVINATOIRE</em></div>
<nav class="topnav">
  <button onclick="TarotSpreads&&TarotSpreads.open()">Tirages</button>
  <button onclick="openSearch()">Recherche</button>
  <button onclick="openNuances()">Nuances</button>
</nav>

<div id="landing">
  <div class="landing-head">
    <h1 class="seuil-title">Tarot <em>Divinatoire</em></h1>
    <p class="seuil-sub">78 lames du Rider-Waite-Smith — cliquez une lame pour ouvrir sa fiche</p>
  </div>
  <div class="full-grid" id="grid"></div>
</div>

<div id="search" class="overlay">
  <div class="overlay-sheet" style="max-width:900px">
    <button class="overlay-close" onclick="closeSearch()">×</button>
    <div class="s-query" id="sQuery"><span class="ph">Tapez une lame…</span></div>
    <div class="s-input-wrap"><input class="s-input" id="sInput" type="text" autocomplete="off" placeholder="Rechercher…"></div>
    <div class="picker-chips" id="pickerChips2"></div>
    <div class="s-grid" id="sGrid"></div>
  </div>
</div>

<div id="nuances" class="overlay">
  <div class="overlay-sheet" style="max-width:780px">
    <button class="overlay-close" onclick="closeNuances()">×</button>
    <h2 style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:2rem;color:var(--ac);margin:0 0 1.6rem">Nuances entre cartes</h2>
    <div class="nuances-body" id="nuancesBody"></div>
  </div>
</div>

<div class="d-stage" id="detail">
  <div class="back-btn" id="backBtn">← Retour</div>
  <div class="d-hero"><img class="d-hero-img" id="heroImg" alt=""><div class="d-hero-loupe" id="heroLoupe"></div></div>
  <div class="d-panel"><div class="d-panel-inner" id="dInner"></div></div>
  <div class="d-loop" id="loopBar"></div>
</div>

<script src="<?= $base ?>/index.php?js=spreads"></script>
<script>
const B=<?= $baseJson ?>,CARDS=<?= $cardsJson ?>,FAMILIES=<?= $familiesJson ?>,ES_MAP=<?= $esJson ?>,ASSOCS=<?= $assocsJson ?>,PORTRAITS=<?= $portraitsJson ?>,IMG_MAP={};
for(const c of CARDS) IMG_MAP[c.id]=B+'/index.php?img='+encodeURIComponent(c.id+'.jpg')+'&v='+<?= (string)@filemtime(__DIR__.'/vault.sqlite') ?>;
let currentIdx=-1, searchState={fam:'',q:'',selIdx:0};

const PICTOS={
 amour:'<svg viewBox="0 0 24 24"><path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z"/></svg>',
 travail:'<svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg>',
 finances:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4"/></svg>',
 guidance:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z"/></svg>',
 signification:'<svg viewBox="0 0 24 24"><path d="m12 3 1.7 5.3H19l-4.3 3.1 1.7 5.3-4.4-3.2-4.4 3.2 1.7-5.3L5 8.3h5.3L12 3Z"/></svg>',
 description:'<svg viewBox="0 0 24 24"><path d="M3 5.5c3.7-1.4 6.8-.8 9 1.2 2.2-2 5.3-2.6 9-1.2v13c-3.7-1.4-6.8-.8-9 1.2-2.2-2-5.3-2.6-9-1.2v-13Z"/><path d="M12 6.7v13"/></svg>'
};
function fam(k){return FAMILIES.find(f=>f.key===k)||{}}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function parseDoc(h){const d=document.createElement('div');d.innerHTML=h;return d}
function secBy(doc,kw){for(const s of doc.querySelectorAll('section')){const t=(s.querySelector('h2')?.textContent||'').toLowerCase();if(t.includes(kw))return s}return null}
function secParas(s){return s?Array.from(s.querySelectorAll('p')).map(p=>'<p>'+p.innerHTML+'</p>').join(''):''}
function secText(s){return s?(s.querySelector('p')?.textContent||''):''}

function parsePortrait(md){const o={key:'',idee:'',realite:''};if(!md)return o;for(const line of md.split('\n').map(l=>l.trim())){if(!line)continue;if(line.startsWith('🧠'))o.idee=line.replace(/^🧠\s*Idée centrale\s*:\s*/i,'').replace(/^🧠\s*/,'').trim();else if(line.startsWith('💭'))o.realite=line.replace(/^💭\s*Ce qui se passe réellement\s*:\s*/i,'').replace(/^💭\s*/,'').trim();else if(line.startsWith('🔑'))o.key=line.replace(/^🔑\s*Mot-clé(?: distinctif)?\s*:\s*/i,'').replace(/^🔑\s*/,'').trim()}return o}

const DOMAINS=[{kw:'amour',label:'Amour'},{kw:'travail',label:'Travail'},{kw:'finance',label:'Finances'},{kw:'guidance',label:'Guidance'}];

function openDetail(sort){
  const i=CARDS.findIndex(c=>c.sort===sort);if(i<0)return;currentIdx=i;
  const c=CARDS[i],f=fam(c.fam),es=ES_MAP[c.id]||{},inFam=CARDS.filter(x=>x.fam===c.fam),fi=inFam.findIndex(x=>x.id===c.id);
  const num=String(i+1).padStart(2,'0'),response=String(es.rep||'').trim().toUpperCase(),answer=['OUI','NON','PEUT-ÊTRE','PAS ENCORE'].includes(response)?response:'';
  const p=parsePortrait(PORTRAITS[c.id]||'');
  document.getElementById('heroImg').src=IMG_MAP[c.id];

  const doc=parseDoc(c.html||'');
  const ansClass=answer?('ans-'+answer.toLowerCase().replace(/\s+/g,'-')):'';

  // bandeau identité
  let ident='<section class="d-identite">';
  if(p.idee)ident+='<p class="d-idee">'+escapeHtml(p.idee)+'</p>';
  if(p.realite)ident+='<p class="d-realite">'+escapeHtml(p.realite)+'</p>';
  if(p.key||answer){ident+='<div class="d-badges">';if(p.key)ident+='<span class="d-key">'+escapeHtml(p.key)+'</span>';if(answer)ident+='<span class="d-answer '+ansClass+'">'+escapeHtml(answer)+'</span>';ident+='</div>'}
  if(c.cod)ident+='<div class="d-cod"><span class="d-cod-ic">☀</span><p>'+escapeHtml(c.cod)+'</p></div>';
  ident+='</section>';

  // 4 domaines 2×2
  let doms='<div class="d-section-label">Les quatre domaines</div><div class="d-domains">';
  for(const d of DOMAINS){const body=secParas(secBy(doc,d.kw));if(body)doms+='<div class="d-domain"><h3><span class="content-icon">'+PICTOS[d.kw]+'</span>'+d.label+'</h3>'+body+'</div>'}
  doms+='</div>';

  // colonnes signification + description
  const sig=secBy(doc,'signification'),desc=secBy(doc,'description');
  let cols='';
  if(sig||desc){cols='<div class="d-section-label">Repères</div><div class="d-cols">';
    if(sig)cols+='<div class="d-col"><h3><span class="content-icon">'+PICTOS.signification+'</span>Signification</h3>'+secParas(sig)+'</div>';
    if(desc)cols+='<div class="d-col"><h3><span class="content-icon">'+PICTOS.description+'</span>Description</h3>'+secParas(desc)+'</div>';
    cols+='</div>'}

  // citation
  const cit=secText(secBy(doc,'citation'));
  const citation=cit?'<div class="d-citation">« '+escapeHtml(cit.replace(/^«|»$/g,'').trim())+' »</div>':'';

  // associations tiroir
  const assocs=renderAssociations(ASSOCS[c.id]);
  const assocsBlock=assocs?'<div class="d-assocs" id="dAssocs"><button class="d-assocs-toggle" onclick="document.getElementById(\'dAssocs\').classList.toggle(\'open\')"><span id="assocsCount"></span><span class="arr">▾</span></button><div class="d-assocs-body">'+assocs+'</div></div>':'';

  const thumbs=inFam.map(x=>'<div class="d-thumb'+(x.id===c.id?' current':'')+'" onclick="openDetail('+x.sort+')"><img src="'+IMG_MAP[x.id]+'"></div>').join('');

  document.getElementById('dInner').innerHTML=
    '<div class="d-meta"><b>'+num+'</b> / '+CARDS.length+' <span style="opacity:.4">·</span> '+f.name+' '+(fi+1)+'/'+inFam.length+(f.el?' <span style="opacity:.4">·</span> '+f.el:'')+'</div>'+
    '<h1 class="d-title"><em>'+c.name+'</em></h1>'+
    ident+doms+cols+citation+assocsBlock+
    '<div class="d-thumbs">'+thumbs+'</div>';

  if(assocs){const n=(ASSOCS[c.id]||[]).length;const el=document.getElementById('assocsCount');if(el)el.textContent=n+' combinaison'+(n>1?'s':'')}

  const prev=CARDS[(i-1+CARDS.length)%CARDS.length],next=CARDS[(i+1)%CARDS.length];
  document.getElementById('loopBar').innerHTML='<a onclick="openDetail('+prev.sort+')">← '+prev.name+'</a><span class="pos"><b>'+num+'</b> / '+CARDS.length+'</span><a onclick="openDetail('+next.sort+')">'+next.name+' →</a>';
  document.getElementById('detail').classList.add('open');
  document.getElementById('detail').scrollTop=0;
}
function closeDetail(){document.getElementById('detail').classList.remove('open');currentIdx=-1}

function assocCard(t){if(!t)return null;let cs=[t];if(t.indexOf('/')>=0)cs=cs.concat(t.split('/').map(s=>s.trim()));for(const cand of cs){const n=cand.toLowerCase().replace(/[\u2018\u2019]/g,"'");for(const c of CARDS){if(c.name.toLowerCase()===n)return c}}return null}
function renderAssociations(rows){if(!rows||!rows.length)return'';const secs={},order=[];for(const r of rows){if(!secs[r.section]){secs[r.section]={it:[]};order.push(r.section)}secs[r.section].it.push(r)}let h='';for(const k of order){const s=secs[k];h+='<div class="association-section"><h3>'+k+'</h3><ul>';for(const r of s.it){const target=(r.pair||'').indexOf(' + ')>=0?(r.pair.split(' + ')[1]||'').trim():(r.pair||'');const card=assocCard(target);const thumb=card?'<a class="assoc-thumb" onclick="openDetail('+card.sort+')">'+(IMG_MAP[card.id]?'<img src="'+IMG_MAP[card.id]+'">':'')+'</a>':'';const link=card?'<a class="assoc-link" onclick="openDetail('+card.sort+')">'+target+'</a>':'<span class="assoc-link">'+target+'</span>';h+='<li>'+thumb+'<div class="assoc-text">'+link+'<p>'+(r.descr||'')+'</p></div></li>'}h+='</ul></div>'}return h}

const NUANCES=[
 {e:'🚶',t:'Partir / changer',i:[{id:'e_06_Six',c:'6 Épées',k:'TRANSITION',d:'je quitte une difficulté pour aller vers plus calme.'},{id:'c_08_Huit',c:'8 Coupes',k:'RENONCEMENT',d:'je quitte ce qui ne me satisfait plus.'},{id:'a_10_Roue',c:'Roue',k:'CHANGEMENT',d:'les circonstances changent.'},{id:'a_13_Mort',c:'Mort',k:'FIN',d:'quelque chose se termine.'}]},
 {e:'🛡️',t:'Lutte / tenir',i:[{id:'b_05_Cinq',c:'5 Bâtons',k:'COMPÉTITION',d:'les volontés s\'affrontent.'},{id:'b_07_Sept',c:'7 Bâtons',k:'DÉFENSE',d:'je défends ma position.'},{id:'b_09_Neuf',c:'9 Bâtons',k:'RÉSISTANCE',d:'je tiens malgré les coups.'},{id:'a_08_Force',c:'Force',k:'MAÎTRISE',d:'je domine sans brutalité.'}]},
 {e:'💞',t:'Lien / relation',i:[{id:'c_02_Deux',c:'2 Coupes',k:'RÉCIPROCITÉ',d:'échange mutuel.'},{id:'a_06_Amoureux',c:'Amoureux',k:'UNION',d:'deux êtres s\'unissent.'},{id:'d_03_Trois',c:'3 Deniers',k:'COLLABORATION',d:'compétences réunies.'},{id:'d_06_Six',c:'6 Deniers',k:'AIDE',d:'l\'un donne ce dont l\'autre a besoin.'}]},
 {e:'🔥',t:'Se lancer',i:[{id:'b_01_As',c:'As Bâtons',k:'IMPULSION',d:'l\'envie surgit.'},{id:'b_12_Cavalier',c:'Cavalier Bâtons',k:'AUDACE',d:'j\'y vais.'},{id:'b_14_Roi',c:'Roi Bâtons',k:'LEADERSHIP',d:'j\'embarque les autres.'},{id:'a_07_Chariot',c:'Chariot',k:'CONQUÊTE',d:'j\'avance vers mon objectif.'}]}
];
function renderNuances(){let h='';for(const cat of NUANCES){h+='<div class="nuc-cat"><h3><span style="margin-right:.5rem">'+cat.e+'</span>'+cat.t+'</h3><ul>';for(const it of cat.i){const thumb=IMG_MAP[it.id]?'<a class="nuc-thumb" onclick="openDetailById(\''+it.id+'\')"><img src="'+IMG_MAP[it.id]+'"></a>':'';h+='<li>'+thumb+'<div><span class="nuc-card">'+it.c+'</span> = <span class="nuc-key">'+it.k+'</span> → <span class="nuc-desc">'+it.d+'</span></div></li>'}h+='</ul></div>'}return h}
function openDetailById(id){const c=CARDS.find(x=>x.id===id);if(c){closeNuances();closeSearch();openDetail(c.sort)}}

function openNuances(){const el=document.getElementById('nuances');if(!el.querySelector('.nuances-body').innerHTML)el.querySelector('.nuances-body').innerHTML=renderNuances();el.classList.add('open');document.body.style.overflow='hidden'}
function closeNuances(){document.getElementById('nuances').classList.remove('open');document.body.style.overflow=''}

// Grille principale : les 78 lames sont visibles sans saisie.
function familyIntro(f,n){return '<div class="mini family-intro" style="--family:'+(f.ac||'#c9a227')+'" onclick="openFamily(\''+f.key+'\')"><div class="ph"><span class="family-glyph">'+(f.sym||'✦')+'</span><span class="family-name">'+f.name+'</span><span class="family-element">'+(f.el||'')+'</span></div><div class="cap"><span class="nm">Famille</span><span class="no">00</span></div></div>'}
function renderGrid(){let h='',last=null;for(const c of CARDS){if(c.fam!==last){const f=fam(c.fam),group=CARDS.filter(x=>x.fam===c.fam);h+='<div class="fam-card"><span class="g">'+(f.sym||'✦')+'</span><span class="fn">'+f.name+'</span><span class="fc">'+group.length+' lames</span></div>'+familyIntro(f,group.length);last=c.fam}h+=mini(c)}document.getElementById('grid').innerHTML=h}
function mini(c){return '<div class="mini" onclick="openDetail('+c.sort+')"><div class="ph"><img src="'+IMG_MAP[c.id]+'" alt="'+c.name+'" loading="lazy"></div><div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+String(c.sort+1).padStart(2,'0')+'</span></div></div>'}

// Recherche
function syncQuery(){const q=document.getElementById('sQuery');q.innerHTML=searchState.q?'<span>'+escapeHtml(searchState.q)+'</span>':'<span class="ph">Tapez une lame…</span>'}
function searchRender(){const out=CARDS.filter(c=>(!searchState.fam||c.fam===searchState.fam)&&(!searchState.q||c.name.toLowerCase().includes(searchState.q)||String(c.num).padStart(2,'0').includes(searchState.q)));document.getElementById('sGrid').innerHTML=out.length?out.map((c,i)=>'<div class="mini '+(i===searchState.selIdx?'sel':'')+'" onclick="openDetail('+c.sort+');closeSearch()"><div class="ph"><img src="'+IMG_MAP[c.id]+'"></div><div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+String(c.sort+1).padStart(2,'0')+'</span></div></div>').join(''):'<div class="s-empty">Aucune lame</div>'}
function openSearch(initial=''){if(initial){searchState.q=initial.toLowerCase();searchState.selIdx=0;document.getElementById('sInput').value=initial}document.getElementById('search').classList.add('open');document.body.style.overflow='hidden';syncQuery();renderChips();searchRender();setTimeout(()=>document.getElementById('sInput').focus(),100)}
function closeSearch(){document.getElementById('search').classList.remove('open');document.body.style.overflow='';document.getElementById('sInput').value='';searchState={fam:'',q:'',selIdx:0};syncQuery();renderChips()}
function setFam(f){searchState.fam=f;searchState.selIdx=0;renderChips();searchRender()}
function openFamily(f){searchState={fam:f,q:'',selIdx:0};document.getElementById('sInput').value='';openSearch()}

function renderChips(){const target=[ 'pickerChips2'];for(const id of target){const el=document.getElementById(id);if(!el)continue;let h='<button class="chip '+(searchState.fam===''?'active':'')+'" onclick="setFam(\'\')">Tout<span class="n">'+CARDS.length+'</span></button>';for(const f of FAMILIES){const n=CARDS.filter(c=>c.fam===f.key).length;h+='<button class="chip '+(searchState.fam===f.key?'active':'')+'" onclick="setFam(\''+f.key+'\')" style="--ac:'+f.ac+'"><span class="sym">'+f.sym+'</span>'+f.short+'<span class="n">'+n+'</span></button>'}el.innerHTML=h}}

// events
document.getElementById('backBtn').onclick=closeDetail;
document.getElementById('sInput').oninput=e=>{searchState.q=e.target.value.trim().toLowerCase();searchState.selIdx=0;syncQuery();searchRender()};
document.getElementById('search').onclick=e=>{if(e.target.id==='search')closeSearch()};
document.getElementById('nuances').onclick=e=>{if(e.target.id==='nuances')closeNuances()};
document.addEventListener('keydown',e=>{if(e.metaKey||e.ctrlKey||e.altKey)return;const tag=e.target?.tagName;
  const sg=document.getElementById('search').classList.contains('open');
  if(e.key==='Escape'){if(sg)closeSearch();else if(document.getElementById('nuances').classList.contains('open'))closeNuances();else closeDetail();return}
  if(tag&&/INPUT|TEXTAREA|SELECT/i.test(tag))return;
  if(sg){if(e.key==='ArrowDown'){searchState.selIdx++;searchRender()}if(e.key==='ArrowUp'){searchState.selIdx=Math.max(0,searchState.selIdx-1);searchRender()}if(e.key==='Enter'){document.querySelector('#sGrid .mini.sel')?.click()}return}
  if(e.key.length===1&&/^[\p{L}\p{N}]$/u.test(e.key)){e.preventDefault();openSearch(e.key);return}
  if(currentIdx>=0){if(e.key==='ArrowLeft')openDetail(CARDS[(currentIdx-1+CARDS.length)%CARDS.length].sort);if(e.key==='ArrowRight')openDetail(CARDS[(currentIdx+1)%CARDS.length].sort)}
});

renderGrid();renderChips();
document.getElementById('loader').classList.add('gone');

// loupe hero (conservé)
(function(){const hero=document.querySelector('.d-hero'),loupe=document.getElementById('heroLoupe');if(!hero||!loupe)return;let active=false;hero.addEventListener('mouseenter',()=>{active=true;loupe.classList.add('active')});hero.addEventListener('mouseleave',()=>{active=false;loupe.classList.remove('active')});hero.addEventListener('mousemove',e=>{if(!active)return;const r=hero.getBoundingClientRect();loupe.style.left=(e.clientX-r.left)+'px';loupe.style.top=(e.clientY-r.top)+'px'})})();

// spreads
function buildTarot(){window.TAROT={families:FAMILIES.map(function(f){return{key:f.key,name:f.name,accent:f.ac||'#c9a227',cards:CARDS.filter(function(c){return c.fam===f.key}).map(function(c){const es=ES_MAP[c.id]||{};return{id:c.id,name:c.name,num:c.num,sort:c.sort,family:c.fam,familyName:f.name,element:f.el||'',file:IMG_MAP[c.id]||'',es:{reponse:es.rep||'',affirmation:es.aff||''}}})}})}}
buildTarot();if(window.TarotSpreads)TarotSpreads.init();
window.tarotOpenCard=function(card){if(window.TarotSpreads)TarotSpreads.closeSpread();if(typeof card.sort==='number')openDetail(card.sort)};
</script>
</body></html>
