![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

Un site statique immersif pour explorer les 78 lames du Tarot de Rider-Waite-Smith : carousel d'accueil par famille, grille de consultation et fiche détaillée pour chaque carte.

## ✅ Fonctionnalités

- **78 cartes** : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers)
- **Interface unifiée** en un seul fichier : vue d'accueil, grille par famille, vue détail
- **Fiches enrichies** : signification, mots-clés (endroit / envers), interprétation, amour, travail, finances, guidance
- **Navigation continue** : les flèches traversent les 78 cartes en boucle, d'une famille à l'autre
- **Cohérence visuelle** : couleur d'accent dédiée par famille, matelas blanc simulant la bordure des cartes réelles
- **Hors-ligne** : contenu embarqué dans `data.js`, fonctionne en double-cliquant sur `index.html`
- **Accessible** : navigation clavier (Échap, flèches) et tactile

## 🧠 Utilisation

1. Ouvrez l'accueil : un carousel présente les 5 familles.
2. Cliquez sur une carte d'entrée pour ouvrir la grille d'une famille (ex. Le Fou → Arcanes Majeurs).
3. Cliquez sur une carte pour afficher sa fiche détaillée.
4. Dans la fiche, naviguez avec les flèches ← / → ou la barre de boucle en bas : le parcours enchaîne toutes les familles sans rupture.

## ⚙️ Régages

Les couleurs d'accent par famille et la palette globale sont définies via des variables CSS dans `index.html` (bloc `:root`). Le matelas des cartes utilise `--mat` (`#ffffff`).

## 🧾 Commandes

| Touche | Action |
|--------|--------|
| `←` / `→` | Carte précédente / suivante (vue détail) |
| `Échap` | Retour à la vue précédente |
| Molette / glisser | Défilement horizontal du carousel d'accueil |

## 📦 Build & Package

Le contenu des fiches (`.md`) est embarqué dans `data.js` via un script de génération.

```bash
node build_data.js
```

Ce script lit `website/cards/*.md`, génère `data.js` (≈ 515 KB) et le log de confirmation. À exécuter après toute modification d'un fichier `.md`.

Aucune étape de bundling : le site est livré en HTML/CSS/JS vanilla.

## 🧪 Installation

```bash
git clone <repo-url>
cd Web_Tarot
node build_data.js      # régénère data.js (optionnel, déjà commité)
open index.html         # ou servir via un serveur local
```

Pour un serveur local :

```bash
python3 -m http.server 8000
# puis ouvrir http://localhost:8000
```

## 📋 Voir le [CHANGELOG](CHANGELOG.md) pour l'historique complet.

Version courante : `🔥v1.2026.1`

## 🔗 Liens

- **Source du contenu des fiches** : [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations** : Tarot Rider-Waite-Smith — domaine public
- **Typographies** : [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

Le code et le design de ce projet sont sous licence MIT (voir `LICENSE`).
Les textes descriptifs des cartes sont adaptés depuis [Vivre Intuitif](https://vivre-intuitif.com) et restent la propriété de leurs auteurs. Les illustrations du Tarot de Rider-Waite-Smith sont dans le domaine public.
