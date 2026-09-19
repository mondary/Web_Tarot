# Tarot Divinatoire — Android

Application Capacitor hors ligne. Sources, projet Android natif et dossier Google Play réunis ici.

- `web/` : interface française, rituels, bibliothèque, journal.
- `scripts/prepare.py` : export des 78 cartes depuis le vault du projet.
- `app/`, `gradle/` : projet natif, identifiant `fr.mondary.tarotdivinatoire`.
- `store/` : textes, visuels, confidentialité et procédure de publication.
- `release/` : APK installable, AAB et empreintes SHA-256 récupérés de CI (ignorés par Git).

Depuis la racine : `pnpm install --frozen-lockfile`, puis `pnpm android:sync`.
La compilation s’effectue dans GitHub Actions, workflow **Android release** (Java 21, SDK 36). Aucun SDK à installer sur le Mac.
Le site et l’application iOS conservent leurs sources actuelles.

## Fonctionnement

Carte quotidienne mémorisée selon la date locale, trois cartes sans doublon, recherche insensible aux accents, filtres par famille, lectures thématiques, notes et suppression locale. Les 78 illustrations et les polices sont embarquées. Sans publicité, compte, analytics ou permission sensible. Sauvegarde Android désactivée.

La désinstallation efface le journal. Les lectures sont symboliques ; aucun service de prédiction n’est fourni.
