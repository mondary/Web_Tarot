# v12 — Rust/WASM

Portage de la v9 en **Rust compilé en WASM**. Le site devient un bundle 100 % statique :
même code pour le web (o2switch, PHP facultatif) et pour une app offline (Capacitor/Tauri).

## Architecture

```
crates/exporter/   CLI Rust : vault.sqlite → www/ (données, images, polices, associations)
crates/app/        L'app en Rust → WASM (wasm-bindgen + web-sys)
static/index.html  Coquille HTML (CSS repris tel quel de la v9)
build.sh           Tout construire → www/
```

## Build

```sh
./build.sh            # produit www/ (~34 Mo)
python3 -m http.server 8899 --directory www   # test local
```

Déploiement o2switch : uploader le contenu de `www/` (fichiers statiques, aucun PHP requis).
App offline : `www/` est exactement ce qu'on embarque dans une coquille Capacitor ou Tauri.

## Source des données

`../v9/vault.sqlite` (unique source). L'exporteur déverse :
`data/app-data.json`, `img/`, `decks/`, `fonts/`, `svg/`, `assocs/<id>.json`.

## Présent en v12

Grille 78 lames, fiche détail (portrait, domaines, mots-clés, réponse, citation,
associations, navigation famille), recherche + filtres, nuances, mode apprentissage
(Leitner persisté), thèmes, decks RWS/CLM/Marseille, mots-clés au survol, diaporama,
swipe mobile, clavier, état dans l'URL (?carte=&deck=&theme=&kw=).

## Écarté en v12 (à porter si besoin)

Tirages (`tarot-spreads.js`), balises OG dynamiques (partage serveur), service worker
(inutile : tout est déjà local), loupe du hero.
