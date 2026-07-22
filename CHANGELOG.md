# Changelog

Toutes les notes de release du projet Web Tarot. Le format de version suit `🔥vMAJOR.YY.PATCH`.

---

## TODO — Roadmap

Statut : `3.2026.11` (traduction complète des symboles en français)

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

### [3.2026.11] - 2026-07-22
#### Fixed
- Traduction complète du contenu anglais en français dans les 78 fichiers `*_symbols.md` (symboles des cartes Rider-Waite)
- Régénération de `data.js` avec le contenu traduit
- `data.js` version incrémentée à `3.2026.11`

### [3.2026.3] - 2026-07-21
#### Fixed
- Mots-clés dans vue détaillée : prennent toute la largeur (override `max-width:44rem` du `.prose`)

### [3.2026.1] - 2026-07-21
#### Changed
- **Fichiers _ES.md** : expurgés de l'espagnol, contenu FR-only dans `website/cards/`
- Originaux ESP+FR sauvegardés dans `benchmarks/cards_ES_original/`
- `data.js` reconstruit avec contenu français uniquement

### [3.2026.0] - 2026-07-21
#### Added
- **Page d'accueil** (`index.html`) dédiée avec navigation vers les 4 vues (Classique, Immersive, Détaillée, Rapide)
- **Mots-clés en 2 colonnes** (À l'endroit / À l'envers) sur les vues Immersive, Détaillée et Classique
- **Affirmation + Réponse** en style hero au-dessus de l'Interprétation sur les 3 vues principales
#### Changed
- **Architecture restructurée** : `index.html` = home, `index_classic.html` = vue classique (grille directe sans carousel)
- **Vue Immersive** : carousel remplacé par grille continue verticale (78 cartes + séparateurs famille)
- **Vue Détaillée** : carousel horizontal remplacé par grille continue verticale
- **SVG famille** inline avec couleur accent via `currentColor` dans la vue Rapide
- Section "Affirmation" renommée "Citation" (c'est une citation, pas l'affirmation ES)
#### Fixed
- Navigation mode-switch : suppression de `syncModeLink` qui causait l'accumulation de `#grid#grid#grid`
- Navigation force `window.location.href` pour éviter les hash accumulés
- Vue classique : `goHome()` redirige vers la nouvelle home
- Vue immersive : accolade manquante dans `decorateThemeIcons` causant un SyntaxError

### [2.2026.27] - 2026-07-20
#### Added
- **Vue Rapide** (`index_quick.html`) : 4ᵉ vue du site — lecture express carte + Réponse + Affirmation + mots-clés
  - Grille continue des 78 cartes avec séparateur CSS par famille (glyphe élément, nom, description, compteur)
  - Fiche carte immersive : image plein écran (88vh), affirmation en grand (hero), Réponse subtile, mots-clés en cartouches
  - Mots-clés positifs combinés FR + ES (depuis les fichiers `_ES.md`)
  - Recherche plein écran identique aux autres vues (F / A-Z)
  - Navigation clavier : ← → pour changer de carte, Échap pour revenir, F pour chercher
- **Badges RÉPONSE + Affirmation** affichés en français sur les 4 vues (classique, immersive, détaillée, rapide)
- **Mots-clés ESP** extraits des fichiers `_ES.md` et embarqués dans `data.js` (`es.espKeywords`)
#### Changed
- `build_data.js` lit désormais la section `## FR` des fichiers `_ES.md` pour les affirmations et réponses en français
- Lien « Rapide » ajouté à la barre de navigation sur les 4 vues
#### Fixed
- 3 cartes sans données ES corrigées : `a_03_Impératrice`, `a_09_Hermite`, `b_11_Valet` (renommage des fichiers `_ES.md` pour correspondre au slug de la carte)
- Vue immersive : `esc()` manquant causait un crash au clic sur une carte (fonction ajoutée au scope global)

### [2.2026.26] - 2026-07-20
#### Changed
- Vues classique et détaillée : titre de carte centré, opaque et sticky en haut de la fiche, sur le modèle de la vue immersive
- Seul le titre reste fixe pendant la lecture ; métadonnées et actions défilent normalement, sans passer sous un header transparent

### [2.2026.22] - 2026-07-20
#### Fixed
- Boutons « Associations » et « Écouter la carte » : même structure HTML (SVG direct, sans wrapper), même hauteur (2.4rem), même CSS — visuellement identiques
- Suppression du CSS redondant `.tf-voice-btn` dans index.html (source unique : tarot-features.js)
- Version détaillée : fond du header remplacé par `var(--bg-2)` solide (plus de gradient transparent qui cachait les boutons)

### [2.2026.21] - 2026-07-20
#### Fixed
- Boutons « Associations » / « Écouter la carte » désormais stylés dès l'ouverture de la carte (le CSS et les injecteurs sont exposés immédiatement, avant la restauration par hash) — corrige le bouton Association « trop gros » en classique
- Version détaillée : la barre d'actions est placée sous le titre, à l'intérieur du header sticky (toujours visible, plus jamais sous le header)

### [2.2026.20] - 2026-07-20
#### Changed
- Les boutons « Associations » et « Écouter la carte » regroupés dans une barre d'actions visible en haut de chaque fiche, sur les trois vues
- Compteur « · 77 » retiré du libellé Associations (toujours 77 associations)

### [2.2026.19] - 2026-07-20
#### Changed
- Numéro de version déplacé dans l'en-tête, à côté du titre, pour rester visible en permanence

### [2.2026.18] - 2026-07-20
#### Changed
- Associations : ajout d'un petit signe `+` entre les deux cartes comparées

### [2.2026.17] - 2026-07-20
#### Changed
- Associations : texte agrandi et plus contrasté pour une lecture confortable
- Comparaisons : icône SVG fiable à la place du glyphe pouvant s'afficher en `??`
- Lecteur vocal : le bouton affiche maintenant `Écouter la carte` puis `Arrêter l'écoute`
- Bouton Tirages classique aligné verticalement sur les vues immersive et détaillée

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
