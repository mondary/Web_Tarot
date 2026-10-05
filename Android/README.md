# Tarot Divinatoire — Android

Application Capacitor hors ligne. Une seule interface web partagée avec iOS, incluant le moteur complet des tirages.

- `src/mobile/src/` (au dépôt racine) : interface unifiée — rituel, tirages (coupe, éventail, croix, prénom), bibliothèque, journal.
- `src/website/tarot-spreads.js` : moteur des tirages embarqué tel quel depuis le site V9.
- `app/`, `gradle/` : projet natif, identifiant `fr.mondary.tarotdivinatoire`.
- `store/` : textes, visuels, confidentialité et procédure de publication.
- `release/` : APK installable, AAB et empreintes SHA-256 récupérés de CI (ignorés par Git).

Depuis la racine : `pnpm install --frozen-lockfile`, puis `pnpm android:sync`
(exporte le web depuis le vault puis synchronise Capacitor).
La compilation s’effectue dans GitHub Actions, workflow **Android release** (Java 21, SDK 36). Aucun SDK à installer sur le Mac.

## Fonctionnement

Carte quotidienne mémorisée selon la date locale, tirages complets en mode piocher (coupe à la hauteur choisie, éventail tactile) ou rapide, ordre prédéterminé ou libre, recherche insensible aux accents, filtres par famille, lectures thématiques, notes et suppression locale. Les 78 illustrations et les polices sont embarquées. Sans publicité, compte, analytics ou permission sensible. Sauvegarde Android désactivée.

La désinstallation efface le journal. Les lectures sont symboliques ; aucun service de prédiction n’est fourni.
