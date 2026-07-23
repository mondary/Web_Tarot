<?php
// Construit tarot.sqlite (images BLOB incluses) depuis website/cards/.
// Usage : php build_db.php

const CARDS_DIR = __DIR__ . '/../website/cards';
const DB_PATH   = __DIR__ . '/tarot.sqlite';
const IMG_MAX_W = 420;
const IMG_QUALITY = 80;

const VS = "\u{FE0E}";

const FAMILY_META = [
    'a' => ['key'=>'majors','name'=>'Arcanes Majeurs','short'=>'Majeurs','element'=>'Éther',
            'element_sym'=>'✦'.VS,'accent'=>'#c9a227','order'=>0,'title_full'=>'Arcanes Majeurs',
            'element_line'=>'les 22 arcanes du chemin initiatique',
            'desc'=>"Les 22 arcanes majeurs racontent les grandes étapes et leçons de la vie. Ils parlent du destin, du cheminement spirituel et de l'accomplissement de soi."],
    'b' => ['key'=>'batons','name'=>'Bâtons','short'=>'Bâtons','element'=>'Feu',
            'element_sym'=>'🜂','accent'=>'#c45a2e','order'=>1,'title_full'=>'Les Bâtons',
            'element_line'=>"associés à l'élément Feu",
            'desc'=>"Les Lames des Bâtons symbolisent ce qui motive et dynamise. Les Bâtons vous parlent de vos désirs et de votre élan vital."],
    'e' => ['key'=>'epees','name'=>'Épées','short'=>'Épées','element'=>'Air',
            'element_sym'=>'🜁','accent'=>'#8fa3b5','order'=>3,'title_full'=>'Les Épées',
            'element_line'=>"associées à l'élément Air",
            'desc'=>"Les Lames des Épées symbolisent les idées et l'intellect. Les Épées évoquent votre esprit rationnel, la réflexion et la communication."],
    'c' => ['key'=>'coupes','name'=>'Coupes','short'=>'Coupes','element'=>'Eau',
            'element_sym'=>'🜄','accent'=>'#5b8fa3','order'=>2,'title_full'=>'Les Coupes',
            'element_line'=>"associées à l'élément Eau",
            'desc'=>"Les Lames des Coupes symbolisent les émotions et les sentiments. Les Coupes vous parlent de ce que vous ressentez et de vos relations."],
    'd' => ['key'=>'deniers','name'=>'Deniers','short'=>'Deniers','element'=>'Terre',
            'element_sym'=>'🜃','accent'=>'#7a9b5e','order'=>4,'title_full'=>'Les Deniers',
            'element_line'=>"associés à l'élément Terre",
            'desc'=>"Les Lames des Deniers évoquent le plan matériel de l'existence, notamment l'argent et les possessions matérielles."],
];

const MAJOR_NAMES = ['00'=>'Le Fou','01'=>'Le Bateleur','02'=>'La Papesse','03'=>"L'Impératrice",
    '04'=>"L'Empereur",'05'=>'Le Pape','06'=>'Les Amoureux','07'=>'Le Chariot',
    '08'=>'La Force','09'=>"L'Ermite",'10'=>'La Roue de Fortune','11'=>'La Justice',
    '12'=>'Le Pendu','13'=>"L'Arcane Sans Nom",'14'=>'Tempérance','15'=>'Le Diable',
    '16'=>'La Maison Dieu','17'=>"L'Étoile",'18'=>'La Lune','19'=>'Le Soleil',
    '20'=>'Le Jugement','21'=>'Le Monde'];

const MINOR_NAMES = ['01'=>'As','02'=>'Deux','03'=>'Trois','04'=>'Quatre','05'=>'Cinq',
    '06'=>'Six','07'=>'Sept','08'=>'Huit','09'=>'Neuf','10'=>'Dix',
    '11'=>'Valet','12'=>'Cavalier','13'=>'Reine','14'=>'Roi'];

const ORDER = ['a','b','e','c','d'];

// ---------------------------------------------------------------------------
// Markdown -> HTML
// ---------------------------------------------------------------------------
function slugify(string $s): string {
    $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s)
         ?? strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return 'sec-'.trim($s, '-');
}

function inline_md(string $s): string {
    $s = htmlspecialchars($s, ENT_QUOTES|ENT_HTML5);
    $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
    $s = preg_replace('/(^|[^*])\*([^*]+)\*(?!\*)/', '$1<em>$2</em>', $s);
    $s = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/',
                     '<a href="$2" target="_blank" rel="noopener">$1</a>', $s);
    return $s;
}

function render_md(string $md): string {
    $md = preg_replace('/\*Source :[\s\S]*$/i', '', $md);
    $md = preg_replace('/\*Illustration :[^\n]*\*/', '', $md);
    $lines = array_values(array_filter(explode("\n", $md),
        fn($l) => !preg_match('/^\s*!\[/', $l)));

    $out = [];
    $list_open = $section_open = $skipped_h1 = false;

    $close_list = function() use (&$list_open, &$out) {
        if ($list_open) { $out[] = '</ul>'; $list_open = false; }
    };
    $close_section = function() use (&$section_open, &$out, $close_list) {
        $close_list();
        if ($section_open) { $out[] = '</section>'; $section_open = false; }
    };

    $i = 0;
    $n = count($lines);
    while ($i < $n) {
        $t = trim($lines[$i]);
        if ($t === '') { $close_list(); $i++; continue; }
        if (!$skipped_h1 && preg_match('/^# /', $t)) { $skipped_h1 = true; $i++; continue; }
        if (str_starts_with($t, '## ')) {
            $close_section(); $section_open = true;
            $heading = substr($t, 3);
            $out[] = '<section id="'.slugify($heading).'"><h2>'.htmlspecialchars($heading, ENT_HTML5).'</h2>';
            $i++; continue;
        }
        if (str_starts_with($t, '### ')) {
            $close_list(); $out[] = '<h3>'.htmlspecialchars(substr($t, 4), ENT_HTML5).'</h3>';
            $i++; continue;
        }
        if (str_starts_with($t, '> ')) {
            $close_list(); $buf = [substr($t, 2)]; $i++;
            while ($i < $n && str_starts_with(trim($lines[$i]), '> ')) {
                $buf[] = trim(substr(trim($lines[$i]), 2)); $i++;
            }
            $out[] = '<blockquote><p>'.inline_md(implode(' ', $buf)).'</p></blockquote>';
            continue;
        }
        if (str_starts_with($t, '- ')) {
            if (!$list_open) { $out[] = '<ul>'; $list_open = true; }
            $out[] = '<li>'.inline_md(substr($t, 2)).'</li>'; $i++; continue;
        }
        if (preg_match('/^---\s*$/', $t)) { $close_section(); $out[] = '<hr>'; $i++; continue; }
        if (preg_match('/^\*\*[^*]+:\*\*/', $t)) {
            $close_list(); $out[] = '<p class="field">'.inline_md($t).'</p>'; $i++; continue;
        }
        $close_list(); $buf = [$t]; $i++;
        while ($i < $n) {
            $nt = trim($lines[$i]);
            if ($nt === '' || preg_match('/^(#{1,3} |- |> |---)/', $nt) || preg_match('/^\*\*[^*]+:\*\*/', $nt)) break;
            $buf[] = $nt; $i++;
        }
        $out[] = '<p>'.inline_md(implode(' ', $buf)).'</p>';
    }
    $close_section();
    return implode("\n", $out);
}

function reorder_classique(string $html): string {
    // Ne garder que les parts qui contiennent vraiment une <section> (évite que
    // le <hr> final isolé ne se retrouve collé à un </section> et ne casse la
    // <div class="prose"> côté navigateur).
    $parts = explode('</section>', $html);
    $sections = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '' || strpos($p, '<section') === false) continue;
        $s = $p.'</section>';
        if (preg_match('/<h2>([\s\S]*?)<\/h2>/', $s, $m)) {
            $h = trim($m[1]);
        } else { $h = ''; }
        $pri = preg_match('/mots?\s*-?\s*cl/i', $h) ? 0
             : (preg_match('/interpr.tation/i', $h) ? 1
             : (preg_match('/signification/i', $h) ? 99
             : (preg_match('/description/i', $h) ? 100 : 50)));
        $sections[] = [$pri, $s];
    }
    usort($sections, fn($a, $b) => $a[0] <=> $b[0]);
    return implode("\n", array_map(fn($x) => $x[1], $sections));
}

// ---------------------------------------------------------------------------
// Parsing ES
// ---------------------------------------------------------------------------
function parse_es(string $content): array {
    $grab = function(string $pat) use ($content): ?string {
        return preg_match($pat.'i', $content, $m) ? trim($m[1]) : null;
    };
    $reponse = $grab('/\*\*RÉPONSE\s*:\*\*\s*(.+)/');
    $affirmation = $grab('/\*\*Affirmation\s*:\*\*\s*>?\s*(.+)/');

    $kw_up = $kw_down = null;
    if (preg_match('/\*\*Mots-clés\s*\(à l\'endroit\)\s*:\*\*\s*\n([\s\S]*?)(?=\n\n|\n\*\*|$)/i', $content, $m)) {
        $kw_up = implode(', ', array_filter(array_map('trim', explode(',', $m[1]))));
    }
    if (preg_match('/\*\*Mots-clés\s*\(à l\'envers\)\s*:\*\*\s*\n([\s\S]*?)(?=\n\n|\n\*\*|$)/i', $content, $m)) {
        $kw_down = implode(', ', array_filter(array_map('trim', explode(',', $m[1]))));
    }
    return ['reponse'=>$reponse, 'affirmation'=>$affirmation,
            'keywords_up'=>$kw_up, 'keywords_down'=>$kw_down];
}

// ---------------------------------------------------------------------------
// Parsing associations (fichiers *_associations.md)
// Format : ## Section\n- **CarteA + CarteB** : texte\n...
// ---------------------------------------------------------------------------
function parse_associations(string $md): array {
    $md = preg_replace('/\*Source :[\s\S]*?\*/', '', $md);
    $md = preg_replace('/^>\s.*$/m', '', $md);
    $out = [];
    $lines = explode("\n", $md);
    $section = '';
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') continue;
        if (preg_match('/^##\s+(.+)$/', $t, $m)) {
            $section = trim($m[1]);
            continue;
        }
        if (preg_match('/^-\s+\*\*(.+?)\*\*\s*:\s*(.+)$/', $t, $m)) {
            $pair = trim($m[1]);
            $text = trim($m[2]);
            if (strpos($pair, '+') === false) continue;
            $out[] = ['section'=>$section, 'pair'=>$pair, 'text'=>$text];
        }
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Chargement des CARD_SYMBOLS (depuis card_symbols.json, extrait de la V1)
// ---------------------------------------------------------------------------
function load_symbols(): array {
    $p = __DIR__.'/card_symbols.json';
    if (!file_exists($p)) {
        fwrite(STDERR, "  ⚠ card_symbols.json introuvable — étape symbols ignorée\n");
        return [];
    }
    return json_decode(file_get_contents($p), true) ?: [];
}

// ---------------------------------------------------------------------------
// Image -> BLOB JPEG
// ---------------------------------------------------------------------------
function image_to_blob(string $path): ?string {
    try {
        $img = imagecreatefromstring(file_get_contents($path));
        if ($img === false) return null;
        $w = imagesx($img);
        if ($w > IMG_MAX_W) {
            $h = imagesy($img);
            $new_h = (int)($h * IMG_MAX_W / $w);
            $resized = imagecreatetruecolor(IMG_MAX_W, $new_h);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, IMG_MAX_W, $new_h, $w, $h);
            $img = $resized;
        }
        ob_start();
        imagejpeg($img, null, IMG_QUALITY);
        return ob_get_clean();
    } catch (Throwable $e) {
        echo "  ⚠ image illisible : ".basename($path)."\n";
        return null;
    }
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------
function main(): void {
    if (!is_dir(CARDS_DIR)) {
        fwrite(STDERR, "Dossier introuvable : ".CARDS_DIR."\n");
        exit(1);
    }
    if (file_exists(DB_PATH)) unlink(DB_PATH);

    $pdo = new PDO('sqlite:'.DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("DROP TABLE IF EXISTS families; DROP TABLE IF EXISTS cards; DROP TABLE IF EXISTS card_es;
        DROP TABLE IF EXISTS card_symbols; DROP TABLE IF EXISTS card_associations;
        CREATE TABLE families(
            key TEXT PRIMARY KEY, name TEXT, title_full TEXT, short TEXT,
            element TEXT, element_sym TEXT, element_line TEXT, desc TEXT,
            signs TEXT, accent TEXT, sort_order INTEGER);
        CREATE TABLE cards(
            id TEXT PRIMARY KEY, family_key TEXT, num INTEGER, name TEXT,
            img BLOB, md TEXT, html TEXT, sort_global INTEGER,
            FOREIGN KEY(family_key) REFERENCES families(key));
        CREATE TABLE card_es(
            card_id TEXT PRIMARY KEY, reponse TEXT, affirmation TEXT,
            keywords_up TEXT, keywords_down TEXT,
            FOREIGN KEY(card_id) REFERENCES cards(id));
        CREATE TABLE card_symbols(
            card_id TEXT, idx INTEGER, label TEXT, short TEXT, side TEXT,
            x REAL, y REAL, descr TEXT,
            FOREIGN KEY(card_id) REFERENCES cards(id));
        CREATE TABLE card_associations(
            card_id TEXT, section TEXT, pair TEXT, descr TEXT,
            FOREIGN KEY(card_id) REFERENCES cards(id));
        CREATE INDEX idx_cards_family ON cards(family_key);
        CREATE INDEX idx_cards_global ON cards(sort_global);
        CREATE INDEX idx_symbols_card ON card_symbols(card_id);
        CREATE INDEX idx_asso_card ON card_associations(card_id);");

    // Familles
    $stmt = $pdo->prepare("INSERT INTO families VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    foreach (ORDER as $p) {
        $m = FAMILY_META[$p];
        $stmt->execute([$m['key'], $m['name'], $m['title_full'], $m['short'], $m['element'],
                        $m['element_sym'], $m['element_line'], $m['desc'], json_encode([]),
                        $m['accent'], $m['order']]);
    }

    // Cartes
    $md_files = array_filter(scandir(CARDS_DIR), fn($f) =>
        str_ends_with($f, '.md')
        && !preg_match('/_(symbols|symboles_pcd|associations|ES|affirmations)\.md$/', $f));
    sort($md_files);

    $global_idx = 0;
    $stmt_card = $pdo->prepare("INSERT INTO cards VALUES (?,?,?,?,?,?,?,?)");
    $stmt_es   = $pdo->prepare("INSERT INTO card_es VALUES (?,?,?,?,?)");
    $stmt_sym  = $pdo->prepare("INSERT INTO card_symbols VALUES (?,?,?,?,?,?,?,?)");
    $stmt_asso = $pdo->prepare("INSERT INTO card_associations VALUES (?,?,?,?)");
    $symbols   = load_symbols();

    foreach ($md_files as $fname) {
        if (!preg_match('/^([abecd])_(\d{2})_(.+)\.md$/', $fname, $m)) continue;
        [$full, $prefix, $num_str, $slug] = $m;
        $meta = FAMILY_META[$prefix];
        $base = "{$prefix}_{$num_str}_{$slug}";
        $num = (int)$num_str;

        if ($prefix === 'a') {
            $name = MAJOR_NAMES[$num_str] ?? $slug;
        } else {
            $article = preg_match('/^[AEIOUYÉÈÊÀ]/', $meta['name']) ? "d'" : 'de ';
            $name = (MINOR_NAMES[$num_str] ?? $slug).' '.$article.$meta['name'];
        }

        $md = file_get_contents(CARDS_DIR.'/'.$fname);
        $html = reorder_classique(render_md($md));

        // Image principale
        $blob = null;
        foreach (['.jpg', '.png', '.webp'] as $ext) {
            $p = CARDS_DIR."/{$base}{$ext}";
            if (file_exists($p)) { $blob = image_to_blob($p); break; }
        }

        $stmt_card->execute([$base, $meta['key'], $num, $name, $blob, $md, $html, $global_idx]);

        // ES
        $es_file = CARDS_DIR."/{$base}_ES.md";
        if (file_exists($es_file)) {
            $es = parse_es(file_get_contents($es_file));
            $stmt_es->execute([$base, $es['reponse'], $es['affirmation'],
                               $es['keywords_up'], $es['keywords_down']]);
        }

        // Symbols (depuis le JSON extrait de la V1)
        if (isset($symbols[$base])) {
            foreach ($symbols[$base] as $i => $s) {
                $label = $s['label'] ?? '';
                // Filtrer le bruit copyright/warning
                if (preg_match('/copyright|warning|watermark/i', $label)) continue;
                $stmt_sym->execute([
                    $base, $i, $label,
                    $s['short'] ?? '', $s['side'] ?? 'l',
                    (float)($s['x'] ?? 0), (float)($s['y'] ?? 0),
                    $s['desc'] ?? '',
                ]);
            }
        }

        // Associations
        $asso_file = CARDS_DIR."/{$base}_associations.md";
        if (file_exists($asso_file)) {
            foreach (parse_associations(file_get_contents($asso_file)) as $a) {
                $stmt_asso->execute([$base, $a['section'], $a['pair'], $a['text']]);
            }
        }

        $global_idx++;
    }

    $n_cards = $pdo->query("SELECT COUNT(*) FROM cards")->fetchColumn();
    $n_es = $pdo->query("SELECT COUNT(*) FROM card_es")->fetchColumn();
    $n_sym = $pdo->query("SELECT COUNT(*) FROM card_symbols")->fetchColumn();
    $n_asso = $pdo->query("SELECT COUNT(*) FROM card_associations")->fetchColumn();
    $size = filesize(DB_PATH);

    echo "✓ tarot.sqlite créé (".round($size/1024/1024, 1)." Mo)\n";
    echo "  - 5 familles\n";
    echo "  - {$n_cards} cartes (images BLOB incluses)\n";
    echo "  - {$n_es} fiches ES\n";
    echo "  - {$n_sym} annotations symboliques\n";
    echo "  - {$n_asso} associations\n";
}

main();
