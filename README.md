![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

Un site éditorial pour explorer les 78 lames du Tarot de Rider-Waite-Smith, leurs significations et leurs associations.

![Réglages et soutien](store/screenshots/01-reglages.png)

![Couper le paquet](store/screenshots/03-tirage-coupe.png)

![Éventail des 78 cartes](store/screenshots/04-tirage-eventail.png)

![Éventail sur mobile](store/screenshots/05-tirage-eventail-mobile.png)

## ✅ Fonctionnalités

- **78 cartes** : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers)
- **Accueil complet** : les 78 lames sont visibles directement, regroupées par famille avec cinq intercalaires
- **Fiches éditoriales** : identité, carte du jour, Amour, Travail, Finances, Guidance, signification et description
- **Associations** : combinaisons entre lames accessibles dans un panneau repliable
- **Tirages interactifs** : plusieurs dispositions de tirage intégrées
- **Deux modes de tirage** : *Rapide* (les cartes sont distribuées au hasard) ou *Piocher* — un réglage et une bascule dans le menu Tirages. En mode Piocher, on coupe soi-même le paquet mélangé à la hauteur choisie (les deux moitiés s'inversent), puis on choisit chaque carte dans l'éventail des 78 lames faces cachées — arc léger de droite à gauche sur ordinateur, roulette défilante de gauche à droite sur mobile. La Carte du jour se pioche aussi à la main et reste mémorisée pour la journée
- **Mode apprentissage** : quiz QCM type flashcards (Leitner simplifié) au choix sur le mot-clé ou la phrase centrale de chaque lame — les lames sues partent au fond de la pile, les ratées reviennent vite ; progression conservée localement
- **Recherche plein écran** : filtrage instantané par nom ou numéro, avec filtres par famille
- **Nuances** : pense-bête comparant les lames aux thèmes proches
- **Partage par URL** : chaque lame a son adresse (`?carte=…&deck=…&theme=…&kw=1`) mise à jour en direct — bouton **Partager** dans la fiche (feuille native Android/iOS, sinon lien copié) et aperçu de la lame dans WhatsApp/iMessage via Open Graph
- **Arcana Index (v11 expérimental)** : bibliothèque Three.js dense, organisée en rayonnages 3D par famille et numéro, avec recherche instantanée et fiche au clic
- **Soixante-Dix-Huit (v13 expérimentale)** : étagère 3D animée (Three.js) façon « best-sellers » — lames reliées en livres rares numérotés, caméra qui glisse le long du rayonnage, hover qui soulève, clic qui amène la lame en grand avec fiche-livre, filtres par famille
- **Navigation clavier** : saisie directe pour chercher, `←`/`→` entre les fiches et `Échap` pour revenir
- **Architecture autonome** : application PHP et contenu embarqué dans un vault SQLite

## 🧠 Utilisation

1. Parcourez les 78 lames sur l'accueil ou sélectionnez un intercalaire pour filtrer une famille.
2. Cliquez une lame pour ouvrir sa fiche détaillée.
3. Utilisez `←`/`→` pour passer à la lame précédente ou suivante.
4. Tapez une lettre ou un chiffre pour ouvrir et préremplir la recherche.
5. Ouvrez **Tirages**, **Nuances** ou **Apprendre** depuis la navigation supérieure.
6. **Tirez en mode Piocher** : basculez le mode dans le menu Tirages (ou depuis les Réglages), coupez le paquet à la hauteur voulue, puis touchez un emplacement vide et choisissez votre carte dans l'éventail.
7. **Partagez une lame** : bouton *Partager* dans la fiche, ou copiez simplement l'adresse — elle contient la lame et l'affichage en cours.

## ⚙️ Réglages

La palette globale et les couleurs d'accent sont définies via les variables CSS du bloc `:root` dans `src/website/v9/index.php`.

## 🧾 Commandes

| Touche | Action |
|--------|--------|
| Lettre ou chiffre | Ouvrir et préremplir la recherche |
| `←` / `→` | Carte précédente / suivante (vue fiche) |
| `Échap` | Retour à la grille depuis une fiche |
| `1`–`5` | Répondre dans le mode apprentissage |
| `Entrée` / `→` | Passer à la lame suivante (mode apprentissage) |
| `Entrée` | Valider la coupe du paquet (mode Piocher) |
| `Échap` | Fermer l'éventail ou annuler la coupe |

## 📦 Build & Package

La V9 ne nécessite pas de build : `index.php` sert l'interface et les ressources, tandis que `vault.sqlite` contient les données et illustrations.

## 🧪 Test local

Le site (V9) se lance depuis `src/website/v9/` avec `launch.command` (double-clic
macOS : port libre, PHP, ouvre le navigateur), ou en CLI :

```bash
php -n -d auto_prepend_file= -S 127.0.0.1:8772 -t src/website/v9 src/website/v9/index.php
```

Pour tester les versions archivées (V2 à V7, extraites depuis les branches git),
utiliser `scripts/tester-server.py`. Ne pas ouvrir un `index.html` en `file://` :
le navigateur bloque alors `fetch()` vers SQLite et le chargement de WebAssembly.

## 📋 Voir le [CHANGELOG](CHANGELOG.md) pour l'historique complet.

Version courante : **2026.10.01**

## 🔗 Liens

- **Version courante (V9)** : [mondary.design/pk/-Games-cards/tarot](https://mondary.design/pk/-Games-cards/tarot/)
- **V8** : [mondary.design/pk/-Games-cards/tarot8](https://mondary.design/pk/-Games-cards/tarot8/)
- **V7** : [mondary.design/pk/-Games-cards/tarot7](https://mondary.design/pk/-Games-cards/tarot7/)
- Anciens sites : [V3](https://mondary.design/pk/tarot3/) · [V1/V2](https://mondary.design/pk/tarot/)
- **Illustrations** : Tarot Rider-Waite-Smith — domaine public
- **Typographies** : [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

Le code et le design de ce projet sont sous licence MIT (voir `LICENSE`).
Les textes descriptifs sont originaux. Les illustrations du Tarot de Rider-Waite-Smith sont dans le domaine public.

[Soutenir sur Ko-fi](https://ko-fi.com/pouark)

Page promotionnelle : [store/website](store/website/index.html) (français uniquement ; version bilingue à compléter). Médias historiques : `store/v1/`.
