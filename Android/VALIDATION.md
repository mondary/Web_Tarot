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
