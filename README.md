![Project icon](website/assets/img/icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

Un site immersif pour explorer les 78 lames du Tarot de Rider-Waite-Smith : grille continue, cartes intercalaires par famille, fiche détaillée pour chaque carte.

## ✅ Fonctionnalités

- **78 cartes** : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers)
- **V3 PHP** : architecture PHP+SQLite, navigation par flèches, recherche instantanée
- **Grille plate** : les 78 cartes dans une seule grille continue avec cartes intercalaires SVG par famille
- **Cartes intercalaires** : 5 tuiles verticales (SVG originaux) présentant chaque famille, cliquables vers la première lame
- **4 vues** : Classique, Immersive (diaporama), Détaillée (éditoriale), Rapide (lecture express)
- **Lecture Rapide** : carte plein écran + Réponse (Oui / Non / Peut-être) + Affirmation + mots-clés positifs (FR + ES)
- **Badges bilingues** : RÉPONSE et Affirmation affichées sur les 4 vues, en français
- **Fiches enrichies** : signification, mots-clés (endroit / envers), interprétation, amour, travail, finances, guidance
- **Scanner mobile** : reconnaissance locale d'une lame Rider-Waite-Smith par caméra, puis ouverture directe de sa fiche
- **Recherche plein écran** : filtrage instantané par nom de carte (touches A-Z ou F)
- **Navigation clavier** : `↑↓←→` dans la grille, `Enter` pour ouvrir, `Escape` pour revenir, flèches dans la fiche
- **Cohérence visuelle** : couleur d'accent dédiée par famille, matelas blanc simulant la bordure des cartes réelles
- **Accessible** : navigation clavier (Échap, flèches) et tactile

## 🧠 Utilisation

1. La grille affiche les 78 cartes avec 5 cartes intercalaires (une par famille).
2. Cliquez une carte intercalaire pour ouvrir la première lame de la famille.
3. Cliquez une carte pour afficher sa fiche détaillée.
4. Naviguez avec les flèches `↑↓←→` dans la grille, `←`/`→` dans la fiche.
5. Recherche : tapez une lettre (A-Z) ou `F` pour ouvrir la recherche plein écran.

## ⚙️ Réglages

Les couleurs d'accent par famille et la palette globale sont définies via des variables CSS dans `website/index.html` (bloc `:root`). Le matelas des cartes utilise `--mat` (`#ffffff`).

## 🧾 Commandes

| Touche | Action |
|--------|--------|
| `↑↓←→` | Sélectionner une carte dans la grille |
| `Enter` | Ouvrir la carte sélectionnée |
| `A-Z` | Ouvre la recherche instantanée (tapez pour filtrer) |
| `←` / `→` | Carte précédente / suivante (vue fiche) |
| `Échap` | Retour à la grille depuis une fiche |
| `F` | Ouvre la recherche plein écran |
| Molette / glisser | Défilement horizontal du carousel d'accueil |

## 📦 Build & Package

Le site V3 est **PHP+SQLite** : `website/v3/index.php` + `website/v3/tarot.sqlite`.

Le site V1/V2 est **autoporté** : les 78 images sont embarquées en WebP (data URI) dans `website/assets/js/data.js`.

```bash
node scripts/build_data.js
```

Ce script lit `website/assets/md/*.md` et `website/assets/img/cards/*.jpg`, régénère `website/assets/js/data.js` (≈ 7 Mo, images WebP 420px incluses) et affiche un log de confirmation. Nécessite [ImageMagick](https://imagemagick.org/) (`magick`). À exécuter après toute modification d'un `.md` ou d'une image.

## 🧪 Test local

Un seul serveur PHP sert les trois versions (V2 statique, V3 PHP+SQLite, V4
statique SQLite/WASM). Le port est choisi automatiquement :

```bash
./start-local.sh
```

Le script affiche les URLs disponibles (`/website/v2/`, `/website/v3/`,
`/website/v4/`) sur le premier port libre à partir de 8765. Nécessite uniquement
PHP en CLI. Ne pas ouvrir `website/v4/index.html` en `file://` : le navigateur
bloque alors `fetch()` vers SQLite et le chargement de WebAssembly.

## 📋 Voir le [CHANGELOG](CHANGELOG.md) pour l'historique complet.

Version courante : `v2026.08.01`

## 🔗 Liens

- **Live V3** : [mondary.design/pk/tarot3](https://mondary.design/pk/tarot3/)
- **Live V1/V2** : [mondary.design/pk/tarot](https://mondary.design/pk/tarot/)
- **Source du contenu des fiches** : [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations** : Tarot Rider-Waite-Smith — domaine public
- **Typographies** : [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

Le code et le design de ce projet sont sous licence MIT (voir `LICENSE`).
Les textes descriptifs des cartes sont adaptés depuis [Vivre Intuitif](https://vivre-intuitif.com) et restent la propriété de leurs auteurs. Les illustrations du Tarot de Rider-Waite-Smith sont dans le domaine public.
