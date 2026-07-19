![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

Un site statique immersif pour explorer les 78 lames du Tarot de Rider-Waite-Smith : carousel d'accueil par famille, grille de consultation et fiche détaillée pour chaque carte.

## ✅ Fonctionnalités

- **78 cartes** : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers)
- **Interface unifiée** en un seul fichier : vue d'accueil, grille par famille, vue détail
- **Fiches enrichies** : signification, mots-clés (endroit / envers), interprétation, amour, travail, finances, guidance
- **Scanner mobile** : reconnaissance locale d'une lame Rider-Waite-Smith par caméra, puis ouverture directe de sa fiche
- **Navigation continue** : les flèches traversent les 78 cartes en boucle, d'une famille à l'autre
- **Cohérence visuelle** : couleur d'accent dédiée par famille, matelas blanc simulant la bordure des cartes réelles
- **Hors-ligne** : contenu embarqué dans `website/data.js`, fonctionne en double-cliquant sur `website/index.html`
- **Accessible** : navigation clavier (Échap, flèches) et tactile

## 🧠 Utilisation

1. Ouvrez l'accueil : un carousel présente les 5 familles.
2. Cliquez sur une carte d'entrée pour ouvrir la grille d'une famille (ex. Le Fou → Arcanes Majeurs).
3. Cliquez sur une carte pour afficher sa fiche détaillée.
4. Dans la fiche, naviguez avec les flèches ← / → ou la barre de boucle en bas : le parcours enchaîne toutes les familles sans rupture.

## ⚙️ Régages

Les couleurs d'accent par famille et la palette globale sont définies via des variables CSS dans `website/index.html` (bloc `:root`). Le matelas des cartes utilise `--mat` (`#ffffff`).

## 🧾 Commandes

| Touche | Action |
|--------|--------|
| `A-Z` | Ouvre la recherche instantanée (tapez pour filtrer) |
| `←` / `→` | Carte précédente / suivante (vue détail) ou famille précédente/suivante (grille) |
| `Échap` | Retour à la vue précédente / ferme la recherche |
| `Entrée` | Ouvre la première carte des résultats de recherche |
| `⌫` | Efface un caractère de la recherche |
| Molette / glisser | Défilement horizontal du carousel d'accueil |

## 📦 Build & Package

Le site est **autoporté** : les 78 images sont embarquées en WebP (data URI) dans `website/data.js`. Pour déployer, il suffit de déposer `website/index.html` et `website/data.js` sur le serveur — aucun dossier d'images à uploader.

Les sources (`website/cards/*.md` et `*.jpg`) produisent `data.js` via un script de génération.

```bash
node website/build_data.js
```

Ce script lit `website/cards/*.md` et `*.jpg`, régénère `website/data.js` (≈ 7 Mo, images WebP 420px incluses) et affiche un log de confirmation. Nécessite [ImageMagick](https://imagemagick.org/) (`magick`). À exécuter après toute modification d'un `.md` ou d'une image.

Aucune étape de bundling : le site est livré en HTML/CSS/JS vanilla.

## 🧪 Installation

```bash
git clone <repo-url>
cd Web_Tarot
# data.js est déjà commité (autoporté) — prêt à déployer tel quel.
# Pour le régénérer après une modif :
node website/build_data.js
# puis déposer website/index.html + website/data.js sur le FTP
open website/index.html
```

Pour un serveur local :

```bash
python3 -m http.server 8000
# puis ouvrir http://localhost:8000/website/
```

## 📋 Voir le [CHANGELOG](CHANGELOG.md) pour l'historique complet.

Version courante : `🔥v2.2026.5`

## 🔗 Liens

- **Source du contenu des fiches** : [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations** : Tarot Rider-Waite-Smith — domaine public
- **Typographies** : [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

Le code et le design de ce projet sont sous licence MIT (voir `LICENSE`).
Les textes descriptifs des cartes sont adaptés depuis [Vivre Intuitif](https://vivre-intuitif.com) et restent la propriété de leurs auteurs. Les illustrations du Tarot de Rider-Waite-Smith sont dans le domaine public.
