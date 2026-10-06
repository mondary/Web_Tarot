# Validation du 19 septembre 2026

Contrôles effectués dans Chromium mobile (393 × 852 puis 432 × 960) :

- 78 cartes exportées, chaque illustration présente.
- Page d’accueil sans image cassée ni débordement horizontal.
- Lecture de la carte du jour et affichage des textes thématiques.
- Journal : ajout avec note, rechargement complet, note toujours présente.
- Tirage à trois cartes : trois identifiants distincts.
- Recherche `etoile` : retrouve `L’Étoile` malgré l’accent.
- Captures réelles de l’accueil, de la bibliothèque et du journal.
- Syntaxe JavaScript contrôlée avec `node --check`.

La CI Android compile l’APK et l’AAB et lance `lintDebug`.
Les tests sur téléphone et TalkBack restent à effectuer avant soumission (voir `store/PUBLICATION.md`).

# Validation du 5 octobre 2026 — interface unifiée + moteur des tirages

`src/tests/mobile_app.py` (Chromium mobile tactile 390 × 844, bundle `src/mobile/www` servi localement) :

- Interface partagée Android/iOS : rituel, bibliothèque (78 cartes), journal.
- Moteur des tirages complet embarqué : menu, croix en mode rapide, cinq cartes
  révélées avec illustrations vérifiées au pixel, aucun noir.
- Bouton flottant Tirages au-dessus de la navigation basse, masqué sur le rituel.
- Journal fonctionnel après un tirage ; zéro erreur JavaScript, zéro violation CSP.
- Captures store réelles 1080 × 2400 : rituel, bibliothèque, journal, menu des tirages.
