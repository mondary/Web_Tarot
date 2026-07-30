<?php
$self    = basename(__FILE__);
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) ?: __DIR__;
$reqPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$reqPath = preg_replace('#/' . preg_quote($self, '#') . '$#', '', $reqPath);

$dir = realpath($docRoot . '/' . $reqPath);
if ($dir === false || !is_dir($dir) || strncmp($dir, $docRoot, strlen($docRoot)) !== 0) {
    $dir = __DIR__;
}
$base = '/' . trim(str_replace('\\', '/', substr($dir, strlen($docRoot))), '/');
if ($base === '/') $base = '/' . basename($dir);

$groups = ['hash' => [], 'pk' => [], 'other' => [], 'files' => []];

foreach (array_diff(scandir($dir), ['.', '..']) as $name) {
    if ($name[0] === '.' || $name === $self) continue;
    $path  = $dir . '/' . $name;
    $isDir = is_dir($path);
    $entry = [
        'name'  => $name,
        'mtime' => filemtime($path),
        'isDir' => $isDir,
        'size'  => $isDir ? null : filesize($path),
        'count' => $isDir ? max(0, count(scandir($path)) - 2) : null,
    ];
    if      ($isDir && ($name[0] === '#' || $name[0] === '_'))  $groups['hash'][]  = $entry;
    elseif  ($isDir && stripos($name, 'pk') === 0)  $groups['pk'][]    = $entry;
    elseif  ($isDir)                                $groups['other'][] = $entry;
    else                                            $groups['files'][] = $entry;
}
foreach ($groups as &$g) usort($g, fn($a, $b) => strcasecmp($a['name'], $b['name']));
unset($g);

function fmt_size(int $b): string {
    if ($b < 1024) return $b . ' o';
    foreach (['Ko', 'Mo', 'Go'] as $u) { $b /= 1024; if ($b < 1024) return round($b, $b < 10 ? 1 : 0) . ' ' . $u; }
    return round($b, 1) . ' To';
}
function fmt_date(int $t): string {
    $mois = ['jan', 'fév', 'mar', 'avr', 'mai', 'juin', 'juil', 'août', 'sep', 'oct', 'nov', 'déc'];
    return date('j', $t) . ' ' . $mois[date('n', $t) - 1] . ' ' . date('Y', $t);
}
function row(array $e, string $strip = '', string $mark = ''): void {
    $href  = rawurlencode($e['name']) . ($e['isDir'] ? '/' : '');
    $label = $strip !== '' ? ltrim($e['name'], $strip) : $e['name'];
    $mark  = $mark ?: $strip;
    $meta  = $e['isDir']
        ? ($e['count'] === 1 ? '1 élément' : $e['count'] . ' éléments')
        : fmt_size($e['size']);
    ?>
    <a class="row" href="<?= htmlspecialchars($href) ?>" data-name="<?= htmlspecialchars(strtolower($e['name'])) ?>">
        <span class="row-name"><?php if ($mark !== ''): ?><span class="row-prefix"><?= htmlspecialchars($mark) ?></span><?php endif; ?><?= htmlspecialchars($label) ?><?php if ($e['isDir']): ?><span class="row-slash">/</span><?php endif; ?></span>
        <span class="leader" aria-hidden="true"></span>
        <span class="row-meta"><span class="row-count"><?= $meta ?></span><span class="row-date"><?= fmt_date($e['mtime']) ?></span></span>
    </a>
    <?php
}
$sections = [
    ['key' => 'hash',  'mark' => '#',  'title' => 'Collections',  'strip' => '_#'],
    ['key' => 'pk',    'mark' => 'pk', 'title' => 'Applications', 'strip' => 'pk'],
    ['key' => 'other', 'mark' => '~',  'title' => 'Dossiers',     'strip' => ''],
    ['key' => 'files', 'mark' => '·',  'title' => 'Fichiers',     'strip' => ''],
];
$total = array_sum(array_map('count', $groups));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Index de <?= htmlspecialchars($base) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Spline+Sans+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --paper:  #EDEFF2;
    --card:   #F8F9FA;
    --ink:    #16181D;
    --muted:  #7A8089;
    --hair:   #D4D8DE;
    --accent: #2038E0;
    --mono: "Spline Sans Mono", ui-monospace, monospace;
    --serif: "Instrument Serif", Georgia, serif;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html { -webkit-text-size-adjust: 100%; }
body {
    background: var(--paper);
    color: var(--ink);
    font-family: var(--mono);
    font-size: 15px;
    line-height: 1.5;
    padding: clamp(20px, 5vw, 64px) clamp(16px, 5vw, 48px) 96px;
}
.sheet { max-width: 780px; margin: 0 auto; }

/* ---- entête ---- */
header { margin-bottom: clamp(32px, 6vw, 56px); }
.eyebrow {
    font-size: 12px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 10px;
}
h1 {
    font-family: var(--serif);
    font-weight: 400;
    font-size: clamp(44px, 10vw, 76px);
    line-height: 1;
    letter-spacing: -0.01em;
}
h1 em { font-style: italic; color: var(--accent); }
.headline-rule {
    margin-top: 22px;
    border: 0;
    border-top: 1px solid var(--ink);
}
.headbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-top: 10px;
    font-size: 12.5px;
    color: var(--muted);
}
.search {
    appearance: none;
    border: 0;
    background: transparent;
    border-bottom: 1px dotted var(--muted);
    font: inherit;
    color: var(--ink);
    padding: 4px 2px;
    width: min(220px, 50vw);
    border-radius: 0;
}
.search::placeholder { color: var(--muted); }
.search:focus { outline: none; border-bottom: 1px solid var(--accent); }
.search:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }

/* ---- sections ---- */
section { margin-bottom: clamp(28px, 5vw, 44px); }
.sec-head {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 6px;
}
.sec-mark {
    font-family: var(--serif);
    font-style: italic;
    font-size: 30px;
    line-height: 1;
    color: var(--accent);
    min-width: 34px;
}
.sec-title {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.sec-n { font-size: 12px; color: var(--muted); }

/* ---- lignes ---- */
.row {
    display: flex;
    align-items: baseline;
    gap: 12px;
    padding: 9px 8px 9px 46px;
    text-decoration: none;
    color: inherit;
    border-radius: 6px;
}
.row:hover, .row:focus-visible { background: var(--card); }
.row:focus-visible { outline: 2px solid var(--accent); outline-offset: -2px; }
.row-name { font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.row-prefix { color: var(--muted); font-weight: 400; }
.row-slash { color: var(--accent); }
.row:hover .row-name { color: var(--accent); }
.leader {
    flex: 1;
    min-width: 24px;
    border-bottom: 2px dotted var(--hair);
    transform: translateY(-4px);
}
.row:hover .leader { border-bottom-color: var(--accent); }
.row-meta {
    display: flex;
    gap: 18px;
    font-size: 12.5px;
    color: var(--muted);
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}
.row-count { min-width: 82px; text-align: right; }

.empty { padding: 24px 8px; color: var(--muted); font-size: 13px; display: none; }
body.no-result .empty { display: block; }

footer {
    margin-top: 56px;
    padding-top: 12px;
    border-top: 1px solid var(--hair);
    font-size: 12px;
    color: var(--muted);
    display: flex;
    justify-content: space-between;
    gap: 16px;
}
footer a { color: inherit; }

/* ---- mobile ---- */
@media (max-width: 560px) {
    .row { padding-left: 8px; flex-wrap: wrap; row-gap: 2px; }
    .leader { display: none; }
    .row-name { flex: 1 1 100%; }
    .row-meta { flex: 1; justify-content: space-between; }
    .row-count { min-width: 0; text-align: left; }
    .sec-mark { min-width: 0; }
    .headbar { flex-direction: column; align-items: flex-start; }
}

/* ---- apparition ---- */
@media (prefers-reduced-motion: no-preference) {
    section, header { animation: rise .45s ease both; }
    section:nth-of-type(1) { animation-delay: .05s; }
    section:nth-of-type(2) { animation-delay: .1s; }
    section:nth-of-type(3) { animation-delay: .15s; }
    section:nth-of-type(4) { animation-delay: .2s; }
    @keyframes rise { from { opacity: 0; transform: translateY(8px); } }
}
</style>
</head>
<body>
<div class="sheet">
    <header>
        <p class="eyebrow">Index du serveur</p>
        <h1>Index <em>de <?= htmlspecialchars($base) ?></em></h1>
        <hr class="headline-rule">
        <div class="headbar">
            <span><?= $total ?> entrées · mis à jour le <?= fmt_date(time()) ?></span>
            <input class="search" id="q" type="search" placeholder="Filtrer…" aria-label="Filtrer les entrées">
        </div>
    </header>

    <?php foreach ($sections as $s): if (!$groups[$s['key']]) continue; ?>
    <section data-section>
        <div class="sec-head">
            <span class="sec-mark"><?= htmlspecialchars($s['mark']) ?></span>
            <span class="sec-title"><?= $s['title'] ?></span>
            <span class="sec-n" data-n><?= count($groups[$s['key']]) ?></span>
        </div>
        <?php foreach ($groups[$s['key']] as $e) row($e, $s['strip'], $s['mark']); ?>
    </section>
    <?php endforeach; ?>

    <p class="empty">Aucune entrée ne correspond au filtre.</p>

    <footer>
        <a href="../">← Dossier parent</a>
        <span><?= htmlspecialchars($_SERVER['SERVER_NAME'] ?? '') ?></span>
    </footer>
</div>

<script>
const q = document.getElementById('q');
const rows = [...document.querySelectorAll('.row')];
const sections = [...document.querySelectorAll('[data-section]')];
q.addEventListener('input', () => {
    const v = q.value.trim().toLowerCase();
    let any = false;
    rows.forEach(r => {
        const show = !v || r.dataset.name.includes(v);
        r.style.display = show ? '' : 'none';
        if (show) any = true;
    });
    sections.forEach(s => {
        const visible = [...s.querySelectorAll('.row')].filter(r => r.style.display !== 'none');
        s.style.display = visible.length ? '' : 'none';
        s.querySelector('[data-n]').textContent = visible.length;
    });
    document.body.classList.toggle('no-result', !any);
});
</script>
</body>
</html>
