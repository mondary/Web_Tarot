<?php
declare(strict_types=1);

// V9 — refonte grand format inspirée de V3, contenu éditorial de V8.
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
if (isset($_GET['deckimg'])) {
    $p = urldecode((string)$_GET['deckimg']);
    if (preg_match('#^[a-z0-9]+/[a-z0-9_ éèêëàâäîïôöûüçÉÈÊËÀÂÄÎÏÔÖÛÜÇ\'-]+\.jpe?g$#iu', $p)) Vault::image('/decks/' . $p);
    http_response_code(404); exit('Not found');
}
if (preg_match('#^/img/(.+)$#', $path, $m)) Vault::image('/img/' . $m[1]);
if (isset($_GET['font']) && preg_match('/^[a-z0-9-]+\.woff2$/', (string)$_GET['font'])) {
    Vault::image('/fonts/' . $_GET['font']);
}
if (isset($_GET['js']) && $_GET['js'] === 'spreads') {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    readfile(__DIR__ . '/tarot-spreads.js');
    exit;
}
if (isset($_GET['svg']) && preg_match('/^[a-z]+$/', (string)$_GET['svg'])) {
    Vault::image('/svg/' . $_GET['svg'] . '.svg');
}
// fichiers statiques PWA servis via PHP (o2switch bloque l'accès direct)
if (preg_match('#^/(manifest\.json|sw\.js|icon-\d+\.png)$#', $path, $m)) {
    $file = __DIR__ . '/' . $m[1];
    if (file_exists($file)) {
        $mime = match(pathinfo($file, PATHINFO_EXTENSION)) {
            'json' => 'application/manifest+json',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            default => 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    http_response_code(404); exit('Not found');
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
$ver = '2026.08.32';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5">
<title>Tarot Divinatoire</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔮</text></svg>">
<meta name="theme-color" content="#0a0907">
<style id="ff">@font-face{font-family:"Cormorant Garamond";font-style:normal;font-weight:400 600;src:url("<?= $base ?>/index.php?font=cormorant-garamond.woff2") format("woff2");font-display:swap}@font-face{font-family:"Cormorant Garamond";font-style:italic;font-weight:400 500;src:url("<?= $base ?>/index.php?font=cormorant-garamond-i.woff2") format("woff2");font-display:swap}@font-face{font-family:"DM Mono";font-style:normal;font-weight:400;src:url("<?= $base ?>/index.php?font=dm-mono-400.woff2") format("woff2");font-display:swap}@font-face{font-family:"DM Mono";font-style:normal;font-weight:500;src:url("<?= $base ?>/index.php?font=dm-mono-500.woff2") format("woff2");font-display:swap}</style>
</style>
</style>
</style>
<style>
:root{--bg:#0a0907;--panel:#14120e;--panel2:#1a1712;--line:#2a2620;--fg:#f1ede4;--muted:#8a8174;--ac:#c9a227;--ac-dim:rgba(201,162,39,.18);--mat:#fff;--hero:#0a0a0a;--overlay:rgba(10,9,7,.85);--overlay-soft:rgba(10,9,7,.7);--cap-line:rgba(0,0,0,.08);--ease:cubic-bezier(.22,1,.36,1);--spring:cubic-bezier(.34,1.56,.64,1)}
/* thèmes : nuit (défaut) / ivoire / sylve */
html[data-theme=ivoire]{--bg:#efe9dc;--panel:#e4dcc9;--panel2:#dad1bb;--line:#c9bfa6;--fg:#221c12;--muted:#6f6450;--ac:#8a6d1d;--ac-dim:rgba(138,109,29,.16);--mat:#fffdf7;--hero:#e7dfcd;--overlay:rgba(239,233,220,.9);--overlay-soft:rgba(239,233,220,.75);--cap-line:rgba(60,50,30,.14)}
html[data-theme=sylve]{--bg:#0a0f0b;--panel:#101812;--panel2:#16211a;--line:#23322a;--fg:#e8f0e6;--muted:#7e9180;--ac:#a3c98a;--ac-dim:rgba(163,201,138,.16);--mat:#fff;--hero:#0d130e;--overlay:rgba(10,15,11,.85);--overlay-soft:rgba(10,15,11,.7);--cap-line:rgba(0,0,0,.08)}
html[data-theme=ivoire] .fx-vignette{background:radial-gradient(120% 100% at 50% 0%,transparent 50%,rgba(60,50,30,.2) 100%)}
html[data-theme=ivoire] #loader,html[data-theme=ivoire] .topnav button,html[data-theme=ivoire] .back-btn,html[data-theme=ivoire] #autoFab,html[data-theme=ivoire] #kwFab{background:var(--overlay)}
html[data-theme=ivoire] .d-loop{background:linear-gradient(transparent,var(--overlay-soft) 40%)}
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:var(--bg);color:var(--fg);-webkit-font-smoothing:antialiased}
body{font-family:"Cormorant Garamond",Georgia,serif;font-size:1.06rem;line-height:1.55;min-height:100vh;overflow-x:hidden}
img{max-width:100%;display:block}
.mono{font-family:"DM Mono",monospace;letter-spacing:.12em;text-transform:uppercase}
em{font-style:italic;color:var(--ac)}
a{color:inherit}

/* loader + grain + vignette */
#loader{position:fixed;inset:0;background:var(--bg);z-index:2000;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:opacity .6s var(--ease)}
#loader.gone{opacity:0;pointer-events:none}
#loader .moon{font-size:clamp(60px,10vw,100px);line-height:1;color:var(--ac);animation:moonPulse 2s ease-in-out infinite,moonSpin 8s linear infinite;text-shadow:0 0 40px rgba(201,162,39,.5),0 0 80px rgba(201,162,39,.2)}
#loader .loader-text{font-family:"DM Mono",monospace;font-size:clamp(.6rem,1.4vw,.72rem);letter-spacing:.4em;text-transform:uppercase;color:var(--muted);margin-top:1.8rem;opacity:0;animation:loaderFadeUp 1s var(--ease) .5s forwards}
#loader .loader-bar{width:200px;height:1px;background:var(--ac-dim);margin-top:1.4rem;position:relative;overflow:hidden}
#loader .loader-bar::after{content:'';position:absolute;left:-100%;top:0;width:100%;height:100%;background:linear-gradient(90deg,transparent,var(--ac),transparent);animation:loaderSweep 1.5s ease-in-out infinite}
@keyframes moonPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.1);opacity:.7}}
@keyframes moonSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
@keyframes loaderSweep{0%{left:-100%}100%{left:100%}}
@keyframes loaderFadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.fx-grain{position:fixed;inset:0;pointer-events:none;z-index:1500;opacity:.035;mix-blend-mode:overlay;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.fx-vignette{position:fixed;inset:0;pointer-events:none;z-index:1400;background:radial-gradient(120% 100% at 50% 0%,transparent 50%,rgba(0,0,0,.55) 100%)}

/* brand + topnav */
.brand{position:fixed;top:1.2rem;left:1.5rem;z-index:1600;font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted)}
.brand b{color:var(--fg);font-weight:500} .brand em{font-style:italic;color:var(--ac);letter-spacing:.04em} .brand .v{color:var(--muted);opacity:.7;letter-spacing:.08em}
.topnav{position:fixed;top:1rem;right:1.5rem;z-index:1600;display:flex;gap:.4rem}
.topnav button{display:inline-flex;align-items:center;gap:.4rem;background:var(--overlay);backdrop-filter:blur(8px);border:1px solid var(--line);color:var(--muted);font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;padding:.5rem .8rem;border-radius:40px;cursor:pointer;transition:.3s var(--ease)}
.topnav button svg{height:16px;width:auto;fill:none;stroke:currentColor;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
.topnav button svg path{fill:currentColor;stroke:none}
.topnav button:hover{color:var(--ac);border-color:var(--ac)}

/* ===== LANDING : les 78 lames directement ===== */
#landing{max-width:1600px;margin:0 auto;padding:7rem 4vw 6rem;position:relative;z-index:100}
.landing-head{text-align:center;margin-bottom:3rem}
.seuil-title{font-family:"Cormorant Garamond",serif;font-weight:400;font-size:clamp(3rem,11vw,8rem);line-height:.95;letter-spacing:-.01em;margin:0}
.seuil-title em{font-style:italic}.seuil-title .split-word{display:inline-flex;overflow:hidden;padding-bottom:.08em}.seuil-title .split-word span{display:inline-block;transform:translateY(110%);transition:transform .55s var(--spring);transition-delay:calc(var(--i)*45ms)}.seuil-title.revealed .split-word span{transform:translateY(0)}
.seuil-sub{color:var(--muted);font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;margin:1.4rem 0 0}
@media(max-width:640px){#landing{padding:7.4rem .8rem 4rem}.landing-head{margin-bottom:2rem}.seuil-title{font-size:clamp(2.8rem,14vw,4.4rem)}.seuil-title em{display:block}.full-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}.mini .cap .nm{font-size:.9rem}.topnav{right:1rem}.topnav .tl{display:none}.topnav button{padding:.48rem}.brand{top:.8rem;left:.8rem}}

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
.full-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1.4rem;align-items:start}
.mini{display:block;width:100%;padding:0;color:inherit;font:inherit;text-align:left;cursor:pointer;border-radius:.95rem;overflow:hidden;position:relative;background:var(--mat);border:1px solid rgba(255,255,255,.35);box-shadow:0 10px 28px rgba(0,0,0,.38);transition:transform .5s var(--spring),box-shadow .5s var(--spring),border-color .35s var(--ease)}
.mini .ph{display:block;aspect-ratio:2/3;overflow:hidden;background:var(--mat);padding:.65rem .65rem .35rem;position:relative}
/* mots-clés au survol (desktop, activable) — pill flottante bas-gauche */
/* barre du bas : affichage (mots-clés, diaporama) */
.viewbar{position:fixed;left:1.5rem;bottom:1.4rem;z-index:1600;display:flex;gap:.4rem}
.viewbar button{display:inline-flex;align-items:center;gap:.5rem;background:var(--overlay);backdrop-filter:blur(8px);border:1px solid var(--line);color:var(--muted);font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;padding:.62rem 1rem;border-radius:40px;cursor:pointer;transition:.3s var(--ease)}
.viewbar button svg{height:16px;width:auto;fill:currentColor}
.viewbar button:hover{color:var(--ac);border-color:var(--ac)}
.viewbar button.on{color:var(--ac);border-color:var(--ac);background:var(--ac-dim);box-shadow:0 0 24px var(--ac-dim)}
#autoFab .ic-pause{display:none} #autoFab.on .ic-pause{display:inline} #autoFab.on .ic-play{display:none}
#autoFab .spd{display:inline-flex;gap:.15rem;margin-left:.3rem}
#autoFab .spd button{background:none;border:1px solid transparent;border-radius:3px;color:inherit;font:inherit;padding:.1rem .3rem;cursor:pointer}
#autoFab .spd button.cur{border-color:var(--ac);color:var(--ac)}
#autoFab{position:static}
/* panneau réglages : les 4 options dans un seul menu */
.settings-wrap{position:relative}
.settings-panel{position:absolute;bottom:calc(100% + .6rem);left:0;display:none;flex-direction:column;gap:.25rem;min-width:230px;padding:.55rem;background:var(--overlay);backdrop-filter:blur(12px);border:1px solid var(--line);border-radius:14px;box-shadow:0 18px 44px rgba(0,0,0,.55);z-index:1650}
.settings-panel.open{display:flex}
.settings-panel button{justify-content:flex-start;border-color:transparent;background:transparent;backdrop-filter:none;box-shadow:none;border-radius:9px;width:100%;padding:.6rem .8rem}
.settings-panel button:hover{background:var(--ac-dim);border-color:transparent}
.settings-panel button.on{background:var(--ac-dim);box-shadow:none}
.settings-panel .tl{display:inline}
.settings-panel .spd{margin-left:auto}
.settings-panel .set-ver{font-family:"DM Mono",monospace;font-size:.55rem;letter-spacing:.14em;color:var(--muted);opacity:.7;text-align:center;padding:.35rem 0 .15rem;border-top:1px solid var(--line);margin-top:.25rem}
@media(max-width:640px){.brand .v{display:none}}
.auto-ring{position:fixed;top:0;left:0;right:0;height:2px;z-index:1660;background:var(--ac-dim);opacity:0;transition:opacity .3s}
.auto-ring.on{opacity:1}
.auto-ring::after{content:'';position:absolute;top:0;bottom:0;left:0;width:var(--auto-p,0%);background:var(--ac);transition:width .12s linear}
@media(max-width:640px){
  .viewbar{left:50%;transform:translateX(-50%);bottom:calc(.7rem + env(safe-area-inset-bottom));width:max-content;max-width:calc(100vw - 1.6rem);gap:.3rem}
  .viewbar button{padding:.55rem .7rem;font-size:.55rem}
  .viewbar .tl{display:none}
  .viewbar #kwFab svg{margin-right:-.2rem}
  .viewbar #autoFab{gap:.35rem}
  .viewbar #autoFab .spd{display:none}
  .viewbar #autoFab .ic-play,.viewbar #autoFab .ic-pause{font-size:.7rem;line-height:1}
  .settings-panel{left:50%;transform:translateX(-50%)}
  .viewbar .settings-panel .tl{display:inline}
}
.kw-overlay{display:none}
body.kw .kw-overlay{position:absolute;inset:0;z-index:2;display:flex;align-items:center;justify-content:center;text-align:center;padding:1rem;background:linear-gradient(180deg,var(--overlay-soft),color-mix(in srgb,var(--bg) 94%,transparent));opacity:0;transform:scale(.92);transition:.25s var(--ease);pointer-events:none}
body.kw .mini:hover .kw-overlay,body.kw .mini:focus-visible .kw-overlay{opacity:1;transform:scale(1)}
@media(hover:none){body.kw .kw-overlay{opacity:1;transform:scale(1)}} /* tactile : pas de hover, toujours visible */
.kw-overlay .kw{font-family:"DM Mono",monospace;font-size:1.18rem;font-weight:500;letter-spacing:.12em;text-transform:uppercase;color:var(--ac);text-shadow:0 2px 14px rgba(0,0,0,.9)}
.kw-overlay .kn{position:absolute;right:1rem;bottom:1rem;left:1rem;font-family:"Cormorant Garamond",serif;font-size:1.35rem;font-weight:600;color:var(--ac);line-height:1.05}
.mini .ph img{width:100%;height:100%;object-fit:contain;border-radius:.55rem;transition:transform .6s var(--ease)}
.mini:hover{transform:translateY(-10px);border-color:var(--ac);box-shadow:0 18px 40px -12px rgba(0,0,0,.7),0 0 30px var(--ac-dim)}
.mini:focus-visible{outline:2px solid var(--ac);outline-offset:2px}
.mini.sel{border-color:var(--ac);box-shadow:0 0 0 2px var(--ac)}
.mini:hover .ph img{transform:scale(1.035)}
.mini .cap{display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.65rem .8rem .75rem;border-top:1px solid rgba(0,0,0,.08);background:var(--mat);font-family:"DM Mono",monospace;font-size:.58rem;letter-spacing:.08em;text-transform:uppercase}
.mini .cap .nm{color:#1c1814;font-family:"Cormorant Garamond",serif;font-size:1.05rem;font-weight:600;text-transform:none;letter-spacing:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mini .cap .no{color:#8b8175}
.family-intro{background:var(--panel);border-color:color-mix(in srgb,var(--family) 55%,var(--line))}.family-intro .ph{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.55rem;padding:1rem;text-align:center;background:var(--panel);aspect-ratio:2/3}
.family-intro .family-glyph{font-family:"Cormorant Garamond",serif;font-size:clamp(2rem,5vw,3.6rem);line-height:1;color:var(--family)}
.family-intro img.family-glyph{font-size:0;width:auto;height:clamp(3.6rem,8vw,5.2rem);object-fit:contain;filter:invert(1)}
.family-intro .family-name{font-family:"Cormorant Garamond",serif;font-style:italic;font-size:clamp(.9rem,2vw,1.2rem);line-height:1;color:var(--fg)}
.family-intro .family-element{font-family:"DM Mono",monospace;font-size:.48rem;letter-spacing:.14em;text-transform:uppercase;color:var(--family)}
.family-intro .cap{background:var(--panel);border-top-color:var(--line)}.family-intro .cap .nm{color:var(--family)}.family-intro .cap .no{color:var(--muted)}
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
.s-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem}
.s-empty{text-align:center;color:var(--muted);padding:3rem;font-style:italic}

/* ===== nuances ===== */
.nuances-body .nuc-cat{margin-bottom:1.8rem}
.nuances-body h3{font-family:"DM Mono",monospace;font-size:.66rem;letter-spacing:.14em;text-transform:uppercase;color:var(--ac);margin:0 0 .8rem;padding-bottom:.4rem;border-bottom:1px solid var(--line)}
.nuances-body ul{list-style:none;padding:0;margin:0}
.nuances-body li{display:flex;gap:.8rem;align-items:center;padding:.6rem 0;border-bottom:1px solid rgba(241,237,228,.04)}
.nuc-thumb{flex:0 0 auto;width:52px;aspect-ratio:2/3;border-radius:5px;overflow:hidden;border:1px solid var(--line);cursor:pointer;transition:.3s var(--ease)}
.nuc-thumb:hover{border-color:var(--ac);transform:translateY(-2px)}
.nuc-thumb img{width:100%;height:100%;object-fit:contain}
.nuc-card{color:var(--fg);font-weight:500} .nuc-key{color:var(--ac);font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.06em}
.nuc-desc{color:var(--muted);font-size:.92rem}

/* ===== DETAIL ===== */
.d-stage{position:fixed;inset:0;z-index:900;background:var(--bg);overflow-y:auto;overflow-x:hidden;display:none}
.d-stage.open{display:block}
.d-stage.slide-next .d-hero,.d-stage.slide-next .d-panel,.d-stage.slide-next .d-loop{animation:detail-next .34s var(--ease)}
.d-stage.slide-prev .d-hero,.d-stage.slide-prev .d-panel,.d-stage.slide-prev .d-loop{animation:detail-prev .34s var(--ease)}
@keyframes detail-next{from{opacity:0;transform:translateX(9vw)}to{opacity:1;transform:translateX(0)}}
@keyframes detail-prev{from{opacity:0;transform:translateX(-9vw)}to{opacity:1;transform:translateX(0)}}
.back-btn{position:fixed;top:1.2rem;left:1.5rem;z-index:1650;font-family:"DM Mono",monospace;font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);cursor:pointer;background:var(--overlay-soft);backdrop-filter:blur(8px);padding:.5rem .9rem;border-radius:40px;border:1px solid var(--line);transition:.3s}
.back-btn:hover{color:var(--ac);border-color:var(--ac)}
body:has(.d-stage.open) .brand{opacity:0;pointer-events:none}
/* diaporama : visible partout via viewbar */

/* HERO conservé */
.d-hero{position:relative;height:70vh;min-height:420px;width:100%;overflow:hidden;background:var(--hero)}
.d-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;filter:saturate(1.05) contrast(1.02)}
.d-hero::before{content:'';position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(180deg,color-mix(in srgb,var(--hero) 28%,transparent) 0%,transparent 22%,transparent 45%,color-mix(in srgb,var(--hero) 55%,transparent) 72%,var(--hero) 100%)}
.d-hero-loupe{position:absolute;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(201,162,39,.1),transparent 65%);border:1.5px solid rgba(201,162,39,.25);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity .35s ease-out;z-index:5;box-shadow:0 0 40px rgba(201,162,39,.08)}
.d-hero-loupe.active{opacity:1}
@media(hover:none){.d-hero-loupe{display:none}}
@media(max-width:640px){.d-hero{height:55vh;min-height:300px}}

.d-panel{position:relative;z-index:2;background:var(--hero);margin-top:-28px;border-radius:26px 26px 0 0;padding:34px 6% 120px;min-height:60vh}
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
.d-keys{display:grid;grid-template-columns:auto 1fr;gap:.55rem .9rem;margin-top:1.4rem;align-items:start}
.d-keys .kl{font-family:"DM Mono",monospace;font-size:.56rem;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);padding-top:.32rem;white-space:nowrap}
.d-keys .kv{display:flex;flex-wrap:wrap;gap:.35rem}
.d-keys .kt{font-family:"DM Mono",monospace;font-size:.58rem;letter-spacing:.04em;padding:.28rem .6rem;border-radius:40px;border:1px solid var(--line);color:#bdb5a4;background:var(--panel)}
.d-keys .up .kt{color:#cde8ce;border-color:rgba(129,199,132,.3);background:rgba(102,187,106,.07)}
.d-keys .dn .kt{color:#f0c3c3;border-color:rgba(229,115,115,.3);background:rgba(239,83,80,.07)}

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

/* ===== DIAPO AUTO ===== */
#autoFab .ic-pause{display:none} #autoFab.on .ic-pause{display:inline} #autoFab.on .ic-play{display:none}
#autoFab .spd{display:inline-flex;gap:.15rem;margin-left:.3rem}
#autoFab .spd button{background:none;border:1px solid transparent;border-radius:3px;color:inherit;font:inherit;padding:.1rem .3rem;cursor:pointer}
#autoFab .spd button.cur{border-color:var(--ac);color:var(--ac)}
.auto-ring{position:fixed;top:0;left:0;right:0;height:2px;z-index:1660;background:var(--ac-dim);opacity:0;transition:opacity .3s}
.auto-ring.on{opacity:1}
.auto-ring::after{content:'';position:absolute;top:0;bottom:0;left:0;width:var(--auto-p,0%);background:var(--ac);transition:width .12s linear}
.d-loop a{cursor:pointer;transition:.3s} .d-loop a:hover{color:var(--ac)}
.d-loop .pos b{color:var(--ac)}
.content-icon{display:inline-flex;width:20px;height:20px;vertical-align:middle;margin-right:.4rem}
.content-icon svg{width:100%;height:100%;fill:none;stroke:var(--ac);stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
/* Mode apprentissage : la lame reste le point focal ; les réponses forment un dock ancré en bas. */
#learn{overflow:hidden}
#learn .learn-sheet{display:flex;flex-direction:column;height:100dvh;max-width:760px;margin:0 auto;padding:0;position:relative}
#learn .overlay-close{top:.55rem;right:.7rem;left:auto;width:32px;height:32px;font-size:1rem}
.learn-head{display:flex;justify-content:center;gap:.7rem;align-items:baseline;white-space:nowrap;font-family:"DM Mono",monospace;font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);padding:.72rem 3.1rem .42rem}
.learn-title{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1rem;text-transform:none;letter-spacing:0;color:var(--fg)}
.learn-count b,.learn-hits b{color:var(--ac);font-weight:500}
.learn-hits{color:var(--ac)}
.learn-bar{height:2px;background:var(--line);flex:0 0 auto}
.learn-bar i{display:block;height:100%;background:var(--ac);width:0;transition:width .4s var(--ease)}
.learn-stage{flex:1 1 auto;display:flex;min-height:0;padding:.7rem .9rem .55rem}
.learn-card{flex:1 1 auto;min-height:0;display:flex;align-items:center;justify-content:center}
.learn-card img{max-width:100%;max-height:100%;width:auto;height:100%;object-fit:contain;background:var(--mat);border-radius:6px;box-shadow:0 14px 30px rgba(0,0,0,.5)}
.learn-dock{flex:0 0 auto;position:relative;z-index:2;padding:.55rem .9rem calc(.65rem + env(safe-area-inset-bottom));background:linear-gradient(180deg,transparent,var(--bg) 18%)}
.learn-mode{display:flex;justify-content:center;gap:.35rem;margin:0 0 .5rem}
.learn-mode button{border:1px solid var(--line);border-radius:40px;background:rgba(20,18,14,.9);color:var(--muted);font-family:"DM Mono",monospace;font-size:.56rem;letter-spacing:.08em;text-transform:uppercase;padding:.35rem .65rem;cursor:pointer;transition:.2s var(--ease)}
.learn-mode button:hover,.learn-mode button[aria-pressed="true"]{color:var(--fg);border-color:var(--ac);background:var(--ac-dim)}
.learn-opts{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.55rem}
.learn-opt{font-family:"DM Mono",monospace;font-size:.66rem;letter-spacing:.1em;text-transform:uppercase;color:var(--fg);background:var(--panel);border:1px solid var(--line);border-radius:8px;padding:.7rem .6rem;cursor:pointer;transition:.25s var(--ease)}
.learn-opt:hover:not(:disabled){border-color:var(--ac);color:var(--ac);transform:translateY(-2px)}
.learn-opt:disabled{cursor:default}
.learn-opt.right{border-color:rgba(129,199,132,.6);background:rgba(102,187,106,.12);color:#cde8ce}
.learn-opt.wrong{border-color:rgba(229,115,115,.6);background:rgba(239,83,80,.12);color:#f0c3c3}
.learn-opt .n{display:none}
/* drawer de réponse : coulisse depuis le bas au-dessus des options */
.learn-drawer{position:absolute;left:0;right:0;bottom:0;background:#0d0c09;border-top:1px solid var(--line);border-radius:18px 18px 0 0;padding:1.2rem 1.2rem calc(1.2rem + env(safe-area-inset-bottom));transform:translateY(105%);transition:transform .4s var(--ease);z-index:10;max-height:70%;overflow-y:auto;box-shadow:0 -20px 50px rgba(0,0,0,.6)}
.learn-drawer.open{transform:translateY(0)}
.learn-drawer h3{font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:500;margin:0 0 .5rem;text-align:center}
.learn-drawer p{color:var(--muted);font-size:.92rem;line-height:1.55;margin:0 0 1rem;text-align:center}
.learn-next{display:block;margin:0 auto;font-family:"DM Mono",monospace;font-size:.64rem;letter-spacing:.14em;text-transform:uppercase;color:var(--ac);background:var(--ac-dim);border:1px solid var(--ac);border-radius:40px;padding:.75rem 1.8rem;cursor:pointer;transition:.25s}
.learn-next:hover{background:var(--ac);color:var(--bg)}
.learn-end{margin:auto;text-align:center;display:none;padding:1rem}
.learn-end.show{display:block}
.learn-end h3{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:2rem;color:var(--ac);margin:0 0 .8rem;font-weight:500}
.learn-end .score{font-family:"DM Mono",monospace;font-size:.7rem;letter-spacing:.12em;color:var(--fg);margin-bottom:1.4rem}
.learn-end .missed{color:var(--muted);font-size:.85rem;line-height:1.7;margin:0 0 1.6rem}
.learn-end .missed b{color:#f0c3c3;font-weight:400}
@media(max-width:640px){
  .learn-stage{padding:.55rem .7rem .4rem}
  .learn-dock{padding:.45rem .7rem calc(.5rem + env(safe-area-inset-bottom))}
  .learn-opts{grid-template-columns:1fr;gap:.38rem}
  .learn-opt{font-size:.7rem;padding:.74rem .85rem;text-align:left;min-height:44px}
  .learn-opt .n{display:inline;color:var(--muted);margin-right:.5rem}
  .learn-head{font-size:.54rem;padding:.62rem 2.8rem .34rem;gap:.55rem}
  .learn-title{font-size:.92rem}
  .learn-mode{margin-bottom:.38rem}
}
@media(max-width:640px){.full-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}.mini .ph{padding:.45rem .45rem .25rem}.mini .cap{padding:.5rem .55rem .6rem}.mini .cap .nm{font-size:.9rem}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}.seuil-title .split-word span{transform:none}.mini:hover{transform:none}}
</style>
<link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" href="icon-512.png">
<meta name="mobile-web-app-capable" content="yes">
</head>
<body>
<div id="loader"><div class="moon">☽</div><div class="loader-text">Entrez dans le mystère</div><div class="loader-bar"></div></div>
<div class="fx-grain"></div><div class="fx-vignette"></div>
<div class="brand"><b>TAROT</b> <em>DIVINATOIRE</em><span class="v"> · v<?= $ver ?></span></div>
<nav class="topnav">
  <button aria-label="Tirages" onclick="TarotSpreads&&TarotSpreads.open()"><svg viewBox="0 0 162 154" fill="currentColor"><path d="M41.3 12.5C16 18.4 13.9 19.2 10.5 23.2c-3.4 4.1-3.9 8.4-2 17.9 3 14.8 17.5 80.3 18.6 83.6.6 1.8 2.7 4.6 4.6 6.4l3.7 3.2 9.5-.8c16.9-1.3 17.3-1.2 38.1 6 28.4 9.9 35.9 10 43.3.3 5.3-6.9 28.7-72.8 28.7-80.7q0-8.5-7.5-11.7c-2.9-1.2-3.3-1.8-3.3-5.3q-.2-8.3-6.7-11.5a81 81 0 0 0-19-4.6c-1.2 0-2.7-1.3-3.7-3-2.5-4.5-7.3-6-21.5-6.9-12.2-.7-12.6-.8-14.6-3.6a15 15 0 0 0-11.9-5.4c-1.8.1-13.3 2.5-25.5 5.4m32.8 2.6c1 1.3 2 3.5 2.3 4.9 10.5 48.8 18.6 90.1 18.1 92.7a10 10 0 0 1-3.1 5.2C87.5 121 51.2 129 41.3 129c-6.7 0-8.5-2.5-11.7-16.1C21.4 78 12 34.4 12 31.2c0-1.3 1.2-3.6 2.8-5.1 2.3-2.3 5.5-3.4 20.2-6.9l23.5-5.7c7.9-1.9 13.4-1.3 15.6 1.6m22.6 6.3c10.5.6 12.8 1.7 14.4 6.4 1.1 3.5.1 31.1-2.7 72.5-1.5 22.5-2.4 26.5-6.3 28.7a71 71 0 0 1-22.2.3l-6.4-.6 9.5-2.3c8.1-2 10-2.9 12.7-5.8 5.6-6 5.5-6.6-4.6-55.1L82 20.7c0-.3 1.2-.5 2.8-.2zm29.7 11.1c13.6 2.9 14.7 5 11.1 22.4-2.8 13.5-14.1 58.7-17.5 70.1-3.3 10.9-8.5 13.8-20.5 11.1l-3-.7 3.6-.7c4.4-.9 8.4-3.9 10.4-7.7 1-1.9 2-10.1 3-25.7 1.7-26.2 3.5-59.8 3.5-66.1 0-4.8-.2-4.7 9.4-2.7"/></svg><span class="tl">Tirages</span></button>
  <button aria-label="Nuances" onclick="openNuances()"><svg viewBox="0 0 100 132" fill="currentColor"><path fill-rule="evenodd" d="M55.3 5.8C51.1 17.2 42.9 31.7 27.9 53.2 16.5 69.6 11.2 81.8 11.2 92.1c0 18.3 15.4 31.8 35.7 32.3 21 .5 38.2-12.8 38.2-33 0-11.2-5.7-24-16.2-39.9C60.9 39.6 57.7 24.4 55.3 5.8m-.1 16.7c-4.6 10.2-11.8 22.9-22.1 37.8-8.5 12.3-13 22.2-13 31 0 14.9 12.3 25.1 26.9 25.5 15.3.4 29.2-10.9 29.2-26.1 0-9.6-4.9-20.3-13.7-34-5.6-9.3-6.3-21.2-7.3-34.2M44 100a5.2 5.2 0 1 0 .1 0z"/></svg><span class="tl">Nuances</span></button>
  <button aria-label="Apprendre" onclick="openLearn()"><svg viewBox="0 0 576 512" aria-hidden="true"><path d="M288 0 0 144l288 144 236.1-118.1V352H576V144L288 0zm-184.4 266.2L96 416c80 42.7 304 42.7 384 0l-7.6-149.8L288 358.4 103.6 266.2z"/></svg><span class="tl">Apprendre</span></button>
  <button aria-label="Recherche" onclick="openSearch()"><svg viewBox="0 0 135 131" fill="currentColor"><path d="M39.8 8.9A47.5 47.5 0 0 0 7 54c0 13.1 4.3 23.5 13.4 32.5A46 46 0 0 0 54.1 100c11.4 0 21.4-3.6 31.3-11.4.8-.5 7.4 5.5 18.6 16.9 9.6 9.7 18.3 18 19.3 18.6 2.4 1.4 5.9-.7 5.5-3.2-.2-1.1-8.6-10.2-18.8-20.4L91.6 82l1.7-3a58 58 0 0 0 6.4-21.6 46.5 46.5 0 0 0-34.1-49 55 55 0 0 0-25.8.5m29.7 8.9a39 39 0 0 1 22.1 44.1c-6.4 30.8-42.2 41.4-65.7 19.4-18.2-17-13.6-48.8 9-62.1a37 37 0 0 1 34.6-1.4"/></svg><span class="tl">Recherche</span></button>
</nav>
<div class="viewbar" id="viewbar">
  <div class="settings-wrap">
    <button id="setFab" aria-expanded="false" aria-haspopup="true" aria-label="Réglages d'affichage" onclick="toggleSettings(event)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg><span class="tl">Réglages</span></button>
    <div class="settings-panel" id="setPanel">
      <button aria-label="Thème" id="themeBtn" onclick="cycleTheme()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 0 18c.4 0 .8 0 1.2-.1a7 7 0 0 1 0-17.8c-.4-.1-.8-.1-1.2-.1Z"/></svg><span class="tl" id="themeLbl">Nuit</span></button>
      <button aria-label="Type de cartes" id="deckBtn" onclick="cycleDeck()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12a1 1 0 0 1 1 1v16.5a1 1 0 0 1-1.4.9L12 18l-5.6 2.4a1 1 0 0 1-1.4-.9V3a1 1 0 0 1 1-1Zm5 11.4 2.5-1.3 2.5 1.3-.5-2.8 2-2-2.8-.4L13.5 7.6l-1.2 2.6-2.8.4 2 2-.5 2.8Z"/></svg><span class="tl" id="deckLbl">RWS</span></button>
      <button id="kwFab" aria-pressed="false" aria-label="Mots-clés au survol" onclick="toggleKw()"><svg viewBox="0 0 24 24"><path d="M14.5 3a6.5 6.5 0 0 0-6.32 8.02L2.3 16.9a1 1 0 0 0-.3.7V21a1 1 0 0 0 1 1h2.5a1 1 0 0 0 1-1v-1.5H8a1 1 0 0 0 1-1v-1.5h1.5a1 1 0 0 0 .7-.3l.28-.28A6.5 6.5 0 1 0 14.5 3Zm2 6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>Mots-clés</button>
      <button id="autoFab" aria-pressed="false" aria-label="Diaporama automatique"><span class="ic-play">▶</span><span class="ic-pause">❚❚</span> Diaporama<span class="spd" id="autoSpd"></span></button>
      <div class="set-ver">v<?= $ver ?></div>
    </div>
  </div>
</div>

<div id="landing">
  <div class="landing-head">
    <h1 class="seuil-title"><span class="split-word" aria-label="Tarot"><span style="--i:0">T</span><span style="--i:1">a</span><span style="--i:2">r</span><span style="--i:3">o</span><span style="--i:4">t</span></span> <em>Divinatoire</em></h1>
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

<div id="learn" class="overlay">
  <div class="learn-sheet">
    <button class="overlay-close" onclick="closeLearn()">×</button>
    <div class="learn-head"><span class="learn-title">Apprendre</span><span class="learn-count"><b id="learnDone">0</b>/<span id="learnTotal">78</span> étudiées</span><span class="learn-hits"><b id="learnHits">0</b> bonnes</span></div>
    <div class="learn-bar"><i id="learnBar"></i></div>
    <div class="learn-stage" id="learnStage"><div class="learn-card"><img id="learnImg" alt="Lame à identifier"></div></div>
    <div class="learn-dock" id="learnDock">
      <div class="learn-mode" aria-label="Type de réponse">
        <button type="button" id="learnModeKey" aria-pressed="true" onclick="setLearnMode('key')">Mots-clés</button>
        <button type="button" id="learnModePhrase" aria-pressed="false" onclick="setLearnMode('phrase')">Phrases</button>
      </div>
      <div class="learn-opts" id="learnOpts"></div>
    </div>
    <div class="learn-end" id="learnEnd"></div>
    <div class="learn-drawer" id="learnDrawer"></div>
  </div>
</div>

 <div class="d-stage" id="detail">
  <div class="back-btn" id="backBtn">← Retour</div>
  <div class="auto-ring" id="autoRing"></div>
  <div class="d-hero"><img class="d-hero-img" id="heroImg" alt=""><div class="d-hero-loupe" id="heroLoupe"></div></div>
  <div class="d-panel"><div class="d-panel-inner" id="dInner"></div></div>
  <div class="d-loop" id="loopBar"></div>
</div>

<script src="<?= $base ?>/index.php?js=spreads"></script>
<script>
const B=<?= $baseJson ?>,CARDS=<?= $cardsJson ?>,FAMILIES=<?= $familiesJson ?>,ES_MAP=<?= $esJson ?>,ASSOCS=<?= $assocsJson ?>,PORTRAITS=<?= $portraitsJson ?>,IMG_MAP={};
const V=<?= (string)@filemtime(__DIR__.'/vault.sqlite') ?>;
for(const c of CARDS) IMG_MAP[c.id]=B+'/index.php?img='+encodeURIComponent(c.id+'.jpg')+'&v='+V;
// decks : rws (défaut) / clm (perso)
const DECKS=[{k:'rws',l:'RWS'},{k:'clm',l:'CLM'},{k:'marseille',l:'Marseille'}];
let DECK=(localStorage.getItem('tarotDeck')||'rws');
function deckUrl(id){return DECK!=='rws'?B+'/index.php?deckimg='+DECK+'/'+encodeURIComponent(id+'.jpg')+'&v='+V:IMG_MAP[id]}
function applyDeck(){document.getElementById('deckLbl').textContent=(DECKS.find(x=>x.k===DECK)||DECKS[0]).l;document.querySelectorAll('img[data-card]').forEach(im=>{im.src=deckUrl(im.dataset.card)});}
function cycleDeck(){const i=DECKS.findIndex(x=>x.k===DECK);DECK=DECKS[(i+1)%DECKS.length].k;try{localStorage.setItem('tarotDeck',DECK)}catch(e){}applyDeck()}
let currentIdx=-1,detailSlideTimer=0,searchState={fam:'',q:'',selIdx:0};

const PICTOS={
 amour:'<svg viewBox="0 0 24 24"><path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z"/></svg>',
 travail:'<svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg>',
 finance:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4"/></svg>',
 guidance:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z"/></svg>',
 signification:'<svg viewBox="0 0 24 24"><path d="m12 3 1.7 5.3H19l-4.3 3.1 1.7 5.3-4.4-3.2-4.4 3.2 1.7-5.3L5 8.3h5.3L12 3Z"/></svg>',
 description:'<svg viewBox="0 0 24 24"><path d="M3 5.5c3.7-1.4 6.8-.8 9 1.2 2.2-2 5.3-2.6 9-1.2v13c-3.7-1.4-6.8-.8-9 1.2-2.2-2-5.3-2.6-9-1.2v-13Z"/><path d="M12 6.7v13"/></svg>'
};
function fam(k){return FAMILIES.find(f=>f.key===k)||{}}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function splitKw(s){return String(s||'').split(',').map(x=>x.trim()).filter(Boolean)}
function parseDoc(h){const d=document.createElement('div');d.innerHTML=h;return d}
function secBy(doc,kw){for(const s of doc.querySelectorAll('section')){const t=(s.querySelector('h2')?.textContent||'').toLowerCase();if(t.includes(kw))return s}return null}
function secParas(s){return s?Array.from(s.querySelectorAll('p')).map(p=>'<p>'+p.innerHTML+'</p>').join(''):''}
function secText(s){return s?(s.querySelector('p')?.textContent||''):''}

function parsePortrait(md){const o={key:'',idee:'',realite:''};if(!md)return o;for(const line of md.split('\n').map(l=>l.trim())){if(!line)continue;if(line.startsWith('🧠'))o.idee=line.replace(/^🧠\s*Idée centrale\s*:\s*/i,'').replace(/^🧠\s*/,'').trim();else if(line.startsWith('💭'))o.realite=line.replace(/^💭\s*Ce qui se passe réellement\s*:\s*/i,'').replace(/^💭\s*/,'').trim();else if(line.startsWith('🔑'))o.key=line.replace(/^🔑\s*Mot-clé(?: distinctif)?\s*:\s*/i,'').replace(/^🔑\s*/,'').trim()}return o}

// mots-clés au survol (desktop) — toggle persistant
const KW={};for(const c of CARDS){const p=parsePortrait(PORTRAITS[c.id]||'');if(p.key)KW[c.id]=p.key}
function toggleKw(){const on=document.body.classList.toggle('kw');const b=document.getElementById('kwFab');if(b){b.setAttribute('aria-pressed',String(on));b.classList.toggle('on',on)}try{localStorage.setItem('tarotKw',on?'1':'0')}catch(e){}}
if(localStorage.getItem('tarotKw')==='1')toggleKw();

// panneau réglages (toutes les options d'affichage en un menu)
function toggleSettings(e){e.stopPropagation();const p=document.getElementById('setPanel');const open=p.classList.toggle('open');document.getElementById('setFab').setAttribute('aria-expanded',String(open))}
document.addEventListener('click',e=>{const p=document.getElementById('setPanel');if(p&&p.classList.contains('open')&&!e.target.closest('.settings-wrap'))p.classList.remove('open')});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){const p=document.getElementById('setPanel');if(p)p.classList.remove('open')}});

const DOMAINS=[{kw:'amour',label:'Amour'},{kw:'travail',label:'Travail'},{kw:'finance',label:'Finances'},{kw:'guidance',label:'Guidance'}];

function openDetail(sort,dir=0){
  const i=CARDS.findIndex(c=>c.sort===sort);if(i<0)return;const detail=document.getElementById('detail'),wasOpen=detail.classList.contains('open');currentIdx=i;
  const c=CARDS[i],f=fam(c.fam),es=ES_MAP[c.id]||{},inFam=CARDS.filter(x=>x.fam===c.fam),fi=inFam.findIndex(x=>x.id===c.id);
  const num=String(i+1).padStart(2,'0'),response=String(es.rep||'').trim().toUpperCase(),answer=['OUI','NON','PEUT-ÊTRE','PAS ENCORE'].includes(response)?response:'';
  const p=parsePortrait(PORTRAITS[c.id]||'');
  document.getElementById('heroImg').src=deckUrl(c.id);document.getElementById('heroImg').dataset.card=c.id;

  const doc=parseDoc(c.html||'');
  const ansClass=answer?('ans-'+answer.toLowerCase().replace(/\s+/g,'-')):'';

  // bandeau identité
  let ident='<section class="d-identite">';
  if(p.idee)ident+='<p class="d-idee">'+escapeHtml(p.idee)+'</p>';
  if(p.realite)ident+='<p class="d-realite">'+escapeHtml(p.realite)+'</p>';
  if(p.key||answer){ident+='<div class="d-badges">';if(p.key)ident+='<span class="d-key">'+escapeHtml(p.key)+'</span>';if(answer)ident+='<span class="d-answer '+ansClass+'">'+escapeHtml(answer)+'</span>';ident+='</div>'}
  const up=splitKw(c.keywords_up),dn=splitKw(c.keywords_down);
  if(up.length||dn.length){ident+='<div class="d-keys">';
    if(up.length)ident+='<span class="kl">Endroit</span><span class="kv up">'+up.map(k=>'<span class="kt">'+escapeHtml(k)+'</span>').join('')+'</span>';
    if(dn.length)ident+='<span class="kl">Envers</span><span class="kv dn">'+dn.map(k=>'<span class="kt">'+escapeHtml(k)+'</span>').join('')+'</span>';
    ident+='</div>'}
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

  const thumbs=inFam.map(x=>'<div class="d-thumb'+(x.id===c.id?' current':'')+'" onclick="openDetail('+x.sort+')"><img data-card="'+x.id+'" src="'+deckUrl(x.id)+'"></div>').join('');

  document.getElementById('dInner').innerHTML=
    '<div class="d-meta"><b>'+num+'</b> / '+CARDS.length+' <span style="opacity:.4">·</span> '+f.name+' '+(fi+1)+'/'+inFam.length+(f.el?' <span style="opacity:.4">·</span> '+f.el:'')+'</div>'+
    '<h1 class="d-title"><em>'+c.name+'</em></h1>'+
    ident+doms+cols+citation+assocsBlock+
    '<div class="d-thumbs">'+thumbs+'</div>';

  if(assocs){const n=(ASSOCS[c.id]||[]).length;const el=document.getElementById('assocsCount');if(el)el.textContent=n+' combinaison'+(n>1?'s':'')}

  const prev=CARDS[(i-1+CARDS.length)%CARDS.length],next=CARDS[(i+1)%CARDS.length];
  document.getElementById('loopBar').innerHTML='<a onclick="openDetail('+prev.sort+',-1)">← '+prev.name+'</a><span class="pos"><b>'+num+'</b> / '+CARDS.length+'</span><a onclick="openDetail('+next.sort+',1)">'+next.name+' →</a>';
  detail.classList.add('open');detail.scrollTop=0;
  if(dir&&wasOpen){clearTimeout(detailSlideTimer);detail.classList.remove('slide-next','slide-prev');void detail.offsetWidth;detail.classList.add(dir>0?'slide-next':'slide-prev');detailSlideTimer=setTimeout(()=>detail.classList.remove('slide-next','slide-prev'),340)}
}
function closeDetail(){document.getElementById('detail').classList.remove('open');currentIdx=-1;stopAuto()}

// diaporama auto : avance les lames, pause/reprise, vitesse 5/8/12 s
const AUTO_SPEEDS=[5,8,12];let autoTimer=0,autoT0=0,autoTick=0,autoDur=8;function autoRing(p){document.documentElement.style.setProperty('--auto-p',(p*100).toFixed(1)+'%')}
function autoStep(){autoTick+=.12;autoRing(Math.min(1,autoTick/autoDur));if(autoTick>=autoDur){autoNext()}}
function autoNext(){autoTick=0;autoRing(0);openDetail(CARDS[(currentIdx+1)%CARDS.length].sort,1)}
function startAuto(){if(currentIdx<0)openDetail(CARDS[0].sort,1);document.body.classList.add('auto-running');document.getElementById('autoFab').classList.add('on');document.getElementById('autoFab').setAttribute('aria-pressed','true');document.getElementById('autoRing').classList.add('on');autoTick=0;clearInterval(autoTimer);autoTimer=setInterval(autoStep,120)}
function stopAuto(){clearInterval(autoTimer);autoTimer=0;autoTick=0;autoRing(0);document.body.classList.remove('auto-running');const b=document.getElementById('autoFab');if(b){b.classList.remove('on');b.setAttribute('aria-pressed','false')}document.getElementById('autoRing').classList.remove('on')}
function toggleAuto(){autoTimer?stopAuto():startAuto()}
(function(){const fab=document.getElementById('autoFab'),spd=document.getElementById('autoSpd');
fab.addEventListener('click',e=>{if(e.target.closest('.spd'))return;toggleAuto();document.getElementById('setPanel').classList.remove('open')});
spd.innerHTML=AUTO_SPEEDS.map(s=>'<button data-s="'+s+'"'+(s===autoDur?' class="cur"':'')+'>'+s+'s</button>').join('');
spd.addEventListener('click',e=>{const b=e.target.closest('button');if(!b)return;autoDur=+b.dataset.s;autoTick=0;spd.querySelectorAll('button').forEach(x=>x.classList.toggle('cur',x===b))});
document.getElementById('detail').addEventListener('click',e=>{if(autoTimer&&!e.target.closest('a,button,#autoFab,.d-thumb'))stopAuto()},true);
window.addEventListener('keydown',e=>{if(e.key===' '&&autoTimer&&currentIdx>=0&&!/INPUT|TEXTAREA|SELECT/i.test(document.activeElement.tagName)){e.preventDefault();stopAuto()}})})();

function assocCard(t){if(!t)return null;let cs=[t];if(t.indexOf('/')>=0)cs=cs.concat(t.split('/').map(s=>s.trim()));for(const cand of cs){const n=cand.toLowerCase().replace(/[\u2018\u2019]/g,"'");for(const c of CARDS){if(c.name.toLowerCase()===n)return c}}return null}
function renderAssociations(rows){if(!rows||!rows.length)return'';const secs={},order=[];for(const r of rows){if(!secs[r.section]){secs[r.section]={it:[]};order.push(r.section)}secs[r.section].it.push(r)}let h='';for(const k of order){const s=secs[k];h+='<div class="association-section"><h3>'+k+'</h3><ul>';for(const r of s.it){const target=(r.pair||'').indexOf(' + ')>=0?(r.pair.split(' + ')[1]||'').trim():(r.pair||'');const card=assocCard(target);const thumb=card?'<a class="assoc-thumb" onclick="openDetail('+card.sort+')">'+(card?'<img data-card="'+card.id+'" src="'+deckUrl(card.id)+'">':'')+'</a>':'';const link=card?'<a class="assoc-link" onclick="openDetail('+card.sort+')">'+target+'</a>':'<span class="assoc-link">'+target+'</span>';h+='<li>'+thumb+'<div class="assoc-text">'+link+'<p>'+(r.descr||'')+'</p></div></li>'}h+='</ul></div>'}return h}

const NUANCES=[
 {e:'🚶',t:'Partir / changer / aller ailleurs',i:[
  {id:'e_06_Six',c:'⚔️ 6 Épées',k:'TRANSITION',d:'je quitte une difficulté pour aller vers plus calme.'},
  {id:'c_08_Huit',c:'🏆 8 Coupes',k:'RENONCEMENT',d:'je quitte volontairement quelque chose qui ne me satisfait plus.'},
  {id:'b_03_Trois',c:'🪾 3 Bâtons',k:'EXPANSION',d:'je m\'ouvre à de nouveaux horizons.'},
  {id:'a_10_Roue_de_Fortune',c:'🎡 Roue',k:'CHANGEMENT',d:'les circonstances changent, indépendamment de moi.'},
  {id:'a_13_Mort',c:'💀 Mort',k:'FIN',d:'quelque chose doit réellement se terminer pour laisser place à autre chose.'}
 ]},
 {e:'🛡️',t:'Difficulté / lutte / tenir',i:[
  {id:'b_05_Cinq',c:'🪾 5 Bâtons',k:'COMPÉTITION',d:'plusieurs volontés s\'affrontent.'},
  {id:'b_07_Sept',c:'🪾 7 Bâtons',k:'DÉFENSE',d:'ma position est attaquée, je la défends.'},
  {id:'b_09_Neuf',c:'🪾 9 Bâtons',k:'RÉSISTANCE',d:'j\'ai déjà pris des coups, mais je tiens.'},
  {id:'a_08_Force',c:'🦁 Force',k:'MAÎTRISE',d:'je domine une difficulté sans brutalité.'},
  {id:'e_05_Cinq',c:'⚔️ 5 Épées',k:'VICTOIRE AMÈRE',d:'je gagne le conflit mais j\'y laisse quelque chose.'}
 ]},
 {e:'😣',t:'Souffrance / difficulté',i:[
  {id:'e_08_Huit',c:'⚔️ 8 Épées',k:'ENFERMEMENT',d:'je me crois sans issue.'},
  {id:'e_09_Neuf',c:'⚔️ 9 Épées',k:'ANGOISSE',d:'je me torture avec mes pensées.'},
  {id:'e_10_Dix',c:'⚔️ 10 Épées',k:'FOND',d:'le pire est arrivé.'},
  {id:'d_05_Cinq',c:'🪙 5 Deniers',k:'MANQUE',d:'je suis dans le besoin et me sens laissé dehors.'},
  {id:'b_10_Dix',c:'🪾 10 Bâtons',k:'SURCHARGE',d:'j\'en porte tellement que je m\'épuise.'},
  {id:'c_05_Cinq',c:'🏆 5 Coupes',k:'REGRET',d:'je souffre de ce que j\'ai perdu.'}
 ]},
 {e:'🎉',t:'Bonheur / réussite / accomplissement',i:[
  {id:'c_03_Trois',c:'🏆 3 Coupes',k:'AMITIÉ',d:'je profite d\'être avec mes proches.'},
  {id:'b_04_Quatre',c:'🪾 4 Bâtons',k:'JALON',d:'une étape est franchie.'},
  {id:'b_06_Six',c:'🪾 6 Bâtons',k:'RECONNAISSANCE',d:'ma réussite est reconnue par les autres.'},
  {id:'c_09_Neuf',c:'🏆 9 Coupes',k:'SATISFACTION',d:'j\'ai obtenu ce que je désirais.'},
  {id:'c_10_Dix',c:'🏆 10 Coupes',k:'BONHEUR PARTAGÉ',d:'nous sommes heureux ensemble.'},
  {id:'d_09_Neuf',c:'🪙 9 Deniers',k:'INDÉPENDANCE',d:'je profite de ce que j\'ai construit.'},
  {id:'d_10_Dix',c:'🪙 10 Deniers',k:'HÉRITAGE',d:'ma réussite devient durable et transmissible.'},
  {id:'a_19_Soleil',c:'☀️ Soleil',k:'CLARTÉ',d:'tout est ouvert, lumineux, évident.'},
  {id:'a_21_Monde',c:'🌍 Monde',k:'ACCOMPLISSEMENT',d:'le parcours est arrivé à complétude.'}
 ]},
 {e:'👁️',t:'Comprendre / voir / savoir',i:[
  {id:'e_01_As',c:'⚔️ As Épées',k:'RÉVÉLATION',d:'je comprends soudainement.'},
  {id:'e_13_Reine',c:'⚔️ Reine Épées',k:'LUCIDITÉ',d:'je vois la situation telle qu\'elle est.'},
  {id:'e_14_Roi',c:'⚔️ Roi Épées',k:'JUGEMENT',d:'je tranche à partir de ce que je sais.'},
  {id:'a_02_Papesse',c:'📖 Papesse',k:'SAVOIR CACHÉ',d:'quelque chose est là mais n\'est pas encore révélé.'},
  {id:'a_18_Lune',c:'🌕 Lune',k:'CONFUSION',d:'je ne sais pas distinguer clairement ce qui est réel.'},
  {id:'a_19_Soleil',c:'☀️ Soleil',k:'CLARTÉ',d:'tout est visible, il n\'y a plus d\'ambiguïté.'},
  {id:'a_09_Hermite',c:'🕯️ Hermite',k:'RECHERCHE',d:'je cherche moi-même la réponse.'}
 ]},
 {e:'💞',t:'Lien / relation aux autres',i:[
  {id:'c_02_Deux',c:'🏆 2 Coupes',k:'RÉCIPROCITÉ',d:'toi et moi échangeons quelque chose mutuellement.'},
  {id:'a_06_Amoureux',c:'❤️ Amoureux',k:'UNION',d:'deux êtres s\'unissent.'},
  {id:'c_03_Trois',c:'🏆 3 Coupes',k:'AMITIÉ',d:'j\'appartiens à un cercle affectif.'},
  {id:'c_10_Dix',c:'🏆 10 Coupes',k:'BONHEUR PARTAGÉ',d:'le lien devient foyer/bonheur collectif.'},
  {id:'d_03_Trois',c:'🪙 3 Deniers',k:'COLLABORATION',d:'nous réunissons nos compétences.'},
  {id:'d_06_Six',c:'🪙 6 Deniers',k:'AIDE',d:'l\'un donne ce dont l\'autre a besoin.'}
 ]},
 {e:'🧱',t:'Construire / avoir / sécuriser',i:[
  {id:'d_01_As',c:'🪙 As Deniers',k:'OPPORTUNITÉ',d:'une possibilité concrète apparaît.'},
  {id:'d_04_Quatre',c:'🪙 4 Deniers',k:'RÉTENTION',d:'je m\'accroche à ce que j\'ai.'},
  {id:'d_07_Sept',c:'🪙 7 Deniers',k:'PATIENCE',d:'j\'ai semé, j\'attends que ça mûrisse.'},
  {id:'d_08_Huit',c:'🪙 8 Deniers',k:'PERFECTIONNEMENT',d:'je développe mon savoir-faire.'},
  {id:'d_09_Neuf',c:'🪙 9 Deniers',k:'INDÉPENDANCE',d:'je profite personnellement de mes acquis.'},
  {id:'d_10_Dix',c:'🪙 10 Deniers',k:'HÉRITAGE',d:'mes acquis deviennent patrimoine.'},
  {id:'d_13_Reine',c:'🪙 Reine Deniers',k:'ENTRETIEN',d:'je prends soin de mes ressources.'},
  {id:'d_14_Roi',c:'🪙 Roi Deniers',k:'PROSPÉRITÉ',d:'mes ressources sont solidement établies.'}
 ]},
 {e:'🔥',t:'Se lancer / vouloir / entreprendre',i:[
  {id:'b_01_As',c:'🪾 As Bâtons',k:'IMPULSION',d:'l\'envie surgit.'},
  {id:'b_11_Valet',c:'🪾 Valet Bâtons',k:'CURIOSITÉ',d:'ça m\'intéresse, je veux découvrir.'},
  {id:'b_12_Cavalier',c:'🪾 Cavalier Bâtons',k:'AVENTURE',d:'je veux le vivre, j\'y vais.'},
  {id:'b_02_Deux',c:'🪾 2 Bâtons',k:'PLANIFICATION',d:'j\'envisage ce que je pourrais faire.'},
  {id:'b_03_Trois',c:'🪾 3 Bâtons',k:'EXPANSION',d:'je veux aller plus loin.'},
  {id:'b_13_Reine',c:'🪾 Reine Bâtons',k:'CHARISME',d:'je sais qui je suis et ça se voit.'},
  {id:'b_14_Roi',c:'🪾 Roi Bâtons',k:'LEADERSHIP',d:'je veux accomplir et j\'embarque les autres.'},
  {id:'a_07_Chariot',c:'🛒 Chariot',k:'CONQUÊTE',d:'je prends les rênes et avance vers mon objectif.'}
 ]}
];
function renderNuances(){let h='';for(const cat of NUANCES){h+='<div class="nuc-cat"><h3><span style="margin-right:.5rem">'+cat.e+'</span>'+cat.t+'</h3><ul>';for(const it of cat.i){const thumb=deckUrl?'<a class="nuc-thumb" onclick="openDetailById(\''+it.id+'\')"><img data-card="'+it.id+'" src="'+deckUrl(it.id)+'"></a>':'';h+='<li>'+thumb+'<div><span class="nuc-card">'+it.c+'</span> = <span class="nuc-key">'+it.k+'</span> → <span class="nuc-desc">'+it.d+'</span></div></li>'}h+='</ul></div>'}return h}
function openDetailById(id){const c=CARDS.find(x=>x.id===id);if(c){closeNuances();closeSearch();openDetail(c.sort)}}

function openNuances(){const el=document.getElementById('nuances');if(!el.querySelector('.nuances-body').innerHTML)el.querySelector('.nuances-body').innerHTML=renderNuances();el.classList.add('open');document.body.style.overflow='hidden'}
function closeNuances(){document.getElementById('nuances').classList.remove('open');document.body.style.overflow=''}

// Mode apprentissage — Leitner simplifié : QCM sur le mot-clé distinctif.
// Lame sue → niveau +1 et fond de pile ; lame ratée → niveau -1 et revient 4 positions plus loin.
const LEARN_KEY='tarotLearnV1';
let LEARN=null;
let learnMode='key';
function learnLvls(){try{return JSON.parse(localStorage.getItem(LEARN_KEY))||{}}catch(e){return{}}}
function learnSave(l){try{localStorage.setItem(LEARN_KEY,JSON.stringify(l))}catch(e){}}
function learnShuffle(a){for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]]}return a}
function learnDeck(mode=learnMode){return CARDS.map(c=>{const p=parsePortrait(PORTRAITS[c.id]||''),kw=p.key.trim(),phrase=(p.idee||p.realite||'').trim();return{c,kw,phrase,answer:mode==='phrase'?phrase:kw}}).filter(x=>x.answer)}
function learnModeLabel(){return learnMode==='phrase'?'phrase':'mot-clé'}
function renderLearnMode(){
  document.getElementById('learnModeKey').setAttribute('aria-pressed',String(learnMode==='key'));
  document.getElementById('learnModePhrase').setAttribute('aria-pressed',String(learnMode==='phrase'));
}
function setLearnMode(mode){
  learnMode=mode;
  renderLearnMode();
  if(LEARN)openLearn();
}
function openLearn(){
  const lv=learnLvls();
  const q=learnShuffle(learnDeck().map(x=>({...x,lvl:lv[x.c.id]||0}))).sort((a,b)=>a.lvl-b.lvl);
  LEARN={queue:q,total:q.length,hits:0,missed:{},seen:{},cur:null,curOpts:[],answered:false};
  document.getElementById('learnTotal').textContent=LEARN.total;
  const stage=document.getElementById('learnStage'),end=document.getElementById('learnEnd'),dock=document.getElementById('learnDock');
  stage.style.display='';dock.style.display='';end.classList.remove('show');
  renderLearnMode();
  document.getElementById('learn').classList.add('open');document.body.style.overflow='hidden';
  learnNext();
}
function closeLearn(){document.getElementById('learn').classList.remove('open');document.body.style.overflow='';LEARN=null}
function learnProgress(){const studied=Object.keys(LEARN.seen).length;document.getElementById('learnDone').textContent=studied;document.getElementById('learnHits').textContent=LEARN.hits;document.getElementById('learnBar').style.width=(100*studied/LEARN.total)+'%'}
function learnNext(){
  if(!LEARN)return;
  const stage=document.getElementById('learnStage'),end=document.getElementById('learnEnd'),dock=document.getElementById('learnDock');
  if(!LEARN.queue.length){
    const lv=learnLvls(),mastered=learnDeck().filter(x=>(lv[x.c.id]||0)>=3).length,miss=Object.values(LEARN.missed);
    document.getElementById('learnDrawer').classList.remove('open');stage.style.display='none';dock.style.display='none';end.classList.add('show');
    end.innerHTML='<h3>Session terminée</h3><div class="score">'+LEARN.hits+' ✓ · '+miss.length+' ratée'+(miss.length>1?'s':'')+' · '+mastered+'/'+LEARN.total+' maîtrisées (niveau ≥ 3)</div>'
      +(miss.length?'<div class="missed"><b>À revoir :</b> '+miss.map(m=>escapeHtml(m.c.name)+' ('+escapeHtml(m.answer)+')').join(' · ')+'</div>':'')
      +'<button class="learn-next" onclick="openLearn()">Recommencer</button> <button class="learn-next" style="background:none;border-color:var(--line);color:var(--muted)" onclick="learnReset()">Réinitialiser la progression</button>';
    return;
  }
  LEARN.cur=LEARN.queue[0];LEARN.answered=false;
  stage.style.display='';dock.style.display='';end.classList.remove('show');
  document.getElementById('learnImg').src=deckUrl(LEARN.cur.c.id);
  // 4 distracteurs, puisés dans la même famille en priorité
  const pool=learnDeck().filter(x=>x.c.id!==LEARN.cur.c.id);
  const dis=learnShuffle(pool.filter(x=>x.c.fam===LEARN.cur.c.fam)).slice(0,4);
  if(dis.length<4)dis.push(...learnShuffle(pool.filter(x=>x.c.fam!==LEARN.cur.c.fam)).slice(0,4-dis.length));
  LEARN.curOpts=learnShuffle(dis.concat([LEARN.cur]));
  document.getElementById('learnOpts').innerHTML=LEARN.curOpts.map((o,i)=>'<button class="learn-opt" onclick="learnAnswer('+i+')"><span class="n">'+(i+1)+'</span>'+escapeHtml(o.answer)+'</button>').join('');
  document.getElementById('learnDrawer').classList.remove('open');
  learnProgress();
}
function learnAnswer(i){
  if(!LEARN||LEARN.answered)return;LEARN.answered=true;
  const cur=LEARN.cur,ok=LEARN.curOpts[i].c.id===cur.c.id;
  document.querySelectorAll('#learnOpts .learn-opt').forEach((b,j)=>{
    b.disabled=true;
    const isRight=LEARN.curOpts[j].c.id===cur.c.id;
    if(ok){b.style.display=isRight?'':'none';if(isRight)b.classList.add('right')}
    else{if(isRight)b.classList.add('right');else if(j===i){b.classList.add('wrong')}else b.style.display='none'}
  });
  const lv=learnLvls();LEARN.seen[cur.c.id]=true;
  LEARN.queue.shift();
  if(ok){LEARN.hits++;lv[cur.c.id]=Math.min(5,(lv[cur.c.id]||0)+1)}
  else{LEARN.missed[cur.c.id]={c:cur.c,answer:cur.answer};lv[cur.c.id]=Math.max(0,(lv[cur.c.id]||0)-1);LEARN.queue.splice(Math.min(LEARN.queue.length,4),0,cur)}
  learnSave(lv);
  const p=parsePortrait(PORTRAITS[cur.c.id]||'');
  document.getElementById('learnDrawer').innerHTML='<h3>'+escapeHtml(cur.c.name)+' — <em>'+escapeHtml(cur.kw)+'</em></h3>'
    +(p.idee?'<p>'+escapeHtml(p.idee)+'</p>':'')+(p.realite?'<p>'+escapeHtml(p.realite)+'</p>':'')
    +'<button class="learn-next" onclick="learnNext()">Suivante →</button>';
  document.getElementById('learnDrawer').classList.add('open');
  learnProgress();
}
function learnReset(){learnSave({});openLearn()}

// Grille principale : les 78 lames sont visibles sans saisie.
function familyIntro(f,n){const glyph=f.key&&f.key!=='majors'?'<img class="family-glyph" src="'+B+'/index.php?svg='+f.key+'" alt="">':'<span class="family-glyph">'+(f.sym||'✦')+'</span>';return '<button type="button" class="mini family-intro" style="--family:'+(f.ac||'#c9a227')+'" onclick="openFamily(\''+f.key+'\')"><span class="ph">'+glyph+'<span class="family-name">'+f.name+'</span><span class="family-element">'+(f.el||'')+'</span></span><span class="cap"><span class="nm">Famille</span><span class="no">00</span></span></button>'}
function renderGrid(){let h='',last=null;for(const c of CARDS){if(c.fam!==last){const f=fam(c.fam),group=CARDS.filter(x=>x.fam===c.fam);h+='<div class="fam-card"><span class="g">'+(f.sym||'✦')+'</span><span class="fn">'+f.name+'</span><span class="fc">'+group.length+' lames</span></div>'+familyIntro(f,group.length);last=c.fam}h+=mini(c)}document.getElementById('grid').innerHTML=h}
function kwOverlay(c){const k=KW[c.id]||'';return k?'<span class="kw-overlay"><span class="kw">'+escapeHtml(k)+'</span><span class="kn">'+escapeHtml(c.name)+'</span></span>':''}
function mini(c){return '<button type="button" class="mini" onclick="openDetail('+c.sort+')"><span class="ph"><img data-card="'+c.id+'" src="'+deckUrl(c.id)+'" alt="'+c.name+'" loading="lazy"></span><span class="cap"><span class="nm">'+c.name+'</span><span class="no">'+String(c.sort+1).padStart(2,'0')+'</span></span>'+kwOverlay(c)+'</button>'}

// Recherche
function syncQuery(){const q=document.getElementById('sQuery');q.innerHTML=searchState.q?'<span>'+escapeHtml(searchState.q)+'</span>':'<span class="ph">Tapez une lame…</span>'}
function searchRender(){const out=CARDS.filter(c=>(!searchState.fam||c.fam===searchState.fam)&&(!searchState.q||c.name.toLowerCase().includes(searchState.q)||String(c.num).padStart(2,'0').includes(searchState.q)));document.getElementById('sGrid').innerHTML=out.length?out.map((c,i)=>'<div class="mini '+(i===searchState.selIdx?'sel':'')+'" onclick="openDetail('+c.sort+');closeSearch()"><div class="ph"><img data-card="'+c.id+'" src="'+deckUrl(c.id)+'"></div><div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+String(c.sort+1).padStart(2,'0')+'</span></div>'+kwOverlay(c)+'</div>').join(''):'<div class="s-empty">Aucune lame</div>'}
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
// swipe horizontal mobile : carte précédente/suivante
(function(){let sx=0,sy=0,st=0;const el=document.getElementById('detail');
el.addEventListener('touchstart',e=>{const t=e.changedTouches[0];sx=t.clientX;sy=t.clientY;st=Date.now()},{passive:true});
el.addEventListener('touchend',e=>{if(currentIdx<0)return;const t=e.changedTouches[0],dx=t.clientX-sx,dy=t.clientY-sy;
if(e.target.closest('.d-thumbs,.d-assocs-toggle,a,button'))return;
if(Math.abs(dx)>60&&Math.abs(dx)>Math.abs(dy)*1.5&&Date.now()-st<800){e.preventDefault();openDetail(CARDS[(currentIdx+(dx<0?1:-1)+CARDS.length)%CARDS.length].sort,dx<0?1:-1)}},{passive:false});})();

document.addEventListener('keydown',e=>{if(e.metaKey||e.ctrlKey||e.altKey)return;const tag=e.target?.tagName;
  const sg=document.getElementById('search').classList.contains('open');
  const lg=document.getElementById('learn').classList.contains('open');
  if(e.key==='Escape'){if(lg)closeLearn();else if(sg)closeSearch();else if(document.getElementById('nuances').classList.contains('open'))closeNuances();else{if(autoTimer)stopAuto();closeDetail()}return}
  if(lg){if(/^[1-5]$/.test(e.key))learnAnswer(+e.key-1);else if(LEARN&&LEARN.answered&&(e.key==='Enter'||e.key===' '||e.key==='ArrowRight')){e.preventDefault();learnNext()}return}
  if(tag&&/INPUT|TEXTAREA|SELECT/i.test(tag))return;
  if(sg){if(e.key==='ArrowDown'){searchState.selIdx++;searchRender()}if(e.key==='ArrowUp'){searchState.selIdx=Math.max(0,searchState.selIdx-1);searchRender()}if(e.key==='Enter'){document.querySelector('#sGrid .mini.sel')?.click()}return}
  if(e.key.length===1&&/^[\p{L}\p{N}]$/u.test(e.key)){e.preventDefault();openSearch(e.key);return}
  if(currentIdx>=0){if(e.key==='ArrowLeft')openDetail(CARDS[(currentIdx-1+CARDS.length)%CARDS.length].sort,-1);if(e.key==='ArrowRight')openDetail(CARDS[(currentIdx+1)%CARDS.length].sort,1)}
});

renderGrid();renderChips();applyDeck();requestAnimationFrame(()=>document.querySelector('.seuil-title').classList.add('revealed'));
document.getElementById('loader').classList.add('gone');

// thème : nuit / ivoire / sylve, persisté
const THEMES=[{k:'',l:'Nuit',c:'#0a0907'},{k:'ivoire',l:'Ivoire',c:'#efe9dc'},{k:'sylve',l:'Sylve',c:'#0a0f0b'}];
(function(){const saved=localStorage.getItem('tarotTheme')||'';const t=THEMES.find(x=>x.k===saved)||THEMES[0];applyTheme(t,false)})();
function applyTheme(t,save){document.documentElement.dataset.theme=t.k;document.getElementById('themeLbl').textContent=t.l;document.querySelector('meta[name=theme-color]').setAttribute('content',t.c);if(save)try{localStorage.setItem('tarotTheme',t.k)}catch(e){}}
function cycleTheme(){const cur=localStorage.getItem('tarotTheme')||'';const i=THEMES.findIndex(x=>x.k===cur);applyTheme(THEMES[(i+1)%THEMES.length],true)}

// loupe hero (conservé)
(function(){const hero=document.querySelector('.d-hero'),loupe=document.getElementById('heroLoupe');if(!hero||!loupe)return;let active=false;hero.addEventListener('mouseenter',()=>{active=true;loupe.classList.add('active')});hero.addEventListener('mouseleave',()=>{active=false;loupe.classList.remove('active')});hero.addEventListener('mousemove',e=>{if(!active)return;const r=hero.getBoundingClientRect();loupe.style.left=(e.clientX-r.left)+'px';loupe.style.top=(e.clientY-r.top)+'px'})})();

// spreads
function buildTarot(){window.TAROT={families:FAMILIES.map(function(f){return{key:f.key,name:f.name,accent:f.ac||'#c9a227',cards:CARDS.filter(function(c){return c.fam===f.key}).map(function(c){const es=ES_MAP[c.id]||{};return{id:c.id,name:c.name,num:c.num,sort:c.sort,family:c.fam,familyName:f.name,element:f.el||'',file:deckUrl(c.id)||'',es:{reponse:es.rep||'',affirmation:es.aff||''}}})}})}}
buildTarot();if(window.TarotSpreads)TarotSpreads.init();
window.tarotOpenCard=function(card){if(window.TarotSpreads)TarotSpreads.closeSpread();if(typeof card.sort==='number')openDetail(card.sort)};
if('serviceWorker' in navigator)navigator.serviceWorker.register('sw.js');
</script>
</body></html>
