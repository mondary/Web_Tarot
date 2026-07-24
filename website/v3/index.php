<?php
declare(strict_types=1);

// ----------------------------------------------------------
// CONFIG
// ----------------------------------------------------------
$basePath = '/pk/tarot';
$dbPath   = __DIR__ . '/tarot.sqlite';

// ----------------------------------------------------------
// ERROR HANDLING
// ----------------------------------------------------------
ini_set('display_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    http_response_code(500);
    echo "<h1>500</h1><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
});

// ----------------------------------------------------------
// DB
// ----------------------------------------------------------
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// ----------------------------------------------------------
// ROUTER
// ----------------------------------------------------------
$rawPath = $_SERVER['REQUEST_URI'] ?? '/';
$rParam  = $_GET['r'] ?? '';
$path    = str_replace($basePath, '', $rawPath);
$path    = parse_url($path, PHP_URL_PATH) ?: '/';
if ($rParam) $path = $rParam;
$path    = '/' . trim($path, '/');
if ($path === '/') $path = '/';

switch (true) {
    case $path === '/':
        page_landing($pdo, $basePath);
        break;
    case preg_match('#^/suite/([^/]+)$#', $path, $m):
        page_family($pdo, $m[1], $basePath);
        break;
    case preg_match('#^/card/(.+)$#', $path, $m):
        page_card($pdo, $m[1], $basePath);
        break;
    case preg_match('#^/img/(.+)$#', $path, $m):
        serve_image($m[1], $pdo);
        break;
    case preg_match('#^/search#', $path):
        api_search($pdo, $basePath);
        break;
    case $path === '/icon':
        serve_icon();
        break;
    default:
        http_response_code(404);
        echo '<h1>404</h1>';
}

// ==========================================================
// CSS — extracted from website/index.html V1/V2 design
// ==========================================================
function css_block(): string {
    return <<<'CSS'
:root{
  --bg:#050505; --bg-2:#0a0907;
  --fg:#f1ede4; --muted:#8a8378;
  --line:rgba(241,237,228,.08);
  --ease:cubic-bezier(.16,1,.3,1);
  --accent:#c9a227; --ac:var(--accent);
  --mat:#ffffff; --mat-line:rgba(255,255,255,.22);
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{background:var(--bg);color:var(--fg);font-family:'Plus Jakarta Sans',sans-serif;-webkit-font-smoothing:antialiased}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}

/* Grain + vignette */
.fx-grain{position:fixed;inset:0;z-index:9000;pointer-events:none;opacity:.035;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.fx-vignette{position:fixed;inset:0;z-index:8990;pointer-events:none;
  background:radial-gradient(120% 100% at 50% 50%,transparent 55%,rgba(0,0,0,.55) 100%)}

/* Brand */
.brand{position:fixed;top:1.8rem;left:2.2rem;z-index:200;display:flex;align-items:center;gap:.65rem;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.32em;text-transform:uppercase;color:var(--muted)}
.brand b{color:var(--fg);font-weight:400}
.brand img{width:1.25rem;height:1.25rem;object-fit:contain}

/* Landing footer */
.landing-foot{position:fixed;bottom:1.2rem;left:1.6rem;z-index:200;
  display:flex;align-items:center;gap:1.1rem;
  font-family:'DM Mono',monospace;font-size:.64rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);
  background:rgba(10,9,7,.75);backdrop-filter:blur(8px);padding:.45rem .9rem;border-radius:6px;border:1px solid rgba(255,255,255,.06)}
.landing-foot a{color:var(--muted);transition:color .3s;border-bottom:1px solid transparent;padding-bottom:1px}
.landing-foot a:hover{color:var(--fg);border-color:var(--ac)}

/* Back button */
.back{position:fixed;top:1.8rem;left:2.2rem;z-index:300;display:flex;align-items:center;gap:.6rem;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.24em;text-transform:uppercase;color:var(--muted);
  padding:.6rem 1.1rem;border:1px solid var(--line);border-radius:50px;background:var(--bg);transition:.4s var(--ease)}
.back:hover{color:var(--fg);border-color:var(--ac)}
.back svg{width:13px;height:13px}

/* Full grid head */
.full-grid-head{padding:6rem 4vw 2rem;border-bottom:1px solid var(--line)}
.full-grid-head h1{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(3rem,7vw,5.5rem);line-height:.9;text-transform:uppercase;letter-spacing:-.02em}
.full-grid-head h1 em{font-style:italic;color:var(--accent);font-weight:400}
.full-grid-head .sub{color:var(--muted);font-size:.9rem;font-weight:300;margin-top:.7rem;max-width:40rem}

/* Full grid */
.full-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1.2rem;padding:2rem 4vw 6rem;align-items:start}

/* Family separator */
.fam-sep{grid-column:1/-1;display:flex;align-items:center;gap:1.5rem;padding:2.5rem 0 1rem;margin-top:1rem;border-bottom:1px solid var(--line)}
.fam-sep-art{flex:0 0 auto;width:80px;height:120px;position:relative;display:grid;place-items:center}
.fam-sep-art .glyph{font-family:'Cormorant Garamond',serif;font-size:3rem;color:var(--ac);line-height:1;text-align:center}
.fam-sep-art .ring{position:absolute;inset:0;border:1.5px solid var(--ac);border-radius:.6rem;opacity:.3}
.fam-sep-body{flex:1;min-width:0}
.fam-sep-body .lbl{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.26em;text-transform:uppercase;color:var(--ac);margin-bottom:.4rem}
.fam-sep-body .name{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(1.8rem,3.5vw,2.8rem);line-height:1;text-transform:uppercase;letter-spacing:-.01em}
.fam-sep-body .name em{font-style:italic;color:var(--ac);font-weight:400}
.fam-sep-body .desc{color:var(--muted);font-size:.82rem;line-height:1.5;font-weight:300;margin-top:.5rem;max-width:32rem}
.fam-sep-count{flex:0 0 auto;font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);text-align:right}
.fam-sep-count b{display:block;font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--fg);letter-spacing:0;text-transform:none;font-weight:400}

/* Mini card */
.mini{position:relative;border-radius:.9rem;overflow:hidden;background:var(--mat);border:1px solid var(--mat-line);
  cursor:pointer;transition:.5s var(--ease);display:flex;flex-direction:column;box-shadow:0 10px 28px rgba(0,0,0,.35)}
.mini:hover{transform:translateY(-6px);border-color:var(--ac);box-shadow:0 16px 40px rgba(0,0,0,.5)}
.mini:focus-visible,.mini.selected{transform:translateY(-6px);border-color:var(--ac);box-shadow:0 0 0 2px var(--ac),0 16px 40px rgba(0,0,0,.5);outline:none;z-index:2}
.mini .ph{aspect-ratio:2/3;display:flex;align-items:center;justify-content:center;padding:.5rem;position:relative}
.mini .ph img{height:100%;object-fit:contain;transition:transform .6s var(--ease)}
.mini:hover .ph img{transform:scale(1.05)}
.mini .cap{padding:.6rem .75rem .7rem;border-top:1px solid rgba(0,0,0,.06);font-size:.78rem;display:flex;justify-content:space-between;align-items:center;gap:.5rem;background:var(--mat)}
.mini .cap .nm{font-family:'Cormorant Garamond',serif;font-size:1.02rem;line-height:1.1;font-weight:500;color:#1c1814;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mini .cap .no{font-family:'DM Mono',monospace;font-size:.62rem;color:#a59c8e;letter-spacing:.1em;flex:0 0 auto}

/* Grid head (family view) */
.grid-head{position:sticky;top:0;z-index:6;display:flex;align-items:flex-end;justify-content:space-between;
  padding:5rem 4vw 1.4rem;gap:2rem;flex-wrap:wrap;background:var(--bg);box-shadow:0 12px 0 var(--bg);border-bottom:1px solid var(--line)}
.grid-head h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(2.6rem,6vw,5rem);line-height:.9;text-transform:uppercase;letter-spacing:-.01em}
.grid-head h2 em{font-style:italic;color:var(--ac);font-weight:400}
.grid-head .summary{max-width:34rem;margin-top:.85rem;color:var(--muted);font-size:.9rem;line-height:1.5;font-weight:300}
.grid-head .right{text-align:right;font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)}
.grid-head .right b{display:block;font-family:'Cormorant Garamond',serif;font-size:2.4rem;color:var(--fg);letter-spacing:0;text-transform:none;font-weight:400}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1.2rem;padding:1.4rem 4vw 7rem}

/* Immersive detail */
.d-stage{position:absolute;inset:0;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:rgba(241,237,228,.25) transparent}
.d-stage::-webkit-scrollbar{width:8px}
.d-stage::-webkit-scrollbar-thumb{background:rgba(241,237,228,.22);border-radius:4px}
.d-hero{position:sticky;top:0;height:78vh;min-height:460px;width:100%;overflow:hidden;background:#0a0a0a;z-index:0}
.d-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;filter:saturate(1.05) contrast(1.02)}
.d-hero::before{content:'';position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(180deg,rgba(0,0,0,.28) 0%,transparent 22%,transparent 45%,rgba(10,10,10,.55) 72%,#0a0a0a 100%)}
.d-hero::after{content:'';position:absolute;left:0;right:0;bottom:-1px;height:42%;pointer-events:none;z-index:2;background:linear-gradient(to bottom,transparent 0%,rgba(10,10,10,.5) 45%,#0a0a0a 100%)}
.d-panel{position:relative;z-index:2;background:#0a0a0a;color:var(--fg);margin-top:-28px;border-radius:26px 26px 0 0;padding:34px 8% 120px;min-height:60vh}
.d-panel-inner{max-width:46rem;margin:0 auto}
.d-meta{font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.26em;text-transform:uppercase;color:var(--muted);margin-bottom:14px;display:flex;gap:.8rem;align-items:center;flex-wrap:wrap}
.d-meta b{color:var(--ac);font-weight:400}
.d-meta .sep{opacity:.4}
.d-title{position:sticky;top:0;z-index:10;font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(2.4rem,5.5vw,4.6rem);line-height:1;text-transform:uppercase;letter-spacing:-.01em;margin:0 0 22px;padding:26px 0 20px;background:linear-gradient(#0a0a0a 72%,rgba(10,10,10,0))}
.d-title em{font-style:italic;color:var(--ac);font-weight:400;text-shadow:0 0 28px rgba(255,255,255,.12)}

/* Prose */
.prose{max-width:44rem}
.prose section{margin-bottom:2.4rem;scroll-margin-top:7rem}
.prose h2{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:1.7rem;margin-bottom:.9rem;display:flex;align-items:center;gap:.8rem}
.prose h2::before{content:'';width:24px;height:1px;background:var(--ac)}
.prose h3{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin:1.4rem 0 .7rem}
.prose p{font-size:1rem;line-height:1.85;color:#d8d2c5;font-weight:300;margin-bottom:.9rem}
.prose p.field{margin-bottom:.5rem;display:flex;gap:.6rem;flex-wrap:wrap;font-size:.92rem;color:#bdb5a6}
.prose p.field strong{color:var(--ac);font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;font-weight:400}
.prose strong{color:var(--fg);font-weight:500}
.prose ul{list-style:none;margin:.6rem 0 1.2rem}
.prose li{position:relative;padding-left:1.4rem;margin-bottom:.5rem;font-size:.98rem;line-height:1.7;color:#cfc8ba;font-weight:300}
.prose li::before{content:'';position:absolute;left:0;top:.65rem;width:6px;height:6px;border-radius:50%;background:var(--ac)}
.prose blockquote{margin:1.6rem 0;padding:1.2rem 1.6rem;border-left:2px solid var(--ac);background:rgba(241,237,228,.03);border-radius:0 .6rem .6rem 0;font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.2rem;line-height:1.6;color:var(--fg)}
.prose blockquote p{color:var(--fg);font-family:inherit;font-style:italic;font-size:inherit;margin:0}
.prose hr{border:none;height:1px;background:var(--line);margin:2.4rem 0}

/* ES badges */
.es-badges{display:flex;flex-direction:column;gap:1rem;margin:2rem auto 2.5rem;align-items:center;text-align:center;max-width:700px}
.es-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .65rem;border-radius:999px;font-size:.72rem;font-weight:600;letter-spacing:.03em;text-transform:uppercase;line-height:1.2}
.es-badge.resp{background:var(--ac);color:#fff}
.es-badge.aff{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:clamp(1.6rem,3vw,2.4rem);line-height:1.2;color:var(--fg);font-style:italic;border-left:3px solid var(--ac);border-radius:0;padding:.5rem 0 .5rem 1.5rem;text-transform:none;letter-spacing:0;display:block;max-width:600px}

/* Thumbnails */
.d-thumbs{display:flex;gap:8px;overflow-x:auto;padding:1.4rem 0 4px;margin-top:2.6rem;scrollbar-width:thin;scrollbar-color:rgba(241,237,228,.25) transparent}
.d-thumbs::-webkit-scrollbar{height:6px}
.d-thumbs::-webkit-scrollbar-thumb{background:rgba(241,237,228,.22);border-radius:3px}
.d-thumb{flex:0 0 auto;width:60px;aspect-ratio:2/3;border-radius:6px;overflow:hidden;background:var(--mat);border:2px solid transparent;cursor:pointer;transition:.3s var(--ease);padding:3px}
.d-thumb img{width:100%;height:100%;object-fit:contain}
.d-thumb:hover{transform:translateY(-3px);border-color:rgba(241,237,228,.5)}
.d-thumb.current{border-color:var(--ac)}

/* Loop bar */
.d-loop{position:fixed;bottom:1.4rem;left:50%;transform:translateX(-50%);z-index:200;
  display:flex;align-items:center;gap:1rem;padding:.55rem 1.2rem;
  background:rgba(10,9,7,.72);backdrop-filter:blur(14px);border:1px solid var(--line);border-radius:50px;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;max-width:calc(100vw - 120px)}
.d-loop a{color:var(--muted);transition:.3s var(--ease);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:30vw}
.d-loop a:hover{color:var(--fg)}
.d-loop .pos{color:var(--fg);flex:0 0 auto;border-left:1px solid var(--line);border-right:1px solid var(--line);padding:0 1rem}
.d-loop .pos b{color:var(--ac);font-weight:400}

/* Scroll container */
#page-scroll{overflow-y:auto;overflow-x:hidden;height:100vh;scrollbar-width:thin;scrollbar-color:rgba(241,237,228,.2) transparent}
#page-scroll::-webkit-scrollbar{width:6px}
#page-scroll::-webkit-scrollbar-thumb{background:rgba(241,237,228,.2);border-radius:3px}

/* Keywords 2-column */
#sec-mots-cles{display:block;max-width:none;width:100%}
#sec-mots-cles h2{margin-bottom:1.2rem}
#sec-mots-cles .kw-cols{display:grid;grid-template-columns:1fr 1fr;gap:2rem}
#sec-mots-cles .kw-col h3{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.22em;text-transform:uppercase;color:var(--ac);margin-bottom:.8rem;padding-bottom:.5rem;border-bottom:1px solid var(--line)}
#sec-mots-cles .kw-col ul{list-style:none;display:grid;gap:.45rem}
#sec-mots-cles .kw-col li{position:relative;padding-left:1rem;color:#d8d2c5;font-size:.92rem;line-height:1.4}
#sec-mots-cles .kw-col li::before{content:'◆';position:absolute;left:0;top:.35rem;color:var(--ac);font-size:.42rem}
@media(max-width:900px){#sec-mots-cles .kw-cols{grid-template-columns:1fr;gap:1rem}}

/* Theme icons */
.prose h2 .content-icon{display:inline-flex;width:1.05em;height:1.05em;color:var(--ac);vertical-align:-.12em;flex:0 0 auto;margin-right:.3rem}
.prose h2 .content-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:1.5}

/* Theme grid */
.theme-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:2rem;margin:2.5rem 0;padding-top:1.5rem;border-top:1px solid var(--line)}
.theme-grid section{margin:0!important;padding:0!important;border:none!important}
.theme-grid section h2{font-family:'Cormorant Garamond',serif;font-size:clamp(1.6rem,2.4vw,2.2rem);font-weight:400;line-height:1;display:flex;align-items:center;gap:.7rem;margin-bottom:.8rem}
.theme-grid section h2::before{display:none}
.theme-grid section p{font-size:.96rem;line-height:1.8;color:#d8d2c5;font-weight:300;margin-bottom:.7rem}

@media(max-width:900px){
  .theme-grid{grid-template-columns:1fr;gap:1.2rem}
  .full-grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.8rem;padding:.8rem 6vw 6rem}
  .full-grid-head{padding:5rem 6vw 1.5rem}
  .brand{left:1.2rem;top:1.2rem}
  .back{left:1.2rem;top:1.2rem}
  .grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.8rem;padding:.8rem 6vw 6rem}
  .grid-head{position:static;padding:4.5rem 6vw 1rem;border-bottom:none}
  .d-hero{height:62vh;min-height:340px}
  .d-panel{padding:28px 6% 110px;border-radius:22px 22px 0 0}
  .d-loop{font-size:.58rem;gap:.6rem;padding:.45rem .9rem;max-width:calc(100vw - 40px)}
}

/* FAB bar */
.fab-bar{position:fixed;right:max(1.2rem,env(safe-area-inset-right));bottom:max(1.2rem,env(safe-area-inset-bottom));z-index:500;
  display:flex;align-items:center;gap:.5rem;padding:.4rem;border-radius:50px;
  background:rgba(10,9,7,.72);backdrop-filter:blur(14px);border:1px solid var(--line);box-shadow:0 12px 28px rgba(0,0,0,.42)}
.search-launch{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;
  background:var(--bg-2);border:1px solid var(--line);color:var(--muted);
  transition:color .2s,border-color .2s;flex-shrink:0;cursor:pointer}
.search-launch:hover{color:var(--accent);border-color:var(--accent)}
.search-launch svg{width:20px;height:20px}
@media(max-width:900px){
  .fab-bar{gap:.35rem;padding:.3rem}
  .search-launch{width:34px;height:34px}
  .search-launch svg{width:16px;height:16px}
}

/* Picker overlay — mobile bottom-sheet / desktop fullscreen */
#search{position:fixed;inset:0;z-index:8000;background:rgba(5,5,5,.94);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  display:none;opacity:0;transition:opacity .3s var(--ease)}
#search.open{display:flex;opacity:1}

/* Sheet = the panel that holds everything */
.picker-sheet{position:relative;width:100%;background:transparent;
  display:flex;flex-direction:column;min-height:0}

/* Desktop: fullscreen column with giant query */
@media(min-width:901px){
  #search.open{align-items:stretch;flex-direction:column}
  .picker-sheet{height:100vh}
  .s-query{flex:0 0 auto;text-align:center;padding:7vh 4vw 1vh;min-height:22vh;
    display:flex;align-items:center;justify-content:center;
    font-family:'Cormorant Garamond',serif;font-weight:300;
    font-size:clamp(4rem,17vw,17rem);line-height:.86;letter-spacing:-.02em;color:var(--fg);
    word-break:break-word;text-transform:uppercase}
  .s-query .ph{color:var(--muted);font-style:italic;font-weight:300;text-transform:none;font-size:.18em;letter-spacing:.04em}
  .s-query .caret{display:inline-block;width:.05em;min-height:.7em;background:var(--ac);margin-left:.05em;align-self:center;animation:s-blink 1s steps(2) infinite}
  @keyframes s-blink{50%{opacity:0}}
  .s-close{position:fixed;top:1.5rem;right:2.2rem;z-index:8001;width:42px;height:42px;border-radius:50%;
    display:grid;place-items:center;background:rgba(5,5,5,.75);border:1px solid var(--line);color:var(--fg);cursor:pointer}
  .s-close svg{width:18px;height:18px}
  .picker-search{display:none}
  .picker-top{display:none}
  .picker-grip{display:none}
  .picker-chips{max-width:1500px;margin:0 auto;width:100%;display:flex;gap:.5rem;overflow-x:auto;
    padding:.9rem 4vw;border-bottom:1px solid var(--line);background:rgba(5,5,5,.7);backdrop-filter:blur(8px);
    position:sticky;top:0;z-index:2;scrollbar-width:none}
  .picker-chips::-webkit-scrollbar{display:none}
  .picker-body{flex:1;overflow-y:auto;padding:1.5rem 4vw 6vh;min-height:0}
  .picker-body .s-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(118px,1fr));gap:1rem;max-width:1500px;margin:0 auto}
}

/* Shared chip styles */
.chip{flex:0 0 auto;display:inline-flex;align-items:center;gap:.4rem;
  padding:.55rem .9rem;border-radius:50px;
  background:rgba(255,255,255,.04);border:1px solid var(--line);
  color:var(--muted);font-family:'DM Mono',monospace;font-size:.68rem;
  letter-spacing:.08em;text-transform:uppercase;cursor:pointer;
  transition:color .2s,border-color .2s,background .2s;white-space:nowrap;-webkit-tap-highlight-color:transparent}
.chip:hover{color:var(--fg);border-color:rgba(255,255,255,.22)}
.chip.active{background:var(--ac,var(--accent));color:#050505;border-color:var(--ac,var(--accent))}
.chip .sym{font-size:.95rem;line-height:1;font-family:'Cormorant Garamond',serif}
.chip .n{opacity:.55;font-size:.58rem;font-weight:400}
.chip.active .n{opacity:.85}
.s-grid .mini{cursor:pointer}
.s-grid .mini.sel{transform:translateY(-6px);border:2px solid var(--ac);box-shadow:0 0 0 1px var(--ac),0 16px 40px rgba(0,0,0,.5);z-index:2}
.s-empty{grid-column:1/-1;text-align:center;color:var(--muted);padding:3rem 0;font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.4rem}

/* Mobile: bottom-sheet */
@media(max-width:900px){
  #search.open{align-items:flex-end}
  .picker-sheet{height:92vh;max-height:92vh;
    background:var(--bg-2);border-top:1px solid var(--line);
    border-radius:22px 22px 0 0;overflow:hidden;
    animation:sheet-up .38s var(--ease);
    padding-bottom:env(safe-area-inset-bottom)}
  @keyframes sheet-up{from{transform:translateY(100%)}to{transform:translateY(0)}}
  .picker-grip{width:38px;height:4px;border-radius:2px;background:rgba(241,237,228,.22);margin:.55rem auto .25rem;flex:0 0 auto}
  .picker-top{display:flex;align-items:center;justify-content:space-between;padding:.3rem 1.1rem .55rem;flex:0 0 auto}
  .picker-count{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)}
  .picker-count b{color:var(--fg);font-weight:400}
  .picker-count .ac{color:var(--ac)}
  .s-close{position:static;width:32px;height:32px;border-radius:50%;display:grid;place-items:center;
    background:rgba(255,255,255,.05);border:1px solid var(--line);color:var(--fg);cursor:pointer;flex:0 0 auto}
  .s-close svg{width:15px;height:15px}
  .picker-search{display:flex;align-items:center;gap:.65rem;margin:0 1rem .05rem;padding:.7rem .9rem;
    background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:50px;flex:0 0 auto}
  .picker-search svg{width:16px;height:16px;color:var(--muted);flex-shrink:0}
  .picker-search .s-input{flex:1;width:100%;margin:0;padding:0;border:none;background:transparent;
    color:var(--fg);font-family:'Plus Jakarta Sans',sans-serif;font-size:.98rem;outline:none}
  .picker-search .s-input::placeholder{color:var(--muted)}
  .picker-chips{display:flex;gap:.4rem;overflow-x:auto;padding:.65rem 1rem .7rem;flex:0 0 auto;
    border-bottom:1px solid var(--line);scrollbar-width:none;-webkit-overflow-scrolling:touch}
  .picker-chips::-webkit-scrollbar{display:none}
  .picker-body{flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:.9rem 1rem 1.2rem;min-height:0;overscroll-behavior:contain}
  .picker-body .s-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:.6rem}
  .picker-body .mini .cap{padding:.4rem .5rem .45rem}
  .picker-body .mini .cap .nm{font-size:.82rem}
  .picker-body .mini .cap .no{font-size:.55rem}
}
CSS;
}

// ==========================================================
// SHARED LAYOUT
// ==========================================================
function layout_head(string $basePath, string $title, string $extraStyle = ''): string {
    $css = css_block();
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<link rel="icon" href="{$basePath}/index.php?r=/icon" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
<style>
{$css}
{$extraStyle}
</style>
</head>
<body>
<div class="fx-grain"></div>
<div class="fx-vignette"></div>
HTML;
}

function layout_brand(string $basePath): string {
    return <<<HTML
<a class="brand" href="{$basePath}/" aria-label="Retour à l'accueil">
  <img src="{$basePath}/index.php?r=/icon" alt="">
  <span><b>Tarot</b> · Divinatoire</span>
</a>
HTML;
}

function layout_foot(string $basePath): string {
    return <<<HTML
<div class="landing-foot">
  <span class="ver">v3.2026.24</span>
  <a href="https://github.com/mondary/Web_Tarot" target="_blank" rel="noopener">GitHub</a>
  <a href="https://mondary.design" target="_blank" rel="noopener">mondary.design</a>
</div>
HTML;
}

function layout_search(PDO $pdo, string $basePath): string {
    // Inline ALL 78 cards metadata (~10 KB) + family chips → zero per-keystroke HTTP
    $rows = $pdo->query("SELECT id, family_key AS fk, name, num FROM cards ORDER BY sort_global ASC")->fetchAll();
    $fams = $pdo->query("SELECT key, short, element_sym, accent, (SELECT COUNT(*) FROM cards c WHERE c.family_key=families.key) AS n FROM families ORDER BY sort_order ASC")->fetchAll();

    $cardsJson = json_encode(array_map(function ($c) {
        return ['id' => $c['id'], 'fk' => $c['fk'], 'name' => $c['name'],
                'num' => str_pad((string)(int)$c['num'], 2, '0', STR_PAD_LEFT)];
    }, $rows), JSON_UNESCAPED_UNICODE);

    $total = count($rows);
    $chipsHtml = '<button class="chip active" data-fam="" type="button">Tout<span class="n">' . $total . '</span></button>';
    foreach ($fams as $f) {
        $k    = htmlspecialchars($f['key'], ENT_QUOTES);
        $ac   = htmlspecialchars($f['accent'], ENT_QUOTES);
        $sym  = htmlspecialchars($f['element_sym'], ENT_QUOTES);
        $sh   = htmlspecialchars($f['short'], ENT_QUOTES);
        $n    = (int)$f['n'];
        $chipsHtml .= '<button class="chip" data-fam="' . $k . '" style="--ac:' . $ac . '" type="button">'
                   . '<span class="sym">' . $sym . '</span>' . $sh . '<span class="n">' . $n . '</span></button>';
    }

    return <<<HTML
<div class="fab-bar">
  <button class="search-launch" id="search-launch" aria-label="Parcourir les cartes">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
  </button>
</div>
<div id="search" role="dialog" aria-modal="true" aria-label="Sélecteur de cartes">
  <div class="picker-sheet">
    <div class="picker-grip" aria-hidden="true"></div>
    <div class="picker-top">
      <span class="picker-count" id="picker-count"><b>{$total}</b> <span class="ac">lames</span></span>
      <button class="s-close" id="s-close" aria-label="Fermer" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
    <div class="s-query" id="s-query"><span class="ph">Tapez une lame…</span></div>
    <div class="picker-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input class="s-input" id="s-input" type="text" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" placeholder="Rechercher une lame…" enterkeyhint="go" inputmode="search" autofocus>
    </div>
    <div class="picker-chips" id="picker-chips" role="tablist" aria-label="Filtrer par famille">{$chipsHtml}</div>
    <div class="picker-body"><div class="s-grid" id="s-grid"></div></div>
  </div>
</div>
<script>
const BASE={$cardsJson};
const PREFIX="{$basePath}";
(function(){
  var sO=document.getElementById('search'),sI=document.getElementById('s-input'),
      sG=document.getElementById('s-grid'),sQ=document.getElementById('s-query'),
      sCount=document.getElementById('picker-count'),chips=document.getElementById('picker-chips');
  var st={fam:'',q:'',selIdx:0},io=null;
  function card(c){
    return '<a class="mini" href="'+PREFIX+'/card/'+c.id+'" style="text-decoration:none;color:inherit">'+
      '<div class="ph"><img src="'+PREFIX+'/img/'+c.fk+'/'+c.id+'.jpg" alt="'+c.name+'" loading="lazy"></div>'+
      '<div class="cap"><span class="nm">'+c.name+'</span><span class="no">'+c.num+'</span></div></a>';
  }
  function render(){
    var q=st.q, f=st.fam, out=[];
    for(var i=0;i<BASE.length;i++){
      var c=BASE[i];
      if(f && c.fk!==f) continue;
      if(q && c.name.toLowerCase().indexOf(q)===-1 && c.num.indexOf(q)===-1) continue;
      out.push(c);
    }
    sCount.innerHTML='<b>'+out.length+'</b> <span class="ac">'+(out.length>1?'lames':'lame')+'</span>'+(f?' · '+f:'');
    if(!out.length){sG.innerHTML='<div class="s-empty">Aucune lame</div>';return;}
    var frag=document.createDocumentFragment(),tmp=document.createElement('div');
    for(var j=0;j<out.length;j++){tmp.innerHTML=card(out[j]);while(tmp.firstChild)frag.appendChild(tmp.firstChild);}
    sG.innerHTML='';sG.appendChild(frag);
    st.selIdx=0;
    paintSel();
  }
  function paintSel(){
    var minis=sG.querySelectorAll('.mini');
    for(var i=0;i<minis.length;i++){
      minis[i].classList.toggle('sel',i===st.selIdx);
    }
    if(minis[st.selIdx])minis[st.selIdx].scrollIntoView({block:'nearest'});
  }
  function syncQuery(){
    if(!sQ) return;
    var v=st.q;
    if(!v){sQ.innerHTML='<span class="ph">Tapez une lame…</span>';return;}
    sQ.textContent=v;
  }
  function setFam(f){
    st.fam=f;
    var all=chips.querySelectorAll('.chip');
    for(var i=0;i<all.length;i++){
      var a=all[i],on=a.getAttribute('data-fam')===f;
      a.classList.toggle('active',on);
      if(on)a.setAttribute('aria-selected','true');else a.removeAttribute('aria-selected');
    }
    render();
  }
  function openSearch(){
    sO.classList.add('open');
    document.body.style.overflow='hidden';
    render();
    setTimeout(function(){sI.focus();},100);
  }
  function closeSearch(){
    sO.classList.remove('open');
    document.body.style.overflow='';
    sI.value='';st.q='';st.fam='';
    setFam('');
    syncQuery();
  }
  document.getElementById('search-launch').addEventListener('click',openSearch);
  document.getElementById('s-close').addEventListener('click',closeSearch);
  sO.addEventListener('click',function(e){if(e.target===sO)closeSearch();});
  chips.addEventListener('click',function(e){
    var b=e.target.closest('.chip');if(!b)return;
    var f=b.getAttribute('data-fam');
    setFam(f);
    var bb=document.querySelector('.picker-body');if(bb)bb.scrollTop=0;
  });
  sI.addEventListener('input',function(){
    var v=this.value.trim().toLowerCase();
    st.q=v;
    clearTimeout(io);
    io=setTimeout(function(){render();syncQuery();},70);
  });
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){if(sO.classList.contains('open')){closeSearch();e.preventDefault();}return;}
    if(sO.classList.contains('open')){
      var minis;
      if(e.key==='Enter'){
        minis=sG.querySelectorAll('.mini');
        if(minis[st.selIdx])window.location.href=minis[st.selIdx].getAttribute('href');
        e.preventDefault();return;
      }
      if(e.key==='ArrowDown'){
        minis=sG.querySelectorAll('.mini');
        if(minis.length){st.selIdx=(st.selIdx+1)%minis.length;paintSel();}
        e.preventDefault();return;
      }
      if(e.key==='ArrowUp'){
        minis=sG.querySelectorAll('.mini');
        if(minis.length){st.selIdx=(st.selIdx-1+minis.length)%minis.length;paintSel();}
        e.preventDefault();return;
      }
      var inInput=e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA';
      if(!inInput){
        if(e.key==='Backspace'){
          st.q=st.q.slice(0,-1);
          sI.value=st.q;
          syncQuery();
          clearTimeout(io);
          io=setTimeout(render,70);
          e.preventDefault();return;
        }
        if(e.key.length===1&&/\p{L}|\p{N}/u.test(e.key)){
          st.q+=e.key.toLowerCase();
          sI.value=st.q;
          syncQuery();
          clearTimeout(io);
          io=setTimeout(render,70);
          e.preventDefault();return;
        }
      }
      return;
    }
    if(typeof PREV_URL!=='undefined'&&e.key==='ArrowLeft'){e.preventDefault();window.location.href=PREV_URL;}
    if(typeof NEXT_URL!=='undefined'&&e.key==='ArrowRight'){e.preventDefault();window.location.href=NEXT_URL;}
  });
  sG.addEventListener('mouseover',function(e){
    var m=e.target.closest('.mini');if(!m)return;
    var minis=sG.querySelectorAll('.mini');
    for(var i=0;i<minis.length;i++){
      if(minis[i]===m){st.selIdx=i;paintSel();break;}
    }
  });

  /* Grid keyboard navigation — landing + family pages */
  (function(){
    var grid=document.querySelector('.full-grid,.grid');
    if(!grid)return;
    var selected=null;
    function getCards(){return Array.prototype.slice.call(grid.querySelectorAll('a.mini'));}
    function getCols(){
      var cards=getCards();if(!cards.length)return 1;
      var firstTop=cards[0].getBoundingClientRect().top,cols=0;
      for(var i=0;i<cards.length;i++){
        if(Math.abs(cards[i].getBoundingClientRect().top-firstTop)<2)cols++;else break;
      }
      return cols||1;
    }
    function select(el){
      if(selected)selected.classList.remove('selected');
      selected=el;
      if(!el)return;
      el.classList.add('selected');
      el.focus();
      el.scrollIntoView({block:'nearest',behavior:'smooth'});
    }
    document.addEventListener('keydown',function(e){
      if(e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA')return;
      if(sO.classList.contains('open'))return;
      if(e.key!=='ArrowLeft'&&e.key!=='ArrowRight'&&e.key!=='ArrowUp'&&e.key!=='ArrowDown'&&e.key!=='Enter')return;
      var cards=getCards();if(!cards.length)return;
      var idx=selected?cards.indexOf(selected):-1;
      var cols=getCols();
      if(e.key==='ArrowRight'){if(idx<cards.length-1)select(cards[idx+1]);e.preventDefault();}
      if(e.key==='ArrowLeft'){if(idx>0)select(cards[idx-1]);e.preventDefault();}
      if(e.key==='ArrowDown'){if(idx+cols<cards.length)select(cards[idx+cols]);e.preventDefault();}
      if(e.key==='ArrowUp'){if(idx-cols>=0)select(cards[idx-cols]);e.preventDefault();}
      if(e.key==='Enter'&&selected){window.location.href=selected.getAttribute('href');e.preventDefault();}
    });
    grid.addEventListener('click',function(e){
      var m=e.target.closest('a.mini');if(m)select(m);
    });
  })();
})();
</script>
HTML;
}

// ==========================================================
// ROUTE: LANDING — full immersive grid
// ==========================================================
function page_landing(PDO $pdo, string $basePath): void {
    $families = $pdo->query("SELECT * FROM families ORDER BY sort_order ASC")->fetchAll();

    // Build grid with family separators
    $gridHtml = '';
    foreach ($families as $fam) {
        $cards = $pdo->prepare("SELECT * FROM cards WHERE family_key = ? ORDER BY sort_global ASC");
        $cards->execute([$fam['key']]);
        $cardsList = $cards->fetchAll();
        $count = count($cardsList);
        $accent = htmlspecialchars($fam['accent']);
        $elementSym = htmlspecialchars($fam['element_sym']);
        $elementLine = htmlspecialchars($fam['element_line']);
        $titleFull = htmlspecialchars($fam['title_full']);
        $desc = htmlspecialchars($fam['desc']);

        // Family separator
        $gridHtml .= <<<HTML
<div class="fam-sep" style="--ac:{$accent}">
  <div class="fam-sep-art">
    <div class="ring" style="border-color:{$accent}"></div>
    <div class="glyph" style="color:{$accent}">{$elementSym}</div>
  </div>
  <div class="fam-sep-body">
    <div class="lbl" style="color:{$accent}">{$elementLine}</div>
    <div class="name">{$titleFull}</div>
    <div class="desc">{$desc}</div>
  </div>
  <div class="fam-sep-count"><b>{$count}</b>LAMES</div>
</div>
HTML;

        // Cards
        foreach ($cardsList as $card) {
            $imgUrl = "{$basePath}/img/{$card['family_key']}/{$card['id']}.jpg";
            $cardUrl = "{$basePath}/card/{$card['id']}";
            $name = htmlspecialchars($card['name']);
            $num = str_pad((string)(int)$card['num'], 2, '0', STR_PAD_LEFT);
            $gridHtml .= <<<HTML
<a class="mini" href="{$cardUrl}" style="text-decoration:none;color:inherit">
  <div class="ph"><img src="{$imgUrl}" alt="{$name}" loading="lazy"></div>
  <div class="cap"><span class="nm">{$name}</span><span class="no">{$num}</span></div>
</a>
HTML;
        }
    }

    echo layout_head($basePath, 'Tarot Divinatoire — 78 Lames');
    echo layout_brand($basePath);
    echo '<div id="page-scroll">';
    echo <<<HTML
<div class="full-grid-head">
  <h1>Tarot <em>Divinatoire</em></h1>
  <p class="sub">Soixante-dix-huit lames, cinq familles, un seul voyage initiatique à travers le Tarot de Waite.</p>
</div>
<div class="full-grid">
{$gridHtml}
</div>
HTML;
    echo '</div>';
    echo layout_foot($basePath);
    echo layout_search($pdo, $basePath);
    echo '</body></html>';
}

// ==========================================================
// ROUTE: FAMILY — grid view
// ==========================================================
function page_family(PDO $pdo, string $familyKey, string $basePath): void {
    $fam = $pdo->prepare("SELECT * FROM families WHERE key = ?");
    $fam->execute([$familyKey]);
    $family = $fam->fetch();

    if (!$family) {
        http_response_code(404);
        echo '<h1>404 — Famille introuvable</h1>';
        return;
    }

    $cards = $pdo->prepare("SELECT * FROM cards WHERE family_key = ? ORDER BY sort_global ASC");
    $cards->execute([$familyKey]);
    $cardsList = $cards->fetchAll();
    $count = count($cardsList);

    $accent = htmlspecialchars($family['accent']);
    $name = htmlspecialchars($family['name']);
    $titleFull = htmlspecialchars($family['title_full']);
    $element = htmlspecialchars($family['element']);
    $desc = htmlspecialchars($family['desc']);
    $elementSym = htmlspecialchars($family['element_sym']);

    // Build title with <em> on last word
    $titleHtml = preg_replace('/(.+ )?(.+)$/', '$1<em>$2</em>', $name);

    $gridHtml = '';
    foreach ($cardsList as $card) {
        $imgUrl = "{$basePath}/img/{$card['family_key']}/{$card['id']}.jpg";
        $cardUrl = "{$basePath}/card/{$card['id']}";
        $cname = htmlspecialchars($card['name']);
        $num = str_pad((string)(int)$card['num'], 2, '0', STR_PAD_LEFT);
        $gridHtml .= <<<HTML
<a class="mini" href="{$cardUrl}" style="text-decoration:none;color:inherit">
  <div class="ph"><img src="{$imgUrl}" alt="{$cname}" loading="lazy"></div>
  <div class="cap"><span class="nm">{$cname}</span><span class="no">{$num}</span></div>
</a>
HTML;
    }

    $extraStyle = "<style>:root{--ac:{$accent}}</style>";

    echo layout_head($basePath, $titleFull . ' — Tarot Divinatoire', $extraStyle);
    echo <<<HTML
<a class="back" href="{$basePath}/">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
  Retour
</a>
<div id="page-scroll">
  <header class="grid-head">
    <div class="left">
      <h2>{$titleHtml}</h2>
      <p class="summary">{$desc}</p>
    </div>
    <div class="right"><b>{$count}</b>lames<br>élément {$element}</div>
  </header>
  <div class="grid">
  {$gridHtml}
  </div>
</div>
HTML;
    echo layout_foot($basePath);
    echo layout_search($pdo, $basePath);
    echo '</body></html>';
}

// ==========================================================
// HTML POST-PROCESSING — keywords 2-col + theme grid + icons
// ==========================================================
function restructure_html(string $html): string {
    // SVG icons for theme sections
    static $icons = [
        'amour'        => '<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z"/></svg></span>',
        'travail'      => '<span class="content-icon"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg></span>',
        'finances'     => '<span class="content-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4"/></svg></span>',
        'guidance'     => '<span class="content-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z"/></svg></span>',
        'interpretation'=> '<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>',
        'affirmation'  => '<span class="content-icon"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h12"/></svg></span>',
    ];

    // 0. Inject SVG icons into h2 headings
    $html = preg_replace_callback(
        '#<h2([^>]*)>(.*?)</h2>#is',
        function ($m) use ($icons) {
            $attrs = $m[1];
            $text  = $m[2];
            if (strpos($text, 'content-icon') !== false) return $m[0];
            $lower = mb_strtolower(strip_tags($text), 'UTF-8');
            $type  = '';
            if (mb_strpos($lower, 'amour') !== false)         $type = 'amour';
            elseif (mb_strpos($lower, 'travail') !== false)    $type = 'travail';
            elseif (mb_strpos($lower, 'finance') !== false)    $type = 'finances';
            elseif (mb_strpos($lower, 'guidance') !== false)   $type = 'guidance';
            elseif (mb_strpos($lower, 'interpr') !== false)    $type = 'interpretation';
            elseif (mb_strpos($lower, 'affirmation') !== false)$type = 'affirmation';
            if (!$type) return $m[0];
            return '<h2' . $attrs . '>' . $icons[$type] . '<span>' . $text . '</span></h2>';
        },
        $html
    );

    // 1. Keywords: transform h3+ul pairs into .kw-cols > .kw-col
    $html = preg_replace_callback(
        '#(<section[^>]*id="sec-mots-cles"[^>]*>)(.*?)(</section>)#is',
        function ($m) {
            $inner = $m[2];
            if (preg_match_all('#<h3>(.*?)</h3>\s*(<ul>.*?</ul>)#is', $inner, $pairs, PREG_SET_ORDER)) {
                $cols = '';
                foreach ($pairs as $p) {
                    $cols .= '<div class="kw-col"><h3>' . $p[1] . '</h3>' . $p[2] . '</div>';
                }
                $inner = preg_replace('#(<h3>.*?</h3>\s*<ul>.*?</ul>)+#is', '', $inner);
                $inner .= '<div class="kw-cols">' . $cols . '</div>';
            }
            return $m[1] . $inner . $m[3];
        },
        $html
    );

    // 2. Wrap theme sections (Amour/Travail/Finances/Guidance) in .theme-grid
    $themePattern = '#(<section[^>]*>\s*<h2[^>]*>[^<]*(?:et\s+l[\x27\x{2019}]amour|et\s+le\s+travail|et\s+les\s+finances|et\s+la\s+guidance)[^<]*</h2>[\s\S]*?</section>\s*)+#iu';
    $html = preg_replace_callback(
        $themePattern,
        function ($m) {
            return '<div class="theme-grid">' . $m[0] . '</div>';
        },
        $html
    );

    return $html;
}

// ==========================================================
// ROUTE: CARD — immersive detail
// ==========================================================
function page_card(PDO $pdo, string $cardId, string $basePath): void {
    $stmt = $pdo->prepare("SELECT c.*, f.name as family_name, f.title_full as family_title,
                                  f.accent, f.element, f.element_sym, f.element_line
                           FROM cards c
                           LEFT JOIN families f ON c.family_key = f.key
                           WHERE c.id = ?");
    $stmt->execute([$cardId]);
    $card = $stmt->fetch();

    if (!$card) {
        http_response_code(404);
        echo '<h1>404 — Carte introuvable</h1>';
        return;
    }

    // Get ES data
    $esStmt = $pdo->prepare("SELECT * FROM card_es WHERE card_id = ?");
    $esStmt->execute([$cardId]);
    $es = $esStmt->fetch();

    // Get global index
    $idxStmt = $pdo->prepare("SELECT COUNT(*) as pos FROM cards WHERE sort_global < ?");
    $idxStmt->execute([$card['sort_global']]);
    $globalIdx = (int)$idxStmt->fetch()['pos'];

    // Get family cards for thumbnails
    $famStmt = $pdo->prepare("SELECT id, family_key, name, num FROM cards WHERE family_key = ? ORDER BY sort_global ASC");
    $famStmt->execute([$card['family_key']]);
    $famCards = $famStmt->fetchAll();
    $inFam = 0;
    foreach ($famCards as $i => $fc) {
        if ($fc['id'] === $cardId) { $inFam = $i; break; }
    }

    // Prev/next cards (global loop)
    $totalCards = (int)$pdo->query("SELECT COUNT(*) FROM cards")->fetchColumn();
    $prevIdx = ($globalIdx - 1 + $totalCards) % $totalCards;
    $nextIdx = ($globalIdx + 1) % $totalCards;
    $prevStmt = $pdo->prepare("SELECT id, name FROM cards ORDER BY sort_global ASC LIMIT 1 OFFSET ?");
    $prevStmt->execute([$prevIdx]);
    $prevCard = $prevStmt->fetch();
    $nextStmt = $pdo->prepare("SELECT id, name FROM cards ORDER BY sort_global ASC LIMIT 1 OFFSET ?");
    $nextStmt->execute([$nextIdx]);
    $nextCard = $nextStmt->fetch();

    // Build URLs
    $imgUrl = "{$basePath}/img/{$card['family_key']}/{$card['id']}.jpg";
    $name = htmlspecialchars($card['name']);
    $accent = htmlspecialchars($card['accent']);
    $familyName = htmlspecialchars($card['family_name']);
    $element = htmlspecialchars($card['element']);
    $html = restructure_html($card['html'] ?? '<p>Pas de description disponible.</p>');
    $globalNum = str_pad((string)($globalIdx + 1), 2, '0', STR_PAD_LEFT);
    $inFamNum = $inFam + 1;
    $famCount = count($famCards);

    // ES badges
    $esHtml = '';
    if ($es) {
        $aff = $es['affirmation'] ?? '';
        $rep = $es['reponse'] ?? '';
        if ($aff) $esHtml .= '<span class="es-badge aff">« ' . htmlspecialchars($aff) . ' »</span>';
        if ($rep) $esHtml .= '<span class="es-badge resp"><span class="lbl">Réponse</span> ' . htmlspecialchars($rep) . '</span>';
        if ($esHtml) $esHtml = '<div class="es-badges">' . $esHtml . '</div>';
    }

    // Thumbnails
    $thumbsHtml = '';
    foreach ($famCards as $fc) {
        $tImgUrl = "{$basePath}/img/{$fc['family_key']}/{$fc['id']}.jpg";
        $tUrl = "{$basePath}/card/{$fc['id']}";
        $tName = htmlspecialchars($fc['name']);
        $current = ($fc['id'] === $cardId) ? ' current' : '';
        $thumbsHtml .= <<<HTML
<a class="d-thumb{$current}" href="{$tUrl}"><img src="{$tImgUrl}" alt="{$tName}" loading="lazy"></a>
HTML;
    }

    // Loop bar
    $prevUrl = "{$basePath}/card/{$prevCard['id']}";
    $nextUrl = "{$basePath}/card/{$nextCard['id']}";
    $prevName = htmlspecialchars($prevCard['name']);
    $nextName = htmlspecialchars($nextCard['name']);

    $extraStyle = "<style>:root{--ac:{$accent}}</style>";

    echo layout_head($basePath, $name . ' — Tarot Divinatoire', $extraStyle);
    echo <<<HTML
<a class="back" href="{$basePath}/suite/{$card['family_key']}">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
  Retour
</a>
<div class="d-loop">
  <a href="{$prevUrl}">← {$prevName}</a>
  <span class="pos"><b>{$globalNum}</b> / {$totalCards}</span>
  <a href="{$nextUrl}">{$nextName} →</a>
</div>
<div class="d-stage">
  <div class="d-hero">
    <img class="d-hero-img" src="{$imgUrl}" alt="{$name}">
  </div>
  <div class="d-panel">
    <div class="d-panel-inner">
      <div class="d-meta">
        <b>{$globalNum} / {$totalCards}</b>
        <span class="sep">·</span>
        <span>{$familyName} {$inFamNum}/{$famCount}</span>
        <span class="sep">·</span>
        <span>{$element}</span>
      </div>
      <h1 class="d-title"><em>{$name}</em></h1>
      {$esHtml}
      <div class="prose">
        {$html}
      </div>
      <div class="d-thumbs">
        {$thumbsHtml}
      </div>
    </div>
  </div>
</div>
<script>
const PREV_URL="{$prevUrl}", NEXT_URL="{$nextUrl}";
(function(){var cur=document.querySelector('.d-thumb.current');if(cur)cur.scrollIntoView({behavior:'smooth',inline:'center',block:'nearest'});})();
</script>
HTML;
    echo layout_search($pdo, $basePath);
    echo '</body></html>';
}

// ==========================================================
// ROUTE: SEARCH API
// ==========================================================
function api_search(PDO $pdo, string $basePath): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache');

    $q = trim($_GET['q'] ?? '');
    if (mb_strlen($q) < 1) {
        echo '[]';
        return;
    }

    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT id, family_key AS fk, name, num
        FROM cards
        WHERE name LIKE ? OR md LIKE ?
        ORDER BY
          CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
          sort_global ASC
        LIMIT 20
    ");
    $stmt->execute([$like, $like, $like . '%']);
    $results = $stmt->fetchAll();

    $out = array_map(function ($c) {
        return [
            'id'   => $c['id'],
            'fk'   => $c['fk'],
            'name' => $c['name'],
            'num'  => str_pad((string)(int)$c['num'], 2, '0', STR_PAD_LEFT),
        ];
    }, $results);

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================================
// ROUTE: IMAGE
// ==========================================================
function serve_image(string $imagePath, PDO $pdo): void {
    // Format: {family_key}/{card_id} or {family_key}/{card_id}.jpg
    $parts = explode('/', $imagePath);
    if (count($parts) !== 2) {
        http_response_code(400);
        echo 'Bad image path';
        return;
    }

    $familyKey = $parts[0];
    $cardId = pathinfo($parts[1], PATHINFO_FILENAME);

    $stmt = $pdo->prepare("SELECT img FROM cards WHERE family_key = ? AND id = ?");
    $stmt->execute([$familyKey, $cardId]);
    $result = $stmt->fetch();

    if (!$result || !$result['img']) {
        http_response_code(404);
        echo 'Image not found';
        return;
    }

    $data = $result['img'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($data) ?: 'image/jpeg';

    header("Content-Type: {$mime}");
    header('Content-Length: ' . strlen($data));
    header('Cache-Control: public, max-age=31536000, immutable');
    echo $data;
    exit;
}

// ==========================================================
// ROUTE: ICON
// ==========================================================
function serve_icon(): void {
    $path = __DIR__ . '/icon.png';
    if (!file_exists($path)) {
        http_response_code(404);
        echo 'Icon not found';
        return;
    }
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}