# Tarot Divinatoire — Android

Application Capacitor hors ligne. Son écran est le **vrai site web**, rendu depuis `src/website/index.php` ; aucune interface Android parallèle.

- `src/mobile/scripts/export.php` : rend la page du site et extrait toutes ses ressources du vault dans `src/mobile/www/`.
- `src/website/tarot-spreads.js` : moteur des tirages embarqué tel quel depuis le site V9.
- `app/`, `gradle/` : projet natif, identifiant `fr.mondary.tarotdivinatoire`.
- `store/` : textes, visuels, confidentialité et procédure de publication.
- `release/` : APK installable, AAB et empreintes SHA-256 récupérés de CI (ignorés par Git).

Installer de préférence `release/android/tarot-divinatoire-release.apk`, signé
avec la clé durable. Un ancien APK debug doit d'abord être désinstallé car sa
signature est différente (les préférences locales seront effacées).

Depuis la racine : `pnpm install --frozen-lockfile`, puis `pnpm android:sync`
(exporte le web depuis le vault puis synchronise Capacitor).
La compilation s’effectue dans GitHub Actions, workflow **Android release** (Java 21, SDK 36). Aucun SDK à installer sur le Mac.

## Fonctionnement

Carte du jour, tirages complets en mode piocher (coupe et éventail tactile) ou rapide, ordre prédéterminé ou libre, recherche, filtres par famille, associations et lectures thématiques. Les 78 cartes des jeux RWS, CLM et Marseille ainsi que les polices sont embarquées. Thèmes Nuit, Ivoire et Sylve. Sans publicité, compte, analytics ou permission sensible. Sauvegarde Android désactivée.

La désinstallation efface les préférences locales. Il n'y a pas de journal de notes. Les lectures sont symboliques ; aucun service de prédiction n’est fourni.
