![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

Un site éditorial pour explorer les 78 lames du Tarot de Rider-Waite-Smith, leurs significations et leurs associations.

![Réglages et soutien](store/screenshots/01-reglages.png)

![Couper le paquet](store/screenshots/03-tirage-coupe.png)

![Éventail des 78 cartes](store/screenshots/04-tirage-eventail.png)

![Éventail sur mobile](store/screenshots/05-tirage-eventail-mobile.png)

![Écran dédié au tirage du prénom](store/screenshots/06-tirage-prenom.png)

![Croix de Décision avec cinq illustrations révélées](store/screenshots/07-croix-revelee.png)

## ✅ Fonctionnalités

- **78 cartes** : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers)
- **Accueil complet** : les 78 lames sont visibles directement, regroupées par famille avec cinq intercalaires
- **Fiches éditoriales** : identité, carte du jour, Amour, Travail, Finances, Guidance, signification et description
- **Associations** : combinaisons entre lames accessibles dans un panneau repliable
- **Tirages interactifs** : plusieurs dispositions intégrées, dont la Croix de Décision (illustrations visibles après révélation) et le tirage du Prénom avec écran de saisie dédié
- **Deux modes de tirage** : *Piocher* (par défaut) — couper le paquet mélangé à la hauteur choisie, puis choisir ses cartes — ou *Rapide* (distribution automatique). Sur ordinateur : éventail étalé de droite à gauche, la carte survolée sort légèrement du paquet. Sur mobile : arc peu courbé, défilement tactile natif ou molette, carte centrale légèrement sortie du paquet ; touchez une carte pour la piocher directement. Le dos sombre au symbole doré est utilisé dans l'éventail et le tirage. Les boutons ↑ / ↓ permettent de parcourir les cartes. La coupe et l'éventail s'adaptent aux petits écrans et au paysage. La Carte du jour est mémorisée pour la journée.
- **Ordre de tirage guidé** : chaque position porte son numéro d'ordre (①②③…) et la prochaine à piocher ou révéler pulse en doré. Par défaut l'ordre est *prédéterminé* (pioche puis révélation dans l'ordre, l'éventail s'enchaîne tout seul) ; un ordre *libre* reste disponible dans le menu Tirages pour ceux qui veulent piocher et révéler comme ils le sentent
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
6. **Tirez en mode Piocher** : choisissez un tirage et coupez le paquet à la hauteur voulue. Sur mobile, glissez pour parcourir les cartes puis touchez celle que vous voulez piocher. En ordre prédéterminé, la position suivante s'ouvre automatiquement ; en ordre libre, touchez l'emplacement souhaité. Révélez ensuite les cartes posées.
7. **Tirage du Prénom** : choisissez-le dans la liste, saisissez votre prénom dans l'écran dédié, puis poursuivez vers le tirage. Le bouton Retour vous ramène à la liste.
8. **Partagez une lame** : bouton *Partager* dans la fiche, ou copiez simplement l'adresse — elle contient la lame et l'affichage en cours.

## ⚙️ Réglages

La palette globale et les couleurs d'accent sont définies via les variables CSS du bloc `:root` dans `src/website/index.php`.

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
| `↑` / `↓`, `Début` / `Fin` | Choisir une carte quand l'éventail mobile a le focus |

## 📦 Build & Package

La V9 ne nécessite pas de build : `index.php` sert l'interface et les ressources, tandis que `vault.sqlite` contient les données et illustrations.

## 🧪 Test local

Le site (V9) se lance depuis `src/website/` avec `launch.command` (double-clic
macOS : port libre, PHP, ouvre le navigateur), ou en CLI :

```bash
php -n -d auto_prepend_file= -S 127.0.0.1:8772 -t src/website src/website/index.php
```

Pour tester les versions archivées (V2 à V7, extraites depuis les branches git),
utiliser `scripts/tester-server.py`. Ne pas ouvrir un `index.html` en `file://` :
le navigateur bloque alors `fetch()` vers SQLite et le chargement de WebAssembly.

## 📋 Voir le [CHANGELOG](CHANGELOG.md) pour l'historique complet.

Version locale courante : **2026.10.13**

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
