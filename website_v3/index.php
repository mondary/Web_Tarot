<?php
// Tarot Divinatoire — v3 SQLite + HTMX (single-file PHP router)
// Lancement : php -S 127.0.0.1:5050 index.php

declare(strict_types=1);

const DB_PATH = __DIR__.'/tarot.sqlite';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        if (!file_exists(DB_PATH)) {
            http_response_code(503);
            die("tarot.sqlite introuvable. Lancez d'abord : php build_db.php");
        }
        $pdo = new PDO('sqlite:'.DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

function num2($n): string { return sprintf('%02d', (int)$n); }

// ---------------------------------------------------------------------------
// Fragments HTML (renvoyés à HTMX)
// ---------------------------------------------------------------------------
function frag_landing(): string {
    $rows = db()->query(
        "SELECT f.*,
           (SELECT c.name FROM cards c WHERE c.family_key=f.key ORDER BY c.num LIMIT 1) AS first_name,
           (SELECT c.id   FROM cards c WHERE c.family_key=f.key ORDER BY c.num LIMIT 1) AS first_id
         FROM families f ORDER BY f.sort_order"
    )->fetchAll();

    $cards_html = '';
    foreach ($rows as $i => $f) {
        $cards_html .= '
    <a class="entry" style="--ac:'.htmlspecialchars($f['accent']).'"
       hx-get="/suite/'.$f['key'].'" hx-target="#main" hx-swap="innerHTML" hx-push-url="/suite/'.$f['key'].'">
      <span class="num">'.num2($i+1).' / 05</span>
      <div class="img-wrap"><img src="/img/'.$f['first_id'].'" alt="'.htmlspecialchars($f['first_name']).'" loading="lazy"></div>
      <div class="meta">
        <div class="meta-info">
          <div class="meta-top">
            <span class="lbl"><span class="esym">'.$f['element_sym'].'</span>'.htmlspecialchars($f['element_line']).'</span>
          </div>
          <div class="fam">'.htmlspecialchars($f['title_full']).'</div>
          <p class="first-card">Première lame · '.htmlspecialchars($f['first_name']).'</p>
          <p class="desc">'.htmlspecialchars($f['desc']).'</p>
        </div>
        <span class="go"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
      </div>
    </a>';
    }

    return <<<HTML
<section id="view-landing">
  <div class="carousel" id="carousel">
    <div class="title-block">
      <span class="overline">Tirage · Étude · Réflexion</span>
      <h1>Tarot<br><em>Divinatoire</em></h1>
      <p class="lede">Soixante-dix-huit lames réparties en quatre suites et vingt-deux arcanes majeurs.
        Version SQLite + HTMX — tout le contenu est servi depuis <code>tarot.sqlite</code>.</p>
      <div class="stats">
        <div><b>78</b>lames</div>
        <div><b>4</b>suites</div>
        <div><b>22</b>majeurs</div>
        <div><b>56</b>mineurs</div>
      </div>
    </div>
    {$cards_html}
  </div>
  <div class="hint" id="hint"><span class="line"></span> Choisir une suite <span class="line"></span></div>
  <div class="rail"><span id="rail-fill"></span></div>
  <script>
    (function(){
      const c = document.getElementById('carousel');
      const fill = document.getElementById('rail-fill');
      const hint = document.getElementById('hint');
      c.addEventListener('scroll', () => {
        const max = c.scrollWidth - c.clientWidth;
        const p = max > 0 ? c.scrollLeft / max : 0;
        fill.style.width = (p * 100) + '%';
        hint.style.opacity = c.scrollLeft > 60 ? '0' : '';
      }, {passive:true});
    })();
  </script>
</section>
HTML;
}

function frag_family(string $key): string {
    $pdo = db();
    $fam = $pdo->prepare("SELECT * FROM families WHERE key=?");
    $fam->execute([$key]);
    $fam = $fam->fetch();
    if (!$fam) return '<p>Famille introuvable</p>';

    $cards = $pdo->prepare("SELECT * FROM cards WHERE family_key=? ORDER BY num");
    $cards->execute([$key]);
    $cards = $cards->fetchAll();

    $families = $pdo->query("SELECT * FROM families ORDER BY sort_order")->fetchAll();
    $idx = array_search($key, array_column($families, 'key'));
    $prev_fam = $families[($idx - 1 + count($families)) % count($families)];
    $next_fam = $families[($idx + 1) % count($families)];

    $minis = '';
    foreach ($cards as $c) {
        $minis .= '<div class="mini" hx-get="/card/'.$c['id'].'" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/'.$c['id'].'" role="button" tabindex="0">
        <div class="ph"><img src="/img/'.$c['id'].'" alt="'.htmlspecialchars($c['name']).'" loading="lazy"></div>
        <div class="cap"><span class="nm">'.htmlspecialchars($c['name']).'</span><span class="no">'.num2($c['num']).'</span></div>
      </div>';
    }

    $crumb = $key === 'majors' ? 'Arcane' : 'Suite';
    $ac = htmlspecialchars($fam['accent']);
    $cards_count = count($cards);

    return <<<HTML
<section id="view-grid" style="--ac:{$ac}">
  <button class="back" hx-get="/landing" hx-target="#main" hx-swap="innerHTML" hx-push-url="/">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>Retour</button>
  <button class="fam-step prev" hx-get="/suite/{$prev_fam['key']}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/suite/{$prev_fam['key']}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
  <button class="fam-step next" hx-get="/suite/{$next_fam['key']}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/suite/{$next_fam['key']}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
  <header class="grid-head">
    <div class="left">
      <div class="crumb"><span class="ac-dot"></span>{$crumb} · {$fam['element']}</div>
      <h2>{$fam['title_full']}</h2>
      <p class="summary">{$fam['desc']}</p>
    </div>
    <div class="right"><b>{$cards_count}</b>lames<br>élément {$fam['element']}</div>
  </header>
  <div class="grid">{$minis}</div>
</section>
HTML;
}

// ---------------------------------------------------------------------------
// Icons SVG (section h2) — repris de V1 decorateDetailedProse
// ---------------------------------------------------------------------------
const SECTION_ICONS = [
    'interpretation' => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>',
    'amour'        => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z"/></svg></span>',
    'travail'      => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg></span>',
    'finances'     => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8.5"/><path d="M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4"/></svg></span>',
    'guidance'     => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8.5"/><path d="m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z"/></svg></span>',
    'affirmation'  => '<span class="content-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 6h18M3 12h18M3 18h12"/></svg></span>',
];

// Décorer le prose MD rendu : supprimer Signification/Description (rendus ailleurs),
// réorganiser Mots-clés en 2 colonnes, injecter les icons SVG dans les h2,
// marquer Interprétation (.primary pleine largeur) et Affirmation (.closing).
function decorate_prose(string $html): string {
    // Splitter sur </section> et ne garder que les sections avec contenu
    $parts = explode('</section>', $html);
    $out = '';
    foreach ($parts as $p) {
        if (strpos($p, '<section') === false) continue;
        if (!preg_match('/<h2>([\s\S]*?)<\/h2>/', $p, $m)) { $out .= $p.'</section>'; continue; }
        $title = trim(html_entity_decode(strip_tags($m[1])));
        $lower = mb_strtolower($title, 'UTF-8');

        if (in_array($title, ['Signification','Description'], true)) continue;

        $type = '';
        if ($title === 'Mots-clés') $type = 'mots-cles';
        elseif ($title === 'Interprétation') $type = 'interpretation';
        elseif (str_contains($lower, 'amour'))      $type = 'amour';
        elseif (str_contains($lower, 'travail'))    $type = 'travail';
        elseif (str_contains($lower, 'finance'))    $type = 'finances';
        elseif (str_contains($lower, 'guidance'))   $type = 'guidance';
        elseif (str_contains($lower, 'affirmation')) $type = 'affirmation';

        $cls = $type;
        if ($type === 'interpretation') $cls .= ' primary';
        if ($type === 'affirmation') $cls .= ' closing';

        // Pour Mots-clés : réorganiser en 2 colonnes kw-cols
        if ($type === 'mots-cles') {
            $h3s = []; $uls = [];
            if (preg_match_all('/<h3>[\s\S]*?<\/h3>/', $p, $h3m)) $h3s = $h3m[0];
            if (preg_match_all('/<ul>[\s\S]*?<\/ul>/', $p, $ulm)) $uls = $ulm[0];
            if (count($h3s) >= 2 && count($uls) >= 2) {
                $out .= '<section id="sec-mots-cles" class="mots-cles"><h2>Mots-clés</h2>'.
                        '<div class="kw-cols">'.
                        '<div class="kw-col">'.$h3s[0].$uls[0].'</div>'.
                        '<div class="kw-col">'.$h3s[1].$uls[1].'</div>'.
                        '</div></section>';
                continue;
            }
        }

        // Injecter l'icône dans le h2
        if (isset(SECTION_ICONS[$type])) {
            $label = $type === 'affirmation' ? 'Citation' : $m[1];
            $new_h2 = '<h2>'.SECTION_ICONS[$type].'<span>'.$label.'</span></h2>';
            $p = preg_replace('/<h2>[\s\S]*?<\/h2>/', $new_h2, $p, 1);
        }

        // Appliquer la classe au <section> (remplacer ou ajouter)
        if ($cls) {
            if (preg_match('/<section([^>]*)>/', $p, $sm)) {
                $attrs = $sm[1];
                if (preg_match('/class="([^"]*)"/', $attrs, $cm)) {
                    $new_class = trim($cm[1].' '.$cls);
                    $new_attrs = preg_replace('/class="[^"]*"/', 'class="'.htmlspecialchars($new_class).'"', $attrs, 1);
                } else {
                    $new_attrs = trim($attrs).' class="'.htmlspecialchars($cls).'"';
                }
                $new_attrs = trim($new_attrs);
                $replacement = $new_attrs !== '' ? '<section '.$new_attrs.'>' : '<section>';
                $p = preg_replace('/<section([^>]*)>/', $replacement, $p, 1);
            }
        }

        $out .= rtrim($p).'</section>';
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Planches symbolique + polarité (diptyque endroit/envers)
// ---------------------------------------------------------------------------
function frag_study_plate(array $card, array $symbols): string {
    if (count($symbols) === 0) return '';

    // Géométrie du canvas (image centrée, 50% du canvas)
    $CARD_L = 25; $CARD_R = 75; $CARD_T = 5; $CARD_B = 85;

    // Séparer gauche/droite, triés par y
    $left = []; $right = [];
    foreach ($symbols as $s) {
        if (($s['side'] ?? 'l') === 'l') $left[] = $s;
        else $right[] = $s;
    }
    usort($left, fn($a,$b) => $a['y'] <=> $b['y']);
    usort($right, fn($a,$b) => $a['y'] <=> $b['y']);

    // Redistribuer verticalement (équirépartition 8% → 92%)
    $slots = fn($arr) => array_map(
        fn($i) => count($arr) === 1 ? 50 : 8 + $i * (84 / (count($arr) - 1)),
        array_keys($arr)
    );
    $left_slots  = $slots($left);
    $right_slots = $slots($right);

    $paths = ''; $circles = ''; $callouts = '';
    $mk_point = function($s, $anchor_y, $side, $i) use (&$paths, &$circles, &$callouts, $CARD_L, $CARD_R, $CARD_T, $CARD_B) {
        $tx = $CARD_L + ($s['x'] / 100) * ($CARD_R - $CARD_L);
        $ty = $CARD_T + ($s['y'] / 100) * ($CARD_B - $CARD_T);
        $ax = $side === 'l' ? 22 : 78;
        $paths   .= sprintf('<path d="M%.1f %.1f L%.1f %.1f"/>', $ax, $anchor_y, $tx, $ty);
        $circles .= sprintf('<circle cx="%.1f" cy="%.1f" r=".7"/>', $tx, $ty);
        $pos_css = $side === 'l' ? "left:0" : "right:0";
        $callouts .= sprintf(
            '<div class="study-callout sym" data-i="%d" data-side="%s" style="top:%.1f%%;%s">'.
            '<span>%s</span><b>%s</b></div>',
            $i+1, $side, $anchor_y, $pos_css,
            htmlspecialchars($s['label'] ?? ''),
            htmlspecialchars($s['short'] ?? '')
        );
    };
    foreach ($left as $i => $s)  $mk_point($s, $left_slots[$i], 'l', $i);
    foreach ($right as $i => $s) $mk_point($s, $right_slots[$i], 'r', $i);

    $n = count($symbols);
    $img = '/img/'.$card['id'];

    return <<<HTML
<article class="study-plate">
  <div class="plate-label">Planche symbolique · {$n} points</div>
  <div class="study-canvas has-symbols">
    <div class="study-card"><img src="{$img}" alt=""></div>
    <svg class="study-arrows" viewBox="0 0 100 100" preserveAspectRatio="none">
      <defs><marker id="ah-{$card['id']}" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="4" markerHeight="4" orient="auto"><path d="M0 0 L10 5 L0 10 Z" fill="currentColor"/></marker></defs>
      <g stroke="currentColor" stroke-width=".3" fill="none" marker-end="url(#ah-{$card['id']})">{$paths}</g>
      <g fill="currentColor">{$circles}</g>
    </svg>
    {$callouts}
  </div>
</article>
HTML;
}

function frag_polarity_plate(array $card, array $es, string $ac): string {
    $kw_up = $es['keywords_up'] ?? '';
    $kw_down = $es['keywords_down'] ?? '';

    // Si on n'a pas les keywords ES, prendre depuis le md (h3 À l'endroit / À l'envers)
    if (!$kw_up || !$kw_down) {
        $md = $card['md'] ?? '';
        if (!$kw_up && preg_match('/###\s*À l\'endroit[\s\S]*?<ul>([\s\S]*?)<\/ul>/i', $md, $m))
            $kw_up = trim(strip_tags($m[1]));
        if (!$kw_down && preg_match('/###\s*À l\'envers[\s\S]*?<ul>([\s\S]*?)<\/ul>/i', $md, $m))
            $kw_down = trim(strip_tags($m[1]));
    }

    $img = '/img/'.$card['id'];
    $alt = htmlspecialchars($card['name']);

    $mk_list = function($kw) {
        if (!$kw) return '<p class="muted">—</p>';
        $items = array_filter(array_map('trim', explode("\n", $kw)));
        $out = '';
        foreach ($items as $it) {
            $it = trim($it, ',;');
            if ($it) $out .= '<li>'.htmlspecialchars($it).'</li>';
        }
        return '<ul>'.$out.'</ul>';
    };

    return <<<HTML
<article class="polarity-plate">
  <div class="plate-label">Lecture comparative</div>
  <div class="polarity-cards">
    <div class="polarity-card">
      <img src="{$img}" alt="{$alt} à l'endroit">
      <h3>À l'endroit</h3>
      {$mk_list($kw_up)}
    </div>
    <div class="polarity-card reverse">
      <img src="{$img}" alt="{$alt} à l'envers">
      <h3>À l'envers</h3>
      {$mk_list($kw_down)}
    </div>
  </div>
</article>
HTML;
}

// ---------------------------------------------------------------------------
// Boutons d'action (Voice + Mirror)
// ---------------------------------------------------------------------------
function frag_card_actions(array $card, bool $has_asso): string {
    $voice = '<button class="tf-voice-btn" type="button" aria-label="Écouter la carte" title="Écouter">'.
             '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M11 5 6 9H3v6h3l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18.5 5.5a9 9 0 0 1 0 13"/></svg>'.
             '<span>Écouter</span></button>';

    $mirror = '';
    if ($has_asso) {
        $mirror = '<button class="tf-mirror-trigger" type="button" hx-get="/mirror/'.$card['id'].'" hx-target="#mirror-stage" hx-swap="innerHTML" onclick="document.getElementById(\'mirror-overlay\').classList.add(\'open\')" aria-label="Voir les associations" title="Associations">'.
                  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/></svg>'.
                  '<span>Associations</span></button>';
    }
    return '<div class="card-actions">'.$voice.$mirror.'</div>';
}

// ---------------------------------------------------------------------------
// Contexte commun chargé pour toute vue d'une carte
// ---------------------------------------------------------------------------
function load_card_context(string $card_id): ?array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM cards WHERE id=?");
    $stmt->execute([$card_id]);
    $card = $stmt->fetch();
    if (!$card) return null;

    $fam_stmt = $pdo->prepare("SELECT * FROM families WHERE key=?");
    $fam_stmt->execute([$card['family_key']]);
    $fam = $fam_stmt->fetch();

    $es_stmt = $pdo->prepare("SELECT * FROM card_es WHERE card_id=?");
    $es_stmt->execute([$card_id]);
    $es = $es_stmt->fetch() ?: ['reponse'=>null,'affirmation'=>null,'keywords_up'=>null,'keywords_down'=>null];

    $all = $pdo->query("SELECT id, name, family_key FROM cards ORDER BY sort_global")->fetchAll();
    $idx = array_search($card_id, array_column($all, 'id'));
    $prev_c = $all[($idx - 1 + count($all)) % count($all)];
    $next_c = $all[($idx + 1) % count($all)];
    $pos = $idx + 1;
    $total = count($all);

    // Cartes de la même famille (pour thumbnails full)
    $fam_cards_stmt = $pdo->prepare("SELECT id, name FROM cards WHERE family_key=? ORDER BY num");
    $fam_cards_stmt->execute([$card['family_key']]);
    $fam_cards = $fam_cards_stmt->fetchAll();
    $in_fam = array_search($card_id, array_column($fam_cards, 'id'));

    $sym_stmt = $pdo->prepare("SELECT * FROM card_symbols WHERE card_id=? ORDER BY idx");
    $sym_stmt->execute([$card_id]);
    $symbols = $sym_stmt->fetchAll();

    $asso_count = (int)$pdo->query("SELECT COUNT(*) FROM card_associations WHERE card_id=".$pdo->quote($card_id))->fetchColumn();

    return [
        'card'=>$card, 'fam'=>$fam, 'es'=>$es,
        'all'=>$all, 'idx'=>$idx, 'prev_c'=>$prev_c, 'next_c'=>$next_c,
        'pos'=>$pos, 'total'=>$total,
        'fam_cards'=>$fam_cards, 'in_fam'=>$in_fam === false ? 0 : $in_fam,
        'symbols'=>$symbols, 'asso_count'=>$asso_count,
        'ac'=>htmlspecialchars($fam['accent']),
        'name'=>htmlspecialchars($card['name']),
        'pos_str'=>num2($pos),
        'num_str'=>num2($card['num']),
    ];
}

// Helper : ES badges HTML
function frag_es_badges(array $es): string {
    if (!$es || empty($es['reponse'])) return '';
    $aff = !empty($es['affirmation']) ? '<span class="es-badge aff">'.htmlspecialchars($es['affirmation']).'</span>' : '';
    return '<div class="es-badges"><span class="es-badge resp"><span class="lbl">Réponse</span> '.htmlspecialchars($es['reponse']).'</span>'.$aff.'</div>';
}

// Helper : navigation prev/next + d-loop (boutons latéraux + barre bas)
function frag_card_nav(array $ctx, string $view): string {
    extract($ctx, EXTR_SKIP);
    $qsv = $view !== 'detail' ? '?view='.$view : '';
    return <<<HTML
<button class="d-step prev" hx-get="/card/{$prev_c['id']}{$qsv}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/{$prev_c['id']}{$qsv}" aria-label="Précédent">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
<button class="d-step next" hx-get="/card/{$next_c['id']}{$qsv}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/{$next_c['id']}{$qsv}" aria-label="Suivant">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
<div class="d-loop">
  <button class="prev" hx-get="/card/{$prev_c['id']}{$qsv}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/{$prev_c['id']}{$qsv}">← {$prev_c['name']}</button>
  <span class="pos"><b>{$pos_str}</b> / {$total}</span>
  <button class="next" hx-get="/card/{$next_c['id']}{$qsv}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/{$next_c['id']}{$qsv}">{$next_c['name']} →</button>
</div>
HTML;
}

// ---------------------------------------------------------------------------
// Vue DÉTAILLÉE — dossier complet avec planche + polarity + prose décoré
// ---------------------------------------------------------------------------
function frag_card_detail(array $ctx): string {
    extract($ctx, EXTR_SKIP);
    $es_badges = frag_es_badges($es);

    // Description courte (1er paragraphe de la section Description)
    $intro = '';
    if (preg_match('/<section id="sec-description">[\s\S]*?<p>([\s\S]*?)<\/p>/', $card['html'], $m)) {
        $intro = '<p class="dossier-intro">'.trim($m[1]).'</p>';
    }

    $html_decorated = decorate_prose($card['html']);
    $study = count($symbols) > 0 ? frag_study_plate($card, $symbols) : '';
    $polarity = frag_polarity_plate($card, $es, $ac);
    $actions = frag_card_actions($card, $asso_count > 0);
    $nav = frag_card_nav($ctx, 'detail');

    return <<<HTML
<section id="view-detail" class="view-detail-mode" style="--ac:{$ac}">
  <button class="back" hx-get="/suite/{$fam['key']}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/suite/{$fam['key']}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>{$fam['short']}</button>
  {$nav}
  <div class="detail-wrap">
    <header class="dossier-head">
      <div class="kicker"><span class="ac-dot"></span>{$fam['title_full']} · {$fam['element']} · {$pos_str}/{$total}</div>
      <h1 class="dossier-title"><em>{$name}</em></h1>
      {$actions}
    </header>
    <section class="dossier-diptych">{$study}{$polarity}</section>
    {$es_badges}
    {$intro}
    <div class="prose detailed-content" id="prose">{$html_decorated}</div>
  </div>
</section>
HTML;
}

// ---------------------------------------------------------------------------
// Vue CLASSIQUE — image sticky + prose réordonné + ES badges + voice/mirror
// ---------------------------------------------------------------------------
function frag_card_classique(array $ctx): string {
    extract($ctx, EXTR_SKIP);
    // Réordonner le prose selon l'ordre Classique (Mots-clés → Interprétation → ... → Signification → Description)
    //decorate_prose supprime Signification/Description ; en Classique on les garde
    $html = decorate_prose_classique($card['html']);
    $es_badges = frag_es_badges($es);
    $actions = frag_card_actions($card, $asso_count > 0);
    $nav = frag_card_nav($ctx, 'classique');
    $img = '/img/'.$card['id'];
    $in_fam_str = num2($in_fam + 1);
    $fam_count = count($fam_cards);

    return <<<HTML
<section id="view-detail" class="view-classique-mode" style="--ac:{$ac}">
  <button class="back" hx-get="/suite/{$fam['key']}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/suite/{$fam['key']}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>{$fam['short']}</button>
  {$nav}
  <div class="detail-wrap view-classique">
    <h1 class="d-card-title"><em>{$name}</em></h1>
    <aside class="d-card">
      <div class="frame"><img src="{$img}" alt="{$name}"></div>
      <div class="tag"><span>{$fam['short']}</span><span class="el">{$fam['element']}</span></div>
    </aside>
    <div class="d-content">
      <div class="kicker"><span>{$pos_str}/{$total}</span><span class="sep">·</span><span>{$fam['title_full']} {$in_fam_str}/{$fam_count}</span></div>
      {$actions}
      {$es_badges}
      <div class="prose view-classique-prose">{$html}</div>
    </div>
  </div>
</section>
HTML;
}

// Variant de decorate_prose qui GARDE Signification et Description (pour Classique et Full)
function decorate_prose_classique(string $html): string {
    // Injecter juste les icônes dans les h2, sans supprimer Signification/Description
    $parts = explode('</section>', $html);
    $out = '';
    foreach ($parts as $p) {
        if (strpos($p, '<section') === false) continue;
        if (!preg_match('/<h2>([\s\S]*?)<\/h2>/', $p, $m)) { $out .= $p.'</section>'; continue; }
        $title = trim(html_entity_decode(strip_tags($m[1])));
        $lower = mb_strtolower($title, 'UTF-8');
        $type = '';
        if ($title === 'Mots-clés') $type = 'mots-cles';
        elseif ($title === 'Interprétation') $type = 'interpretation';
        elseif (str_contains($lower, 'amour'))      $type = 'amour';
        elseif (str_contains($lower, 'travail'))    $type = 'travail';
        elseif (str_contains($lower, 'finance'))    $type = 'finances';
        elseif (str_contains($lower, 'guidance'))   $type = 'guidance';
        elseif (str_contains($lower, 'affirmation')) $type = 'affirmation';

        // Pour Mots-clés : réorganiser en 2 colonnes kw-cols
        if ($type === 'mots-cles') {
            $h3s = []; $uls = [];
            if (preg_match_all('/<h3>[\s\S]*?<\/h3>/', $p, $h3m)) $h3s = $h3m[0];
            if (preg_match_all('/<ul>[\s\S]*?<\/ul>/', $p, $ulm)) $uls = $ulm[0];
            if (count($h3s) >= 2 && count($uls) >= 2) {
                $out .= '<section id="sec-mots-cles" class="mots-cles"><h2>Mots-clés</h2>'.
                        '<div class="kw-cols"><div class="kw-col">'.$h3s[0].$uls[0].'</div>'.
                        '<div class="kw-col">'.$h3s[1].$uls[1].'</div></div></section>';
                continue;
            }
        }

        if (isset(SECTION_ICONS[$type])) {
            $label = $type === 'affirmation' ? 'Citation' : $m[1];
            $new_h2 = '<h2>'.SECTION_ICONS[$type].'<span>'.$label.'</span></h2>';
            $p = preg_replace('/<h2>[\s\S]*?<\/h2>/', $new_h2, $p, 1);
        }
        $out .= rtrim($p).'</section>';
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Vue IMMERSIVE (Full) — hero 78vh + panel qui slide + thumbs famille
// ---------------------------------------------------------------------------
function frag_card_full(array $ctx): string {
    extract($ctx, EXTR_SKIP);
    $html = decorate_prose_classique($card['html']);
    $es_badges = frag_es_badges($es);
    $actions = frag_card_actions($card, $asso_count > 0);
    $nav = frag_card_nav($ctx, 'full');
    $img = '/img/'.$card['id'];

    // Thumbs de la famille
    $thumbs = '';
    foreach ($fam_cards as $c) {
        $is_current = $c['id'] === $card['id'] ? ' current' : '';
        $qsv = '?view=full';
        $thumbs .= '<a class="d-thumb'.$is_current.'" hx-get="/card/'.$c['id'].$qsv.'" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/'.$c['id'].$qsv.'"><img src="/img/'.$c['id'].'" alt="'.htmlspecialchars($c['name']).'"></a>';
    }

    $in_fam_str = num2($in_fam + 1);
    $fam_count = count($fam_cards);

    return <<<HTML
<section id="view-detail" class="view-full-mode" style="--ac:{$ac}">
  <button class="back" hx-get="/landing" hx-target="#main" hx-swap="innerHTML" hx-push-url="/">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>Accueil</button>
  {$nav}
  <div class="d-stage view-full">
    <div class="d-hero"><img class="d-hero-img" src="{$img}" alt="{$name}"></div>
    <div class="d-panel">
      <div class="d-panel-inner">
        <div class="d-meta"><b>{$pos_str}</b>/{$total} · {$fam['title_full']} {$in_fam_str}/{$fam_count} · {$fam['element']}</div>
        <h1 class="d-title"><em>{$name}</em></h1>
        {$actions}
        {$es_badges}
        <div class="prose view-full-prose">{$html}</div>
        <div class="d-thumbs">{$thumbs}</div>
      </div>
    </div>
  </div>
</section>
HTML;
}

// ---------------------------------------------------------------------------
// Vue RAPIDE (Quick) — minimaliste : image + réponse + affirmation + kw-tags
// ---------------------------------------------------------------------------
function frag_card_quick(array $ctx): string {
    extract($ctx, EXTR_SKIP);
    $img = '/img/'.$card['id'];

    // Mots-clés : keywords_up (es) ou extraits du md
    $kw_up = $es['keywords_up'] ?? '';
    if (!$kw_up && preg_match('/###\s*À l\'endroit[\s\S]*?<ul>([\s\S]*?)<\/ul>/i', $card['md'], $m)) {
        $kw_up = trim(strip_tags($m[1]));
    }
    $kw_items = array_filter(array_map('trim', preg_split('/[\n,]/', $kw_up)));
    $kw_tags = '';
    foreach (array_slice($kw_items, 0, 10) as $kw) {
        $kw = trim($kw, ',;');
        if ($kw) $kw_tags .= '<span class="kw-tag">'.htmlspecialchars($kw).'</span>';
    }

    $reponse = !empty($es['reponse']) ? '<div class="reponse-line">Réponse · <span>'.htmlspecialchars($es['reponse']).'</span></div>' : '';
    $affirmation = !empty($es['affirmation']) ? '<div class="affirmation">'.htmlspecialchars($es['affirmation']).'</div>' : '';

    $qsv = '?view=quick';
    $prev_url = "/card/{$prev_c['id']}{$qsv}";
    $next_url = "/card/{$next_c['id']}{$qsv}";

    return <<<HTML
<section id="view-detail" class="view-quick-mode" style="--ac:{$ac}">
  <button class="back" hx-get="/landing?view=quick" hx-target="#main" hx-swap="innerHTML" hx-push-url="/?view=quick">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>Accueil</button>
  <div class="d-stage view-quick">
    <div class="d-body">
      <div class="d-card"><img src="{$img}" alt="{$name}"></div>
      <div class="d-content">
        <h1 class="d-name"><em>{$name}</em></h1>
        {$reponse}
        {$affirmation}
        <div class="kw-list">{$kw_tags}</div>
      </div>
    </div>
  </div>
  <div class="d-pos">
    <button hx-get="{$prev_url}" hx-target="#main" hx-swap="innerHTML" hx-push-url="{$prev_url}">←</button>
    <b>{$pos_str}</b> / {$total}
    <button hx-get="{$next_url}" hx-target="#main" hx-swap="innerHTML" hx-push-url="{$next_url}">→</button>
  </div>
</section>
HTML;
}

// ---------------------------------------------------------------------------
// Dispatcher principal
// ---------------------------------------------------------------------------
function frag_card(string $card_id, string $view = 'detail'): string {
    $ctx = load_card_context($card_id);
    if (!$ctx) return '<p>Carte introuvable</p>';
    return match ($view) {
        'classique' => frag_card_classique($ctx),
        'full'      => frag_card_full($ctx),
        'quick'     => frag_card_quick($ctx),
        default     => frag_card_detail($ctx),
    };
}

// ---------------------------------------------------------------------------
// Mirror (associations) — grille complète par section
// ---------------------------------------------------------------------------
function frag_mirror(string $card_id): string {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, name FROM cards WHERE id=?");
    $stmt->execute([$card_id]);
    $card = $stmt->fetch();
    if (!$card) return '<p>Carte introuvable</p>';

    $rows = $pdo->prepare("SELECT section, pair, descr FROM card_associations WHERE card_id=? ORDER BY rowid");
    $rows->execute([$card_id]);
    $rows = $rows->fetchAll();

    // Grouper par section
    $by_section = [];
    foreach ($rows as $r) {
        $by_section[$r['section']][] = $r;
    }

    $sections_html = '';
    foreach ($by_section as $section => $items) {
        $items_html = '';
        foreach ($items as $it) {
            $items_html .= '<div class="asso-item">'.
                '<div class="asso-pair">'.htmlspecialchars($it['pair']).'</div>'.
                '<div class="asso-text">'.htmlspecialchars($it['descr']).'</div>'.
                '</div>';
        }
        $sec_title = $section ?: 'Divers';
        $sections_html .= '<section class="asso-section">'.
            '<h3>'.htmlspecialchars($sec_title).'</h3>'.
            $items_html.
            '</section>';
    }

    $name = htmlspecialchars($card['name']);
    $total = count($rows);

    return <<<HTML
<div class="mirror-content">
  <header class="mirror-head">
    <div class="kicker">Associations · {$total} combinaisons</div>
    <h2>{$name}</h2>
  </header>
  <div class="mirror-grid">{$sections_html}</div>
</div>
HTML;
}

function frag_search(string $q, string $view = 'detail'): string {
    $q = trim($q);
    if ($q === '') return '';
    $nq = '%'.remove_accents(mb_strtolower($q, 'UTF-8')).'%';

    // Charger toutes les cartes + ES + family une seule fois (78 cartes, cheap)
    $rows = db()->query(
        "SELECT c.id, c.name, c.num, c.family_key, f.accent, f.element, f.short AS fam_short,
                es.keywords_up
         FROM cards c
         JOIN families f ON c.family_key=f.key
         LEFT JOIN card_es es ON es.card_id=c.id
         ORDER BY c.sort_global")->fetchAll();

    // Normaliser une fois par carte + matcher substring
    $matches = [];
    foreach ($rows as $c) {
        $blob = remove_accents(mb_strtolower(
            $c['name'].' '.$c['element'].' '.$c['fam_short'].' '.($c['keywords_up'] ?? '')
        , 'UTF-8'));
        if (strpos($blob, trim($nq, '%')) !== false) {
            $matches[] = $c;
        }
    }
    // Prioriser les matches sur le nom
    usort($matches, function($a, $b) use ($nq) {
        $an = strpos(remove_accents(mb_strtolower($a['name'], 'UTF-8')), trim($nq, '%'));
        $bn = strpos(remove_accents(mb_strtolower($b['name'], 'UTF-8')), trim($nq, '%'));
        if ($an === false && $bn === false) return 0;
        if ($an === false) return 1;
        if ($bn === false) return -1;
        return $an <=> $bn;
    });

    if (!$matches) return '<div class="s-empty">Aucune carte pour «&nbsp;'.htmlspecialchars($q).'&nbsp;»</div>';
    $qsv = $view !== 'detail' ? '?view='.$view : '';
    $out = '';
    foreach ($matches as $c) {
        $kws = [];
        if (!empty($c['keywords_up'])) {
            $parts = array_filter(array_map('trim', explode(',', $c['keywords_up'])));
            $kws = array_slice($parts, 0, 4);
        }
        $kw_html = '';
        foreach ($kws as $k) $kw_html .= '<li>'.htmlspecialchars(trim($k)).'</li>';
        $kw_block = $kw_html ? '<ul class="keyword-list">'.$kw_html.'</ul>' : '';

        $out .= '<div class="mini keyword-mini" style="--ac:'.htmlspecialchars($c['accent']).'"
          hx-get="/card/'.$c['id'].$qsv.'" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/'.$c['id'].$qsv.'"
          onclick="closeSearch()" role="button" tabindex="0">
          <div class="ph"><img src="/img/'.$c['id'].'" alt="'.htmlspecialchars($c['name']).'" loading="lazy"></div>
          <div class="cap"><span class="nm">'.htmlspecialchars($c['name']).'</span><span class="no">'.num2($c['num']).'</span></div>
          '.$kw_block.'
        </div>';
    }
    return $out;
}

// Helper : retirer les accents d'une string (PHP)
function remove_accents(string $s): string {
    if (!class_exists('Transliterator', false)) {
        return str_replace(
            ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ÿ','ç','À','Â','Ä','É','È','Ê','Ë','Î','Ï','Ô','Ö','Ù','Û','Ü','Ÿ','Ç'],
            ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','y','c','A','A','A','E','E','E','E','I','I','O','O','U','U','U','Y','C'],
            $s
        );
    }
    return transliterator_transliterate('Any-Latin; Latin-ASCII;', $s);
}

function frag_draw(): string {
    $row = db()->query(
        "SELECT c.*, f.accent, f.title_full, f.element_sym FROM cards c
         JOIN families f ON c.family_key=f.key ORDER BY RANDOM() LIMIT 1"
    )->fetch();
    $es = db()->prepare("SELECT * FROM card_es WHERE card_id=?");
    $es->execute([$row['id']]);
    $es = $es->fetch();

    $resp = ($es && $es['reponse'])
        ? '<span class="es-badge resp"><span class="lbl">Réponse</span> '.htmlspecialchars($es['reponse']).'</span>' : '';
    $aff = ($es && $es['affirmation'])
        ? '<span class="es-badge aff">'.htmlspecialchars($es['affirmation']).'</span>' : '';
    $num_str = num2($row['num']);

    return <<<HTML
<div class="reveal-card"><img src="/img/{$row['id']}" alt="{$row['name']}"></div>
<div class="reveal-label">{$row['element_sym']} {$row['title_full']} · № {$num_str}</div>
<h2 class="reveal-title">{$row['name']}</h2>
<div class="es-badges" style="justify-content:center">{$resp}{$aff}</div>
<button class="reveal-open" hx-get="/card/{$row['id']}" hx-target="#main" hx-swap="innerHTML" hx-push-url="/card/{$row['id']}"
        onclick="document.getElementById('reveal').classList.remove('open')">Lire la fiche complète</button>
HTML;
}

// ---------------------------------------------------------------------------
// Page HTML complète (servie une seule fois au premier chargement)
// ---------------------------------------------------------------------------
function render_page(string $initial_fragment = ''): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tarot Divinatoire — v3 SQLite + HTMX</title>
<link rel="icon" href="/icon" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
<script src="https://unpkg.com/htmx.org@1.9.12"></script>
<style>
:root{
  --bg:#050505; --bg-2:#0a0907;
  --fg:#f1ede4; --muted:#8a8378;
  --line:rgba(241,237,228,.08);
  --ease:cubic-bezier(.16,1,.3,1);
  --accent:#c9a227; --ac:var(--accent);
  --mat:#ffffff; --mat-line:rgba(255,255,255,.22);
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{min-height:100%}
body{background:var(--bg);color:var(--fg);font-family:'Plus Jakarta Sans',sans-serif;-webkit-font-smoothing:antialiased}
img{display:block;max-width:100%}
button{font-family:inherit;color:inherit;background:none;border:none;cursor:pointer}
a{color:inherit;text-decoration:none}

.fx-grain{position:fixed;inset:0;z-index:9000;pointer-events:none;opacity:.035;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.fx-vignette{position:fixed;inset:0;z-index:8990;pointer-events:none;
  background:radial-gradient(120% 100% at 50% 50%,transparent 55%,rgba(0,0,0,.55) 100%)}

.brand{position:fixed;top:1.8rem;left:2.2rem;z-index:200;display:flex;align-items:center;gap:.65rem;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.32em;text-transform:uppercase;color:var(--muted)}
.brand b{color:var(--fg);font-weight:400}
.brand img{width:1.25rem;height:1.25rem;object-fit:contain}
.brand-version{margin-left:.25rem;padding-left:.65rem;border-left:1px solid var(--line);
  color:var(--accent);font-size:.56rem;letter-spacing:.16em;white-space:nowrap}

.mode-switch{position:fixed;top:1.5rem;right:2.2rem;z-index:250;display:flex;align-items:center;
  font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;
  padding:.25rem;border:1px solid var(--line);border-radius:50px;background:var(--bg)}
.mode-switch button{color:var(--muted);padding:.42rem .62rem;border-radius:50px;transition:color .2s,background .2s}
.mode-switch button:hover{color:var(--fg)}
.mode-switch button.active{color:#050505;background:var(--accent)}

#main{position:relative;z-index:10}

#view-landing{display:flex;align-items:center;height:100vh}
.carousel{display:flex;align-items:center;height:100vh;width:100%;
  overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;scroll-behavior:smooth;
  scrollbar-width:none;-ms-overflow-style:none;padding:0 8vw 0 0}
.carousel::-webkit-scrollbar{display:none}
.title-block{flex:0 0 58vw;height:100vh;display:flex;flex-direction:column;justify-content:center;
  padding:0 4vw 0 8vw;scroll-snap-align:start}
.title-block .overline{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.34em;
  text-transform:uppercase;color:var(--muted);margin-bottom:2.4rem;display:flex;align-items:center;gap:1rem}
.title-block .overline::before{content:'';width:46px;height:1px;background:var(--muted)}
.title-block h1{font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(4rem,11vw,11.5rem);line-height:.86;letter-spacing:-.02em;text-transform:uppercase}
.title-block h1 em{font-style:italic;font-weight:400;color:var(--accent)}
.title-block .lede{margin-top:2.6rem;max-width:30rem;font-size:1.05rem;line-height:1.7;color:var(--muted);font-weight:300}
.title-block .stats{margin-top:3rem;display:flex;gap:2.6rem;font-family:'DM Mono',monospace;
  font-size:.72rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)}
.title-block .stats b{display:block;color:var(--fg);font-family:'Cormorant Garamond',serif;
  font-size:2rem;letter-spacing:0;text-transform:none;font-weight:400;margin-bottom:.2rem}

.hint{position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);z-index:50;
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.3em;text-transform:uppercase;
  color:var(--muted);display:flex;align-items:center;gap:.8rem;transition:opacity .5s}
.hint .line{width:34px;height:1px;background:var(--muted);position:relative;overflow:hidden}
.hint .line::after{content:'';position:absolute;inset:0;background:var(--fg);
  transform:translateX(-100%);animation:slide 2.2s var(--ease) infinite}
@keyframes slide{0%{transform:translateX(-100%)}55%,100%{transform:translateX(100%)}}
.rail{position:fixed;bottom:0;left:0;right:0;height:2px;background:rgba(241,237,228,.05);z-index:60}
.rail span{display:block;height:100%;width:0;background:var(--fg);transition:width .15s linear}

.entry{flex:0 0 auto;height:88vh;aspect-ratio:2/3;min-width:240px;scroll-snap-align:center;
  position:relative;border-radius:1.4rem;overflow:hidden;background:var(--mat);
  border:1px solid var(--mat-line);display:flex;flex-direction:column;
  box-shadow:0 24px 60px rgba(0,0,0,.45);transition:transform .7s var(--ease),border-color .5s,box-shadow .6s}
.entry:hover{transform:translateY(-10px);border-color:rgba(255,255,255,.45);box-shadow:0 34px 80px rgba(0,0,0,.6)}
.entry .img-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:.6rem;min-height:58%}
.entry .img-wrap img{height:100%;width:auto;max-width:100%;object-fit:contain;transition:transform .9s var(--ease)}
.entry:hover .img-wrap img{transform:scale(1.04)}
.entry .meta{padding:1.1rem 1.4rem 1.3rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;
  border-top:1px solid rgba(0,0,0,.06);background:var(--mat);color:#1c1814}
.entry .meta-info{min-width:0;flex:1}
.entry .meta .lbl{font-size:.8rem;color:#6f6a5f;font-style:italic;line-height:1.2}
.entry .meta .lbl .esym{font-style:normal;font-size:1.05rem;margin-right:.4rem;color:var(--ac);vertical-align:-1px}
.entry .meta .fam{font-family:'Cormorant Garamond',serif;font-size:1.7rem;line-height:1;font-weight:500;margin-bottom:.5rem}
.entry .meta .first-card{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.15em;
  text-transform:uppercase;color:var(--ac);margin:0 0 .45rem}
.entry .meta .desc{font-size:.83rem;line-height:1.5;color:#6f6a5f;font-weight:300}
.entry .go{flex:0 0 auto;width:38px;height:38px;border-radius:50%;border:1px solid rgba(0,0,0,.12);
  display:grid;place-items:center;transition:.4s var(--ease);color:#1c1814;background:rgba(0,0,0,.02)}
.entry:hover .go{background:var(--ac);border-color:transparent;color:#fff;transform:rotate(-45deg)}
.entry .go svg{width:14px;height:14px}
.entry .num{position:absolute;top:1rem;right:1.1rem;z-index:2;
  font-family:'DM Mono',monospace;font-size:.64rem;letter-spacing:.2em;color:#a59c8e}

#view-grid{padding:6rem 0 7rem;min-height:100vh}
.grid-head{display:flex;align-items:flex-end;justify-content:space-between;
  padding:3rem 4vw 1.4rem;gap:2rem;flex-wrap:wrap;border-bottom:1px solid var(--line);max-width:1500px;margin:0 auto}
.grid-head .left .crumb{font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.28em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1rem;display:flex;gap:.7rem;align-items:center}
.grid-head .left .crumb .ac-dot{width:8px;height:8px;border-radius:50%;background:var(--ac)}
.grid-head h2{font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(2.6rem,6vw,5rem);line-height:.9;text-transform:uppercase;letter-spacing:-.01em}
.grid-head .summary{max-width:34rem;margin-top:.85rem;color:var(--muted);font-size:.9rem;line-height:1.5;font-weight:300}
.grid-head .right{text-align:right;font-family:'DM Mono',monospace;font-size:.72rem;
  letter-spacing:.2em;text-transform:uppercase;color:var(--muted)}
.grid-head .right b{display:block;font-family:'Cormorant Garamond',serif;font-size:2.4rem;
  color:var(--fg);letter-spacing:0;text-transform:none;font-weight:400}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));
  gap:1.2rem;padding:1.4rem 4vw 7rem;max-width:1500px;margin:0 auto}
.mini{position:relative;border-radius:.9rem;overflow:hidden;background:var(--mat);
  border:1px solid var(--mat-line);cursor:pointer;transition:.5s var(--ease);
  display:flex;flex-direction:column;box-shadow:0 10px 28px rgba(0,0,0,.35)}
.mini:hover{transform:translateY(-6px);border-color:var(--ac);box-shadow:0 16px 40px rgba(0,0,0,.5)}
.mini .ph{aspect-ratio:2/3;display:flex;align-items:center;justify-content:center;padding:.5rem}
.mini .ph img{height:100%;object-fit:contain;transition:transform .6s var(--ease)}
.mini:hover .ph img{transform:scale(1.05)}
.mini .cap{padding:.6rem .75rem .7rem;border-top:1px solid rgba(0,0,0,.06);font-size:.78rem;
  display:flex;justify-content:space-between;align-items:center;gap:.5rem;background:var(--mat);color:#1c1814}
.mini .cap .nm{font-family:'Cormorant Garamond',serif;font-size:1.02rem;line-height:1.1;font-weight:500;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mini .cap .no{font-family:'DM Mono',monospace;font-size:.62rem;color:#a59c8e;letter-spacing:.1em;flex:0 0 auto}

#view-detail{overflow-y:auto}
.detail-wrap{max-width:1500px;margin:0 auto;padding:6rem 4vw 7rem;
  display:grid;grid-template-columns:minmax(300px,440px) 1fr;gap:5rem;align-items:start}
.d-card{position:sticky;top:5.5rem}
.d-card .frame{position:relative;border-radius:1.2rem;overflow:hidden;background:var(--mat);
  border:1px solid var(--mat-line);aspect-ratio:2/3;display:flex;align-items:center;justify-content:center;padding:1.1rem;
  box-shadow:0 30px 70px rgba(0,0,0,.55)}
.d-card .frame img{height:100%;object-fit:contain}
.d-card .tag{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:baseline;
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted)}
.d-card .tag .el{color:var(--ac)}
.d-content{min-width:0}
.d-card-title{margin:0 0 1.5rem;padding:1.15rem 0 .9rem;border-bottom:1px solid var(--line);
  font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(2.4rem,4.5vw,3.8rem);line-height:1;text-align:center;text-transform:uppercase;letter-spacing:-.01em}
.prose{max-width:44rem;margin-top:1.5rem}
.prose section{margin-bottom:2.4rem;scroll-margin-top:7rem}
.prose h2{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:1.7rem;
  margin-bottom:.9rem;display:flex;align-items:center;gap:.8rem}
.prose h2::before{content:'';width:24px;height:1px;background:var(--ac)}
.prose h3{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--muted);margin:1.4rem 0 .7rem}
.prose p{font-size:1rem;line-height:1.85;color:#d8d2c5;font-weight:300;margin-bottom:.9rem}
.prose p.field{margin-bottom:.5rem;display:flex;gap:.6rem;flex-wrap:wrap;font-size:.92rem;color:#bdb5a6}
.prose p.field strong{color:var(--ac);font-family:'DM Mono',monospace;font-size:.72rem;
  letter-spacing:.14em;text-transform:uppercase;font-weight:400;margin:0}
.prose strong{color:var(--fg);font-weight:500}
.prose ul{list-style:none;margin:.6rem 0 1.2rem}
.prose li{position:relative;padding-left:1.4rem;margin-bottom:.5rem;
  font-size:.98rem;line-height:1.7;color:#cfc8ba;font-weight:300}
.prose li::before{content:'';position:absolute;left:0;top:.65rem;width:6px;height:6px;border-radius:50%;background:var(--ac)}
.prose blockquote{margin:1.6rem 0;padding:1.2rem 1.6rem;border-left:2px solid var(--ac);
  background:rgba(241,237,228,.03);border-radius:0 .6rem .6rem 0;
  font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.2rem;line-height:1.6;color:var(--fg)}
.prose blockquote p{color:var(--fg);font-family:inherit;font-style:italic;font-size:inherit;margin:0}
.prose hr{border:none;height:1px;background:var(--line);margin:2.4rem 0}

.es-badges{display:flex;gap:.6rem;flex-wrap:wrap;margin:.5rem 0 1.5rem;align-items:center}
.es-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .65rem;border-radius:999px;
  font-size:.72rem;font-weight:600;letter-spacing:.03em;text-transform:uppercase;line-height:1.2}
.es-badge.resp{background:var(--ac);color:#fff}
.es-badge.aff{background:transparent;border:1.5px solid var(--ac);color:var(--ac);
  text-transform:none;font-weight:500;letter-spacing:0;font-size:.78rem}
.es-badge .lbl{opacity:.7;font-size:.65rem;font-weight:400}

.back{position:fixed;top:4rem;left:2.2rem;z-index:300;display:flex;align-items:center;gap:.6rem;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.24em;text-transform:uppercase;color:var(--muted);
  padding:.6rem 1.1rem;border:1px solid var(--line);border-radius:50px;background:var(--bg);transition:.4s var(--ease)}
.back:hover{color:var(--fg);border-color:var(--ac)}
.back svg{width:13px;height:13px}

.fam-step,.d-step{position:fixed;top:50%;transform:translateY(-50%);z-index:200;width:46px;height:46px;border-radius:50%;
  border:1px solid var(--line);display:grid;place-items:center;color:var(--muted);background:rgba(10,9,7,.5);
  backdrop-filter:blur(8px);transition:.4s var(--ease)}
.fam-step:hover,.d-step:hover{color:var(--fg);border-color:var(--ac);background:rgba(241,237,228,.06)}
.fam-step svg,.d-step svg{width:18px;height:18px}
.fam-step.prev,.d-step.prev{left:1.4rem}
.fam-step.next,.d-step.next{right:1.4rem}

.d-loop{position:fixed;bottom:1.4rem;left:50%;transform:translateX(-50%);z-index:200;
  display:flex;align-items:center;gap:1rem;padding:.55rem 1.2rem;background:rgba(10,9,7,.72);
  backdrop-filter:blur(14px);border:1px solid var(--line);border-radius:50px;
  font-family:'DM Mono',monospace;font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;max-width:calc(100vw - 120px)}
.d-loop button{color:var(--muted);transition:.3s var(--ease);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:30vw}
.d-loop button:hover{color:var(--fg)}
.d-loop .pos{color:var(--fg);flex:0 0 auto;border-left:1px solid var(--line);border-right:1px solid var(--line);padding:0 1rem}
.d-loop .pos b{color:var(--ac);font-weight:400}

.search-launch{position:fixed;top:4rem;right:2.2rem;z-index:300;padding:.6rem 1.1rem;border:1px solid var(--line);
  border-radius:50px;background:var(--bg);font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.18em;
  text-transform:uppercase;color:var(--muted);transition:.4s var(--ease)}
.search-launch:hover{color:var(--fg);border-color:var(--ac)}
#search{position:fixed;inset:0;z-index:8000;background:rgba(5,5,5,.96);backdrop-filter:blur(10px);
  display:none;flex-direction:column}
#search.open{display:flex}
.search-top{display:flex;align-items:center;gap:1rem;padding:2rem 4vw;border-bottom:1px solid var(--line)}
.search-top input{flex:1;background:none;border:none;font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(2rem,6vw,4rem);color:var(--fg);outline:none}
.search-top input::placeholder{color:var(--muted);font-style:italic}
.search-close{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;
  border:1px solid var(--line);color:var(--muted);transition:.3s}
.search-close:hover{color:var(--fg);border-color:var(--accent)}
.search-close svg{width:16px;height:16px}
.s-scroll{flex:1;overflow-y:auto;padding:2rem 4vw 6vh}
.s-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(118px,1fr));gap:1rem;max-width:1500px;margin:0 auto}
.s-empty{grid-column:1/-1;text-align:center;color:var(--muted);padding:3rem 0;
  font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.4rem}

.draws-launch{position:fixed;left:max(1.4rem,env(safe-area-inset-left));bottom:max(1.4rem,env(safe-area-inset-bottom));
  z-index:500;display:inline-flex;align-items:center;gap:.6rem;padding:.85rem 1.2rem;border-radius:50px;
  background:var(--bg-2);border:1px solid var(--line);color:var(--fg);
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.18em;text-transform:uppercase;
  box-shadow:0 12px 28px rgba(0,0,0,.42);transition:transform .2s var(--ease),border-color .2s,background .2s}
.draws-launch:hover{transform:translateY(-2px);border-color:var(--accent);color:var(--accent)}
.draws-launch svg{width:16px;height:16px}
.draws-launch .dot{width:6px;height:6px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent)}

#reveal{display:none}
#reveal.open{display:flex;position:fixed;inset:0;z-index:8600;align-items:center;justify-content:center}
.reveal-backdrop{position:absolute;inset:0;background:rgba(5,5,5,.94);backdrop-filter:blur(14px)}
.reveal-panel{position:relative;z-index:1;padding:2rem;display:flex;align-items:center;justify-content:center;text-align:center}
.reveal-close{position:absolute;top:1.5rem;right:1.5rem;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;
  border:1px solid var(--line);color:var(--fg);background:rgba(5,5,5,.75);z-index:10}
.reveal-close svg{width:18px;height:18px}
.reveal-card{width:min(62vw,300px);aspect-ratio:2/3;background:var(--mat);padding:.5rem;
  box-shadow:0 24px 60px rgba(0,0,0,.5);border-radius:.8rem;overflow:hidden;margin:0 auto 1.5rem}
.reveal-card img{width:100%;height:100%;object-fit:contain}
.reveal-label{font-family:'DM Mono',monospace;font-size:.68rem;letter-spacing:.28em;
  text-transform:uppercase;color:var(--accent);margin-bottom:1rem}
.reveal-title{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:2.5rem;
  text-align:center;text-transform:uppercase;letter-spacing:-.01em;margin-bottom:1.5rem;color:var(--fg)}
.reveal-open{margin:1.5rem auto 0;padding:.7rem 1.4rem;border-radius:50px;background:var(--accent);color:#050505;
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.16em;text-transform:uppercase;border:none;cursor:pointer;transition:.3s;display:block}
.reveal-open:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(201,162,39,.4)}

@media (max-width:900px){
  .title-block{flex:0 0 52vw;padding:0 4vw 0 7vw}
  .title-block h1{font-size:clamp(2.6rem,13vw,4.4rem)}
  .entry{flex:0 0 62vw;height:auto;min-width:0}
  .dossier-diptych{grid-template-columns:1fr!important;gap:3rem!important}
  .detailed-content{grid-template-columns:1fr!important}
  .detailed-content section.primary,.detailed-content section.closing{grid-column:auto!important}
  .grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.8rem;padding:.8rem 6vw 6rem}
  .brand{left:1.2rem;top:1.2rem}
  .back{left:1.2rem;top:3.7rem}
  .search-launch{right:1.2rem;top:3.7rem}
  .d-loop{font-size:.55rem;padding:.45rem .8rem;gap:.6rem}
  .d-loop .pos{padding:0 .5rem}
  .study-callout{position:relative!important;left:auto!important;right:auto!important;top:auto!important;
    width:100%!important;margin:.6rem 0;padding:.6rem .8rem;background:rgba(255,255,255,.04);border-radius:.5rem}
  .study-canvas{min-height:auto!important;padding:1rem!important}
  .study-arrows{display:none!important}
  .polarity-cards{grid-template-columns:1fr!important}
}

/* ============================================================
   VUE DÉTAILLÉE — layout 2 colonnes + dossier diptyque
   ============================================================ */
.detail-wrap{display:block!important;padding:6rem 4vw 8rem;max-width:1400px;margin:0 auto}

.dossier-head{padding-bottom:2rem;margin-bottom:2.5rem;border-bottom:1px solid var(--line)}
.dossier-head .kicker{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem}
.dossier-head .kicker .ac-dot{width:7px;height:7px;border-radius:50%;background:var(--ac);flex:0 0 auto}
.dossier-title{font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(3rem,8vw,6.5rem);line-height:.92;letter-spacing:-.02em;text-transform:uppercase}
.dossier-title em{font-style:italic;color:var(--ac);font-weight:400}

.card-actions{display:flex;gap:.7rem;flex-wrap:wrap;margin-top:1.8rem}
.tf-voice-btn,.tf-mirror-trigger{display:inline-flex;align-items:center;gap:.55rem;
  padding:.65rem 1.1rem;border-radius:50px;border:1px solid var(--line);
  background:rgba(241,237,228,.02);color:var(--fg);
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.16em;text-transform:uppercase;
  transition:.4s var(--ease);cursor:pointer}
.tf-voice-btn:hover,.tf-mirror-trigger:hover{border-color:var(--ac);color:var(--ac);background:rgba(201,162,39,.06)}
.tf-voice-btn svg,.tf-mirror-trigger svg{width:15px;height:15px}
.tf-voice-btn.speaking{border-color:var(--ac);color:var(--ac);animation:tf-voice-pulse 1.4s ease-in-out infinite}
@keyframes tf-voice-pulse{0%,100%{box-shadow:0 0 0 0 rgba(201,162,39,.4)}50%{box-shadow:0 0 0 10px rgba(201,162,39,0)}}

.dossier-diptych{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(300px,.88fr);
  gap:3rem;margin-bottom:4rem;padding-bottom:3rem;border-bottom:1px solid var(--line)}

/* Planches */
.plate-label{font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.24em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem}
.plate-label::before{content:'';width:18px;height:1px;background:var(--ac)}

/* Study plate (planche symbolique) */
.study-plate{min-width:0}
.study-canvas{position:relative;aspect-ratio:3/4;background:rgba(255,255,255,.02);
  border:1px solid var(--line);border-radius:.6rem;padding:2.5rem 4rem;overflow:hidden}
.study-canvas.has-symbols{padding:2.5rem 7.5rem}
.study-card{position:absolute;top:5%;left:25%;width:50%;height:80%;
  display:flex;align-items:center;justify-content:center;background:var(--mat);
  border:1px solid var(--mat-line);border-radius:.4rem;padding:.5rem;box-shadow:0 16px 40px rgba(0,0,0,.4)}
.study-card img{height:100%;width:auto;object-fit:contain}
.study-arrows{position:absolute;inset:0;width:100%;height:100%;color:var(--ac);pointer-events:none;opacity:.85}
.study-callout{position:absolute;width:24%;padding:.45rem .6rem;
  background:rgba(10,9,7,.85);backdrop-filter:blur(4px);border:1px solid var(--line);
  border-radius:.35rem;transition:.3s var(--ease);cursor:default}
.study-callout:hover,.study-callout.active{border-color:var(--ac);background:rgba(201,162,39,.12)}
.study-callout span{display:block;font-family:'DM Mono',monospace;font-size:.55rem;
  letter-spacing:.12em;text-transform:uppercase;color:var(--ac);margin-bottom:.2rem;line-height:1.2}
.study-callout b{display:block;font-family:'Cormorant Garamond',serif;font-style:italic;
  font-size:.82rem;line-height:1.25;color:var(--fg);font-weight:400}

/* Polarity plate (endroit/envers) */
.polarity-plate{min-width:0}
.polarity-cards{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem}
.polarity-card{background:rgba(255,255,255,.02);border:1px solid var(--line);
  border-radius:.6rem;padding:1.2rem .9rem 1.4rem;display:flex;flex-direction:column}
.polarity-card img{width:60%;aspect-ratio:2/3;object-fit:contain;margin:0 auto 1rem;
  background:var(--mat);border-radius:.3rem;padding:.3rem;box-shadow:0 8px 20px rgba(0,0,0,.35)}
.polarity-card.reverse img{transform:rotate(180deg)}
.polarity-card h3{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.2em;
  text-transform:uppercase;color:var(--ac);margin-bottom:.7rem;text-align:center}
.polarity-card ul{list-style:none;display:grid;gap:.32rem}
.polarity-card li{position:relative;padding-left:.95rem;
  font-size:.8rem;line-height:1.4;color:#cfc8ba;font-weight:300}
.polarity-card li::before{content:'◆';position:absolute;left:0;top:0;
  color:var(--ac);font-size:.5rem;line-height:1.6}

/* ES badges (remis en forme pour la vue détaillée) */
.es-badges{display:flex;gap:.7rem;flex-wrap:wrap;align-items:center;margin:0 0 2rem}
.es-badge.resp{padding:.4rem .9rem;border-radius:50px;background:var(--ac);color:#050505;
  font-family:'DM Mono',monospace;font-size:.66rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase}
.es-badge.resp .lbl{opacity:.55;margin-right:.4rem}
.es-badge.aff{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.05rem;
  line-height:1.4;color:var(--fg);padding:.3rem 0 .3rem 1rem;border-left:2px solid var(--ac)}

.dossier-intro{font-family:'Cormorant Garamond',serif;font-weight:300;font-style:italic;
  font-size:clamp(1.3rem,2.4vw,1.8rem);line-height:1.45;color:#e0d9cb;
  max-width:50rem;margin:0 auto 3rem;text-align:center}

/* Prose détaillée : grid 2-col + icons h2 */
.prose.detailed-content{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));
  gap:1.5rem 3rem;max-width:1100px;margin:0 auto}
.prose.detailed-content section{margin:0!important;padding:1.6rem 0;
  border-top:1px solid var(--line);scroll-margin-top:7rem}
.prose.detailed-content section.primary,
.prose.detailed-content section.closing,
.prose.detailed-content section.mots-cles{grid-column:1/-1}
.prose.detailed-content h2{font-family:'Cormorant Garamond',serif;font-weight:400;
  font-size:clamp(1.6rem,2.6vw,2.4rem);line-height:1;margin-bottom:1.2rem;
  display:flex;align-items:center;gap:.7rem;letter-spacing:-.01em}
.prose.detailed-content h2::before{display:none}
.prose.detailed-content h2 .content-icon{display:inline-flex;width:1.1em;height:1.1em;
  color:var(--ac);vertical-align:-.12em}
.prose.detailed-content h2 .content-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:1.5}
.prose.detailed-content h3{font-family:'DM Mono',monospace;font-size:.68rem;
  letter-spacing:.22em;text-transform:uppercase;color:var(--ac);margin:.4rem 0 .8rem}

/* Mots-clés en 2 colonnes */
.prose.detailed-content section.mots-cles h2{margin-bottom:1.6rem}
.kw-cols{display:grid;grid-template-columns:1fr 1fr;gap:2.5rem}
.kw-col h3{font-family:'DM Mono',monospace;font-size:.68rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--ac);margin-bottom:.9rem;padding-bottom:.55rem;
  border-bottom:1px solid var(--line)}
.kw-col ul{list-style:none;display:grid;gap:.42rem}
.kw-col li{position:relative;padding-left:.95rem;color:#d8d2c5;font-size:.92rem;line-height:1.45}
.kw-col li::before{content:'◆';position:absolute;left:0;top:0;color:var(--ac);font-size:.5rem;line-height:1.7}

/* Mirror overlay */
#mirror-overlay{display:none}
#mirror-overlay.open{display:flex;position:fixed;inset:0;z-index:9100;
  align-items:stretch;justify-content:center}
.mirror-backdrop{position:absolute;inset:0;background:rgba(5,5,5,.94);backdrop-filter:blur(12px);
  cursor:pointer}
.mirror-panel{position:relative;z-index:1;width:100%;max-width:1100px;
  padding:3rem 4vw;margin:auto;height:auto;max-height:100vh;overflow-y:auto}
.mirror-close{position:fixed;top:1.5rem;right:1.5rem;width:42px;height:42px;border-radius:50%;
  display:grid;place-items:center;border:1px solid var(--line);color:var(--fg);
  background:rgba(5,5,5,.75);z-index:10;cursor:pointer}
.mirror-close svg{width:18px;height:18px}
.mirror-head{margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--line)}
.mirror-head .kicker{font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--muted);margin-bottom:.6rem}
.mirror-head h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(2rem,5vw,3.5rem);
  text-transform:uppercase;letter-spacing:-.01em}
.mirror-grid{display:grid;gap:2.5rem}
.asso-section h3{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.24em;
  text-transform:uppercase;color:var(--ac);margin-bottom:1rem;padding-bottom:.5rem;
  border-bottom:1px solid var(--line)}
.asso-item{padding:1rem 0;border-bottom:1px dashed var(--line)}
.asso-item:last-child{border-bottom:none}
.asso-pair{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.2rem;
  color:var(--fg);margin-bottom:.5rem;line-height:1.3}
.asso-pair b{color:var(--ac);font-weight:400}
.asso-text{font-size:.92rem;line-height:1.65;color:#cfc8ba;font-weight:300}

/* ============================================================
   VUE CLASSIQUE — image sticky + prose (2 col)
   ============================================================ */
.detail-wrap.view-classique{display:grid;grid-template-columns:minmax(300px,440px) 1fr;
  gap:5rem;max-width:1500px;margin:0 auto;padding:6rem 4vw 8rem;align-items:start}
.view-classique .d-card-title{grid-column:1/-1;font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(2.4rem,4.5vw,3.8rem);line-height:1;text-align:center;text-transform:uppercase;
  letter-spacing:-.01em;margin:0 0 1rem;padding-bottom:1.5rem;border-bottom:1px solid var(--line)}
.view-classique .d-card-title em{font-style:italic;color:var(--ac);font-weight:400}
.view-classique .d-card{position:sticky;top:5.5rem}
.view-classique .d-card .frame{position:relative;border-radius:1.2rem;overflow:hidden;background:var(--mat);
  border:1px solid var(--mat-line);aspect-ratio:2/3;display:flex;align-items:center;justify-content:center;
  padding:1.1rem;box-shadow:0 30px 70px rgba(0,0,0,.55)}
.view-classique .d-card .frame img{height:100%;object-fit:contain}
.view-classique .d-card .tag{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:baseline;
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted)}
.view-classique .d-card .tag .el{color:var(--ac)}
.view-classique .d-content{min-width:0}
.view-classique .d-content .kicker{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.2em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1.5rem;display:flex;gap:.6rem;align-items:center}
.view-classique .d-content .kicker .sep{opacity:.4}
.view-classique-prose{max-width:44rem}
.view-classique-prose section{margin-bottom:2.4rem;scroll-margin-top:7rem}
.view-classique-prose h2{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:1.7rem;
  margin-bottom:.9rem;display:flex;align-items:center;gap:.8rem}
.view-classique-prose h2 .content-icon{display:inline-flex;width:1.1em;height:1.1em;
  color:var(--ac);vertical-align:-.12em}
.view-classique-prose h2 .content-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:1.5}
.view-classique-prose h3{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--muted);margin:1.4rem 0 .7rem}
.view-classique-prose p{font-size:1rem;line-height:1.85;color:#d8d2c5;font-weight:300;margin-bottom:.9rem}
.view-classique-prose p.field{margin-bottom:.5rem;display:flex;gap:.6rem;flex-wrap:wrap;font-size:.92rem;color:#bdb5a6}
.view-classique-prose p.field strong{color:var(--ac);font-family:'DM Mono',monospace;font-size:.72rem;
  letter-spacing:.14em;text-transform:uppercase;font-weight:400;margin:0}
.view-classique-prose strong{color:var(--fg);font-weight:500}
.view-classique-prose ul{list-style:none;margin:.6rem 0 1.2rem}
.view-classique-prose li{position:relative;padding-left:1.4rem;margin-bottom:.5rem;
  font-size:.98rem;line-height:1.7;color:#cfc8ba;font-weight:300}
.view-classique-prose li::before{content:'';position:absolute;left:0;top:.65rem;width:6px;height:6px;
  border-radius:50%;background:var(--ac)}
.view-classique-prose blockquote{margin:1.6rem 0;padding:1.2rem 1.6rem;border-left:2px solid var(--ac);
  background:rgba(241,237,228,.03);border-radius:0 .6rem .6rem 0;
  font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.2rem;line-height:1.6;color:var(--fg)}
.view-classique-prose hr{border:none;height:1px;background:var(--line);margin:2.4rem 0}

/* ============================================================
   VUE FULL (IMMERSIVE) — hero 78vh + panel + thumbs
   ============================================================ */
.d-stage.view-full{position:relative;z-index:10}
.view-full .d-hero{height:78vh;min-height:460px;position:sticky;top:0;overflow:hidden;background:#000}
.view-full .d-hero-img{width:100%;height:100%;object-fit:cover;object-position:center;
  transform:scale(1.06);animation:heroFadeIn .5s var(--ease)}
@keyframes heroFadeIn{from{opacity:0;transform:scale(1.12)}to{opacity:1;transform:scale(1.06)}}
.view-full .d-hero::before{content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent 60%,rgba(10,10,10,.4) 90%,#0a0a0a 100%);pointer-events:none}
.view-full .d-hero::after{content:'';position:absolute;inset:0;
  background:radial-gradient(80% 60% at 50% 30%,transparent,rgba(0,0,0,.5));pointer-events:none}
.view-full .d-panel{position:relative;margin-top:-28px;border-radius:26px 26px 0 0;
  padding:34px 8% 120px;background:#0a0a0a;z-index:2;min-height:50vh}
.view-full .d-panel-inner{max-width:46rem;margin:0 auto}
.view-full .d-meta{font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.2em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1.5rem}
.view-full .d-meta b{color:var(--ac);font-weight:400}
.view-full .d-title{font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(2.8rem,6vw,5.5rem);line-height:.92;letter-spacing:-.02em;text-transform:uppercase;
  margin-bottom:2rem;position:sticky;top:0;padding:1.5rem 0;background:linear-gradient(to bottom,#0a0a0a 70%,transparent)}
.view-full .d-title em{font-style:italic;color:var(--ac);font-weight:400}
.view-full .d-thumbs{display:flex;gap:.5rem;overflow-x:auto;padding:2rem 0 1rem;
  scrollbar-width:thin;scrollbar-color:rgba(241,237,228,.2) transparent}
.view-full .d-thumbs::-webkit-scrollbar{height:4px}
.view-full .d-thumbs::-webkit-scrollbar-thumb{background:rgba(241,237,228,.2);border-radius:2px}
.view-full .d-thumb{flex:0 0 auto;width:60px;aspect-ratio:2/3;display:block;
  border:2px solid transparent;border-radius:.3rem;overflow:hidden;background:var(--mat);padding:1px;
  transition:.4s var(--ease);cursor:pointer}
.view-full .d-thumb img{width:100%;height:100%;object-fit:contain}
.view-full .d-thumb:hover{border-color:rgba(241,237,228,.3);transform:translateY(-2px)}
.view-full .d-thumb.current{border-color:var(--ac);box-shadow:0 0 0 2px var(--ac)}
.view-full-prose{max-width:44rem}
.view-full-prose section{margin-bottom:2.4rem}
.view-full-prose h2{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:1.7rem;
  margin-bottom:.9rem;display:flex;align-items:center;gap:.8rem}
.view-full-prose h2 .content-icon{display:inline-flex;width:1.1em;height:1.1em;color:var(--ac);vertical-align:-.12em}
.view-full-prose h2 .content-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:1.5}
.view-full-prose p{font-size:1rem;line-height:1.85;color:#d8d2c5;font-weight:300;margin-bottom:.9rem}
.view-full-prose strong{color:var(--fg);font-weight:500}
.view-full-prose ul{list-style:none;margin:.6rem 0 1.2rem}
.view-full-prose li{position:relative;padding-left:1.4rem;margin-bottom:.5rem;
  font-size:.98rem;line-height:1.7;color:#cfc8ba;font-weight:300}
.view-full-prose li::before{content:'';position:absolute;left:0;top:.65rem;width:6px;height:6px;
  border-radius:50%;background:var(--ac)}

/* ============================================================
   VUE QUICK (RAPIDE) — carte pleine + affirmation + kw-tags
   ============================================================ */
.view-quick .d-stage{position:relative;z-index:10}
.view-quick .d-body{display:flex;min-height:100vh;align-items:stretch}
.view-quick .d-card{flex:0 0 44%;padding:4rem 2rem;display:flex;align-items:center;justify-content:center;pointer-events:none}
.view-quick .d-card img{max-height:88vh;width:auto;max-width:100%;object-fit:contain;
  background:var(--mat);border-radius:1.2rem;padding:.5rem;
  filter:drop-shadow(0 30px 60px rgba(0,0,0,.5))}
.view-quick .d-content{flex:1;padding:4rem 5rem 4rem 2.5rem;display:flex;flex-direction:column;justify-content:center;min-width:0}
.view-quick .d-name{font-family:'Cormorant Garamond',serif;font-weight:300;
  font-size:clamp(3rem,6vw,5.5rem);line-height:.92;text-transform:uppercase;letter-spacing:-.02em;
  margin-bottom:2rem}
.view-quick .d-name em{font-style:italic;color:var(--ac);font-weight:400}
.view-quick .reponse-line{font-family:'DM Mono',monospace;font-size:.78rem;letter-spacing:.18em;
  text-transform:uppercase;color:var(--muted);margin-bottom:1.5rem;opacity:.85}
.view-quick .reponse-line span{color:var(--ac);font-weight:500;letter-spacing:.22em}
.view-quick .affirmation{font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:300;
  font-size:clamp(2rem,4vw,3.2rem);line-height:1.2;color:var(--fg);margin-bottom:3rem;
  position:relative;padding:0 2.5rem}
.view-quick .affirmation::before{content:'«';position:absolute;left:0;top:-.4rem;color:var(--ac);
  font-size:1.4em;font-style:normal}
.view-quick .affirmation::after{content:'»';position:absolute;right:0;bottom:-.4rem;color:var(--ac);
  font-size:1.4em;font-style:normal}
.view-quick .kw-list{display:flex;flex-wrap:wrap;gap:.55rem}
.view-quick .kw-tag{display:inline-block;padding:.45rem 1rem;border-radius:50px;
  border:1px solid var(--line);font-size:.84rem;color:#d8d2c5;font-weight:300;
  transition:.3s var(--ease);cursor:default}
.view-quick .kw-tag:hover{border-color:var(--ac);color:var(--ac)}
.view-quick .d-pos{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:200;
  display:flex;align-items:center;gap:1.2rem;padding:.7rem 1.4rem;
  background:rgba(10,9,7,.72);backdrop-filter:blur(14px);border:1px solid var(--line);
  border-radius:50px;font-family:'DM Mono',monospace;font-size:.72rem;letter-spacing:.14em;color:var(--muted)}
.view-quick .d-pos b{color:var(--ac);font-weight:400}
.view-quick .d-pos button{width:32px;height:32px;display:grid;place-items:center;
  border-radius:50%;transition:.3s var(--ease);color:var(--fg)}
.view-quick .d-pos button:hover{background:rgba(241,237,228,.06);color:var(--ac)}
</style>
</head>
<body>
<div class="fx-grain"></div>
<div class="fx-vignette"></div>

<a class="brand" href="/" hx-get="/landing" hx-target="#main" hx-swap="innerHTML" hx-push-url="/">
  <img src="/icon" alt=""><span><b>Tarot</b> · Divinatoire</span>
  <span class="brand-version">v3 · sqlite+htmx</span>
</a>

<nav class="mode-switch" aria-label="Choisir une vue" id="mode-switch">
  <button data-view="classique" onclick="switchView('classique')">Classique</button>
  <button data-view="full" onclick="switchView('full')">Immersive</button>
  <button data-view="detail" class="active" onclick="switchView('detail')">Détaillée</button>
  <button data-view="quick" onclick="switchView('quick')">Rapide</button>
</nav>

<main id="main">$initial_fragment</main>

<button class="search-launch" onclick="openSearch()">Recherche</button>
<div id="search" role="dialog" aria-modal="true" aria-label="Recherche">
  <div class="search-top">
    <input type="text" id="search-input" placeholder="Nom d'une carte…" autocomplete="off"
           oninput="triggerSearch(this.value)" onkeydown="searchKeydown(event)">
    <button class="search-close" onclick="closeSearch()" aria-label="Fermer">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
  </div>
  <div class="s-scroll"><div class="s-grid" id="s-grid"></div></div>
</div>

<button class="draws-launch"
        hx-get="/draw" hx-target="#reveal-stage" hx-swap="innerHTML"
        onclick="document.getElementById('reveal').classList.add('open')"
        aria-label="Carte du jour">
  <span class="dot"></span>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="9" width="18" height="6" rx="1"/><path d="M3 12h18"/></svg>
  <span>Tirage</span>
</button>

<div id="reveal" role="dialog" aria-modal="true" aria-label="Révélation">
  <div class="reveal-backdrop" onclick="document.getElementById('reveal').classList.remove('open')"></div>
  <div class="reveal-panel">
    <button class="reveal-close" onclick="document.getElementById('reveal').classList.remove('open')" aria-label="Fermer">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
    <div id="reveal-stage"></div>
  </div>
</div>

<div id="mirror-overlay" role="dialog" aria-modal="true" aria-label="Associations">
  <div class="mirror-backdrop" onclick="document.getElementById('mirror-overlay').classList.remove('open')"></div>
  <div class="mirror-panel">
    <button class="mirror-close" onclick="document.getElementById('mirror-overlay').classList.remove('open')" aria-label="Fermer">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
    <div id="mirror-stage"></div>
  </div>
</div>

<script>
/* ============================================================
   Vue courante (depuis ?view= dans l'URL, default 'detail')
   ============================================================ */
function currentView(){
  const p = new URLSearchParams(location.search);
  const v = p.get('view');
  return ['classique','full','detail','quick'].includes(v) ? v : 'detail';
}

function updateModeSwitch(){
  const v = currentView();
  document.querySelectorAll('#mode-switch button').forEach(b => {
    b.classList.toggle('active', b.dataset.view === v);
  });
}

/* Switch de vue en préservant la carte courante (V1-like) */
function switchView(mode){
  if (!['classique','full','detail','quick'].includes(mode)) return;
  const path = location.pathname;
  const params = new URLSearchParams(location.search);
  params.set('view', mode);
  const qs = '?' + params.toString();
  // Si on est sur une carte, recharge cette carte dans la nouvelle vue
  const cardMatch = path.match(/\/card\/(.+)$/);
  if (cardMatch){
    htmx.ajax('GET', '/card/'+cardMatch[1]+qs, {target:'#main', swap:'innerHTML'});
    history.replaceState({}, '', path+qs);
  } else {
    // Sinon, recharge la page avec la nouvelle vue (préserve /suite/x ou /)
    location.search = qs;
  }
  updateModeSwitch();
}

/* ============================================================
   Recherche V1-like : s'ouvre sur lettre simple (sans modifier)
   ============================================================ */
let searchTimer;
function triggerSearch(q){
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    const grid = document.getElementById('s-grid');
    if (!q.trim()){ grid.innerHTML = ''; return; }
    const v = currentView();
    const qs = '&view=' + v;
    htmx.ajax('GET', '/search?q='+encodeURIComponent(q)+qs, {target:'#s-grid', swap:'innerHTML'});
  }, 120);
}
function openSearch(initialKey){
  const s = document.getElementById('search');
  const input = document.getElementById('search-input');
  s.classList.add('open');
  if (initialKey && !input.value) input.value = initialKey;
  setTimeout(() => { input.focus(); if (initialKey) triggerSearch(input.value); }, 30);
}
function closeSearch(){
  document.getElementById('search').classList.remove('open');
  document.getElementById('search-input').value = '';
  document.getElementById('s-grid').innerHTML = '';
}
function searchKeydown(e){
  if (e.key === 'Escape') { closeSearch(); }
  else if (e.key === 'Backspace' && !e.target.value) { closeSearch(); }
  else if (e.key === 'Enter'){
    const first = document.querySelector('#s-grid .mini');
    if (first) first.click();
  }
}

/* ============================================================
   Voice TTS (Web Speech API)
   ============================================================ */
let currentUtterance = null;
function stopVoice(){
  if (window.speechSynthesis) { window.speechSynthesis.cancel(); currentUtterance = null; }
  document.querySelectorAll('.tf-voice-btn.speaking').forEach(b => b.classList.remove('speaking'));
}
function speakCard(btn){
  if (!('speechSynthesis' in window)) return;
  if (btn.classList.contains('speaking')) { stopVoice(); return; }
  stopVoice();
  const wrap = btn.closest('#view-detail') || document;
  const title = wrap.querySelector('.dossier-title, .d-card-title, .d-title, .d-name')?.textContent.trim() || '';
  const intro = wrap.querySelector('.dossier-intro')?.textContent.trim() || '';
  const prose = Array.from(wrap.querySelectorAll('#prose section, .prose section')).map(s => {
    const h = s.querySelector('h2')?.textContent.trim();
    const ps = Array.from(s.querySelectorAll('p,li')).map(p => p.textContent.trim()).filter(Boolean).join('. ');
    return h ? (h + '. ' + ps) : ps;
  }).filter(Boolean).join('. ');
  const text = [title, intro, prose].filter(Boolean).join('. ');
  if (!text) return;
  const u = new SpeechSynthesisUtterance(text);
  u.lang = 'fr-FR'; u.rate = 0.92; u.pitch = 1;
  const frVoice = window.speechSynthesis.getVoices().find(v => /fr[-_]?FR/i.test(v.lang));
  if (frVoice) u.voice = frVoice;
  u.onend = () => btn.classList.remove('speaking');
  u.onerror = () => btn.classList.remove('speaking');
  btn.classList.add('speaking');
  currentUtterance = u;
  window.speechSynthesis.speak(u);
}
if ('speechSynthesis' in window) {
  window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
}

/* ============================================================
   Wire events après swaps HTMX
   ============================================================ */
document.body.addEventListener('htmx:afterSettle', () => {
  document.querySelectorAll('.tf-voice-btn:not([data-wired])').forEach(b => {
    b.dataset.wired = '1';
    b.addEventListener('click', () => speakCard(b));
  });
  document.querySelectorAll('.study-callout.sym').forEach(c => {
    if (c.dataset.wired) return;
    c.dataset.wired = '1';
    c.addEventListener('mouseenter', () => c.classList.add('active'));
    c.addEventListener('mouseleave', () => c.classList.remove('active'));
  });
  updateModeSwitch();
  window.scrollTo({top:0, behavior:'instant'});
});

/* ============================================================
   Clavier global : lettre ouvre search, ← → navigation, Esc ferme
   ============================================================ */
document.addEventListener('keydown', e => {
  // Si on est dans un input/textarea, on laisse faire
  const tag = (document.activeElement?.tagName || '').toLowerCase();
  if (tag === 'input' || tag === 'textarea') return;
  if (e.metaKey || e.ctrlKey || e.altKey) return;

  // Esc ferme tout
  if (e.key === 'Escape') {
    document.getElementById('mirror-overlay')?.classList.remove('open');
    document.getElementById('reveal')?.classList.remove('open');
    if (document.getElementById('search')?.classList.contains('open')) closeSearch();
    return;
  }

  // Si search ouverte, ne pas intercepter (sauf ce que gère searchKeydown)
  const searchOpen = document.getElementById('search')?.classList.contains('open');
  const overlayOpen = document.querySelector('#mirror-overlay.open, #reveal.open');
  if (overlayOpen) return;

  // Lettre seule ouvre search (V1-like)
  if (!searchOpen && e.key.length === 1 && /\p{L}|\p{N}/u.test(e.key)) {
    openSearch(e.key);
    e.preventDefault();
    return;
  }

  // ← → navigation cartes
  if (e.key === 'ArrowLeft') {
    document.querySelector('#view-detail .d-step.prev, #view-detail .d-loop .prev, #view-detail .d-pos button:first-child')?.click();
  } else if (e.key === 'ArrowRight') {
    document.querySelector('#view-detail .d-step.next, #view-detail .d-loop .next, #view-detail .d-pos button:last-child')?.click();
  }
});

/* ============================================================
   Init : charger la route courante
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  updateModeSwitch();
  // La page entière est déjà servie par PHP avec le fragment initial si URL directe.
  // Si on arrive en "GET /" simple, le fragment est déjà en place aussi.
  // Rien à faire de plus, le HTML est pré-rendu côté serveur.
});
</script>
</body>
</html>
HTML;
}

// ---------------------------------------------------------------------------
// Routeur principal
// ---------------------------------------------------------------------------
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = urldecode($path);
$query = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
$view = $query['view'] ?? 'detail';
$is_htmx = isset($_SERVER['HTTP_HX_REQUEST']);

// Icône (servie telle quelle, hors page)
if ($path === '/icon') {
    $icon = __DIR__.'/../icon.png';
    if (file_exists($icon)) {
        header('Content-Type: image/png');
        readfile($icon);
    }
    exit;
}

// Image BLOB d'une carte
if (preg_match('#^/img/(.+)$#', $path, $m)) {
    $stmt = db()->prepare("SELECT img FROM cards WHERE id=?");
    $stmt->execute([$m[1]]);
    $row = $stmt->fetch();
    if ($row && $row['img']) {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        echo $row['img'];
    } else {
        http_response_code(404);
    }
    exit;
}

// ---------------------------------------------------------------------------
// Résolution du fragment pour la route courante
// ---------------------------------------------------------------------------
function resolve_fragment(string $path, string $view): string {
    if ($path === '/' || $path === '/index.php' || $path === '/index.html') return frag_landing();
    if ($path === '/landing') return frag_landing();
    if (preg_match('#^/suite/(.+)$#', $path, $m)) return frag_family($m[1]);
    if (preg_match('#^/card/(.+)$#', $path, $m)) return frag_card($m[1], $view);
    if ($path === '/search') return frag_search($query['q'] ?? '', $view);
    if ($path === '/draw') return frag_draw();
    return '<p>Not found</p>';
}

// Routes qui retournent un fragment ET qui peuvent être accédées directement
// (landing/suite/card) : si pas HTMX, on emballe dans la page complète
$wrap_in_page = !$is_htmx && (
    $path === '/' || $path === '/index.php' || $path === '/index.html'
    || preg_match('#^/(landing|suite|card)/#', $path)
    || $path === '/landing'
);

// Routes qui sont TOUJOURS des fragments (mirror, search, draw)
header('Content-Type: text/html; charset=utf-8');

if ($wrap_in_page) {
    // Pré-rendre le fragment initial dans #main
    $initial = resolve_fragment($path, $view);
    echo render_page($initial);
    exit;
}

// Fragments purs (HTMX ou overlays)
if ($path === '/landing') { echo frag_landing(); exit; }
if (preg_match('#^/suite/(.+)$#', $path, $m)) { echo frag_family($m[1]); exit; }
if (preg_match('#^/card/(.+)$#', $path, $m)) { echo frag_card($m[1], $view); exit; }
if (preg_match('#^/mirror/(.+)$#', $path, $m)) { echo frag_mirror($m[1]); exit; }
if ($path === '/search') { echo frag_search($query['q'] ?? '', $view); exit; }
if ($path === '/draw') { echo frag_draw(); exit; }

http_response_code(404);
echo 'Not found';
