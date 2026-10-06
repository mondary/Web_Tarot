<?php
declare(strict_types=1);

/** Render the actual PHP website into offline assets for Android and iOS. */
$root = dirname(__DIR__, 3);
$source = $root . '/src/website';
$web = $root . '/src/mobile/www';

function replace_once(string $html, string $from, string $to): string {
    $count = substr_count($html, $from);
    if ($count !== 1) throw new RuntimeException("Expected one occurrence of mobile URL pattern ($count): $from");
    return str_replace($from, $to, $html);
}

// Render the production page, rather than maintaining a second mobile design.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_ACCEPT_ENCODING'] = '';
ob_start();
require $source . '/index.php';
$html = (string) ob_get_clean();

// PHP routes become ordinary local assets. Keep the website source untouched.
$html = replace_once($html, '/index.php?js=spreads&amp;v=', '/tarot-spreads.js?v=');
$html = replace_once($html, "B+'/index.php?img='+encodeURIComponent(c.id+'.jpg')+'&v='+V", "B+'/img/'+encodeURIComponent(c.id+'.jpg')+'?v='+V");
$html = replace_once($html, "B+'/index.php?deckimg='+DECK+'/'+encodeURIComponent(id+'.jpg')+'&v='+V", "B+'/decks/'+DECK+'/'+encodeURIComponent(id+'.jpg')+'?v='+V");
$html = replace_once($html, "B+'/index.php?assocs='+encodeURIComponent(id)", "B+'/assocs/'+encodeURIComponent(id)+'.json'");
$html = replace_once($html, "B+'/index.php?svg='+f.key", "B+'/svg/'+f.key+'.svg'");
$html = replace_once($html, 'const u=location.href;', "const u='https://mondary.design/pk/-Games-cards/tarot/'+location.search;");
$html = replace_once($html, "if('serviceWorker' in navigator)navigator.serviceWorker.register('sw.js', {updateViaCache:'none'});", '');
$html = str_replace('/index.php?font=', '/fonts/', $html, $fontCount);
if ($fontCount !== 4) throw new RuntimeException("Expected four font URLs, found $fontCount");
if (str_contains($html, '/index.php?')) throw new RuntimeException('Unconverted PHP route in mobile page');

$db = new PDO('sqlite:' . $source . '/vault.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $db->query("SELECT path, data FROM vault WHERE path LIKE '/img/%' OR path LIKE '/decks/%' OR path LIKE '/fonts/%' OR path LIKE '/svg/%' OR path LIKE '/cards/%/associations.json'");

function remove_tree(string $path): void {
    if (!is_dir($path)) return;
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($items as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    rmdir($path);
}

remove_tree($web);
mkdir($web, 0775, true);
file_put_contents($web . '/index.html', $html);
copy($source . '/tarot-spreads.js', $web . '/tarot-spreads.js');
copy($source . '/icon-192.png', $web . '/icon-192.png');
copy($source . '/icon-512.png', $web . '/icon-512.png');
copy($source . '/manifest.json', $web . '/manifest.json');
copy($root . '/src/mobile/src/privacy.html', $web . '/privacy.html');

$count = 0;
foreach ($rows as $row) {
    $path = (string) $row['path'];
    $relative = preg_replace('#^/cards/([^/]+)/associations\.json$#', '/assocs/$1.json', $path);
    $target = $web . $relative;
    if (!is_dir(dirname($target))) mkdir(dirname($target), 0775, true);
    file_put_contents($target, $row['data']);
    $count++;
}
if ($count < 300 || !is_file($web . '/img/a_00_Fou.jpg')) throw new RuntimeException("Incomplete mobile export: $count assets");
fwrite(STDOUT, "Actual website exported for mobile: $count offline assets.\n");
