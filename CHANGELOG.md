# Changelog

Toutes les notes de release du projet Web Tarot. Le format de version suit `🔥vMAJOR.YY.PATCH`.

---

## TODO — Roadmap

Statut : `2.2026.16` (lectures comparatives à deux cartes dans toutes les vues)

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
- [x] Scanner mobile de lames par caméra
- [ ] Animations d'entrée et transitions entre vues
- [ ] Sélecteur de thème / palette

---

## Releases

### [2.2026.16] - 2026-07-20
#### Changed
- Associations : chaque comparaison affiche désormais la carte courante et sa partenaire côte à côte, avec la lecture à droite
- Déclencheur Associations explicite sur desktop et mobile dans les vues classique, immersive et détaillée
#### Fixed
- Suppression des associations d'une carte avec elle-même
- Suppression du bouton Tirages dupliqué dans la vue classique

### [2.2026.15] - 2026-07-20
#### Changed
- Remplacement du Miroir par une grille complète présentant toutes les associations dans une seule fenêtre, organisée par suite
- Carte du jour tirée aléatoirement par navigateur puis conservée pour chaque date locale
#### Fixed
- Lecteur vocal : utilise désormais le parseur Markdown local dans les trois vues
- Compteur de combinaisons fiable malgré les ponctuations atypiques de certaines sources

### [2.2026.14] - 2026-07-20
#### Added
- Miroir des Lames : lecture immersive des associations en tirage à deux cartes dans les trois vues
- Navigation par suite, compteur, flèches et clavier dans les 5904 combinaisons
#### Fixed
- Écran noir de la vue classique causé par une apostrophe JavaScript non échappée
- Chargement de `tarot-features.js` dans la vue classique
- Conteneur manquant dans la vue immersive pour les boutons vocal et Miroir
- Quelques coquilles récupérées dans les associations source

### [2.2026.13] - 2026-07-20
#### Added
- 78 fichiers `_associations.md` (5904 combinaisons à 2 cartes) scrapés depuis guide-tarot.com, structurés en 5 sections (Arcanes majeurs, Bâtons, Coupes, Épées, Deniers)
- Champ `associations` exposé sur chaque carte dans `data.js` (prêt pour intégration UI)
- `build_data.js` : filtre `_associations.md` du scan de cartes, charge le contenu dans `card.associations`

### [2.2026.12] - 2026-07-19
#### Added
- Lecteur vocal (Web Speech API) : bouton « Écouter la carte » sur les pages de détail des 3 vues, lit l'interprétation et la description en français
#### Changed
- Scanner : feedback visuel en temps réel — nom de la carte détectée, score de confiance (%), compteur de validation 0/2 → 1/2 → 2/2
- Scanner : cadre animé (pulse doré en recherche, bordure verte + halo à la détection)
- Fermeture du scanner arrête automatiquement la lecture vocale

### [2.2026.11] - 2026-07-19
#### Changed
- Accueil : première lame de chaque famille mise en avant avec son nom, tout en conservant la signification de la famille
- Vue immersive : les cinq grilles de familles se lisent maintenant de haut en bas, sans carousel latéral
- Vue détaillée : les cartes de grille affichent directement les mots-clés à l'endroit
- Bouton Tirages remonté pour ne plus recouvrir la version ni les liens du footer

### [2.2026.10] - 2026-07-19
#### Fixed
- Scanner de lames accéléré : empreintes visuelles compactes pré-générées, sans décodage des 78 illustrations à l'ouverture de la caméra
- Zone analysée alignée sur le cadre de scan affiché, pour éviter que l'arrière-plan ne perturbe la reconnaissance
- Validation ramenée à deux images espacées de 120 ms pour ouvrir la lame détectée en moins d'une seconde après le démarrage de la caméra

### [2.2026.5] - 2026-07-19
#### Added
- Scanner de lames mobile : reconnaissance locale des illustrations Rider-Waite-Smith via la caméra et ouverture directe de la fiche détectée

#### Changed
- Bouton de scan réservé aux écrans mobiles pour préserver l'interface desktop

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
