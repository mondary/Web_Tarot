<?php
declare(strict_types=1);

/** Convert the V9 SQLite vault into immutable Capacitor web assets. */
$root = dirname(__DIR__, 3);
$source = $root . '/src/website/v9/vault.sqlite';
$web = $root . '/src/mobile/www';
$template = $root . '/src/mobile/src';

if (!is_file($source)) { fwrite(STDERR, "V9 vault not found.\n"); exit(1); }

function remove_tree(string $path): void {
    if (!is_dir($path)) return;
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($items as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    rmdir($path);
}
function copy_tree(string $from, string $to): void {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
        $target = $to . '/' . substr($item->getPathname(), strlen($from) + 1);
        if ($item->isDir()) { if (!is_dir($target)) mkdir($target, 0775, true); }
        else copy($item->getPathname(), $target);
    }
}

remove_tree($web);
mkdir($web, 0775, true);
copy_tree($template, $web);

$db = new PDO('sqlite:' . $source, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $db->query("SELECT path, data FROM vault WHERE path='/app-data.json' OR path LIKE '/img/%' OR path LIKE '/fonts/%'");
$count = 0;
foreach ($rows as $row) {
    $relative = ltrim((string) $row['path'], '/');
    $target = $web . '/' . $relative;
    $dir = dirname($target);
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    file_put_contents($target, $row['data']);
    $count++;
}
if (!is_file($web . '/app-data.json') || $count < 80) { fwrite(STDERR, "Incomplete vault export.\n"); exit(1); }
fwrite(STDOUT, "Mobile web bundle ready: {$count} assets.\n");
