# Changelog

Toutes les notes de release du projet Web Tarot. Le format de version suit `🔥vMAJOR.YY.PATCH`.

---

## TODO — Roadmap

Statut : `2.2026.4` (trois vues de lecture livrées, enrichissement du contenu en cours d'amélioration continue)

### Phase 1 — Fondations ✅
- [x] Interface V2 unifiée (carousel d'accueil + grille par famille + vue détail)
- [x] 78 cartes référencées (22 arcanes majeurs + 56 mineurs)
- [x] Fiches .md enrichies pour les 78 cartes
- [x] Génération de `data.js` (contenu .md embarqué, fonctionnement hors-ligne)
- [x] Navigation continue traversant les 5 familles en boucle
- [x] Grille : carousel horizontal de familles (swipe + boutons + indicateurs)
- [x] Vue détail alternative en diaporama plein écran (`index_full.html`)
- [x] Vue détaillée éditoriale (`index_detail.html`)
- [x] Matelas blanc simulant la bordure réelle des cartes

### Phase 2 — Expérience
- [ ] Tirages (spreads) interactifs (croix, past, présent, futur…)
- [ ] Mode diaporama plein écran avec lecture auto
- [x] Recherche et filtrage des cartes par mot-clé
- [ ] Animations d'entrée et transitions entre vues
- [ ] Sélecteur de thème / palette

---

## Releases

### [2.2026.4] - 2026-07-19
#### Changed
- Vue détaillée restructurée : diptyque de lecture, planche annotée, description mise en avant et domaines en deux colonnes avec icônes
- Mots-clés à l'endroit et à l'envers correctement extraits dans le diptyque
- Accueil mobile ajusté pour garder la première lame visible

#### Fixed
- Fond opaque appliqué au header sticky des grilles pour empêcher le chevauchement du contenu

### [2.2026.3] - 2026-07-19
#### Added
- Troisième vue détaillée (`index_detail.html`) : dossier éditorial noir, métadonnées, lecture endroit/envers et article de la lame
- Sélecteur segmenté commun aux vues classique, immersive et détaillée, en conservant la lame via le hash URL
- Icône du Tarot dans l'en-tête et favicon

#### Changed
- Navigation d'accueil compatible avec la molette verticale, en plus du défilement horizontal
- Retours, sélecteur de vue et navigation immersive rendus lisibles sur les contenus sombres
- En-têtes de suites enrichis avec leur rôle symbolique

### [2.2026.2] - 2026-07-19
#### Added
- Choix entre vue classique et vue immersive depuis toutes les vues, avec conservation de la suite et de la lame via le hash URL
- Navigation clavier complète dans la recherche : flèches, Tab, focus visuel accentué et Entrée pour ouvrir la lame sélectionnée

#### Changed
- Hero immersif cadré sur la partie haute de la lame pour conserver le sujet principal visible
- Focus de recherche remplacé par un contour d'accent net, sans effet de sursaut

### [2.2026.1] - 2026-07-19
#### Changed
- Site **autoporté** : les 78 images sont embarquées en WebP (data URI) dans `data.js` — déploiement en déposant juste `index.html` + `data.js`, plus de dossier `cards/` requis
- Cartes des suites (accueil) en ratio tarot `2/3`, image remplissant la hauteur (88vh) sur tous les écrans, y compris très hautes résolutions
- Header de grille : défile avec les cartes sur mobile (`position:static`) pour libérer le visuel ; sticky sur desktop
- Terminologie tarot corrigée : « lames » (au lieu de cartes) et « suites » (au lieu de familles)
- Données enrichies par suite : élément + symbole alchimique, 3 signes astrologiques avec glyphes, ligne d'élément et description symbolique

#### Added
- Recherche instantanée FZF (par occurrences) : une lettre tapée n'importe où ouvre une overlay, requête affichée en géant, grille des 78 lames filtrée (nom + suite + élément + mots-clés, sans accent ni casse)
- Vue détail alternative `index_full.html` : diaporama plein écran, hero mega-zoom pleine largeur tronqué par un fondu, panneau contenu qui remonte par-dessus, navigation par swipe et boutons latéraux
- Grille : carousel horizontal de suites (swipe tactile, boutons desktop, indicateurs en points)

### [1.2026.1] - 2026-07-18
#### Added
- Interface unifiée en un seul fichier : vue d'accueil (carousel horizontal), grille par famille, et vue détail avec fiche complète
- 78 cartes : 22 arcanes majeurs + 56 mineurs (Bâtons, Épées, Coupes, Deniers), images et fiches descriptives
- `build_data.js` : script de génération produisant `data.js` (contenu .md embarqué via `<script src>` pour un fonctionnement en `file://`)
- Navigation continue : les flèches précédent / suivant traversent les 78 cartes en boucle, d'une famille à l'autre
- Grille : carousel horizontal de familles (swipe tactile, boutons desktop, indicateurs en points)
- Vue détail alternative `website/index_full.html` : diaporama plein écran, hero de la carte qui se replie au scroll, panneau de contenu qui remonte par-dessus, navigation par swipe et boutons latéraux
- Hero « mega zoom » pleine largeur dans `index_full.html` (couverture cinématographique tronquée avec fondu vers le panneau)
- Recherche instantanée FZF (par occurrences) : une lettre tapée n'importe où ouvre une overlay, requête affichée en géant, grille des 78 cartes filtrée (nom + famille + élément + mots-clés, sans accent ni casse)
- Site **autoporté** : les 78 images embarquées en WebP (data URI) dans `data.js` — déploiement en déposant juste `index.html` + `data.js`, aucun dossier d'images requis
- Terminologie tarot : « lames » (au lieu de cartes) et « suites » (au lieu de familles)
- Barre de boucle indiquant la position globale et les cartes voisines
- Détail par famille : couleur d'accent dédiée (Majeurs, Bâtons, Épées, Coupes, Deniers)
- Rendu Markdown léger et navigation par section (Signification, Mots-clés, Interprétation, Amour, Travail, Finances, Guidance)
- Film grain, vignette et typographie éditoriale (Cormorant Garamond + Plus Jakarta Sans + DM Mono)
- Support clavier (Échap, flèches) et navigation tactile

#### Changed
- Refonte V2 de l'interface (remplace la V1 archivée)
- Matelas blanc (`#ffffff`) autour des illustrations pour simuler la bordure des cartes réelles, avec filet discret et ombre portée
- Le titre « Tarot Divinatoire » défile désormais avec les cartes (au lieu d'être une barre latérale fixe)

#### Fixed
- Correction du rendu Markdown : fermeture correcte des sections et gestion des lignes de définition
