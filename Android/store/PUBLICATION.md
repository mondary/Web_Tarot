# Publication Google Play

## Livrables

- `../release/tarot-divinatoire-debug.apk` : installation et essais, signature de développement uniquement.
- `../release/tarot-divinatoire-unsigned.aab` : compilation de production vérifiable ; NON publiable tant qu’elle n’est pas signée.
- Avec les secrets configurés, la CI produit `tarot-divinatoire-signed.aab`.
- `fr-FR/` : nom, descriptions et nouveautés prêts à copier.
- `assets/` : icône 512 × 512, feature graphic 1024 × 500, captures portrait de l’interface.
- `privacy.html` : politique à héberger sur une URL HTTPS publique, stable et accessible sans connexion.

## Signature

Dans les secrets GitHub du dépôt, renseigner : `ANDROID_KEYSTORE_BASE64`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS`, `ANDROID_KEY_PASSWORD`.
Utiliser la clé d’import existante si l’application est déjà enregistrée sur Play. Pour une première publication, créer et conserver une clé d’import privée, puis activer Play App Signing. Ne jamais versionner cette clé.
La CI utilise son numéro d’exécution comme versionCode : vérifier qu’il dépasse celui déjà publié et adapter si nécessaire.

## Play Console : informations à compléter par le propriétaire

1. Créer/sélectionner l’application `fr.mondary.tarotdivinatoire`, français, application, catégorie proposée : Livres et références.
2. Renseigner l’adresse e-mail de support réelle, l’identité et les coordonnées du développeur ; choisir pays et prix.
3. Héberger `privacy.html` et renseigner son URL publique. Vérifier les coordonnées de contact du document.
4. Coller les textes, téléverser l’icône, la feature graphic et les captures. Captures issues du rendu web réel de l’app ; valider aussi le résultat natif sur téléphone avant soumission.
5. Sécurité des données : aucune donnée collectée ni partagée ; notes et tirages traités uniquement localement. Aucun SDK publicitaire ou analytique.
6. Publicités : non. Accès à l’application : toutes les fonctions accessibles sans compte. Aucun achat intégré.
7. Compléter sincèrement le questionnaire IARC et sélectionner l’audience visée ; ne pas inventer une classification avant la réponse de Google.
8. Vérifier les droits des textes et illustrations pour les pays distribués (Rider–Waite–Smith, Pamela Colman Smith, 1909). Aucun transfert de droits n’est présumé par la génération du paquet.
9. Importer l’AAB signé en test interne, puis satisfaire l’éventuel test fermé imposé au compte. Consulter le rapport de pré-lancement.
10. Tester sur Android : lancement hors ligne, rotation, retour système, taille de texte élevée, recherche, lecture, journal après redémarrage, suppression et TalkBack. Soumettre ensuite à examen.

## Références officielles vérifiées le 19 septembre 2026

- API cible : Android 16 / API 36 : https://developer.android.com/google/play/requirements/target-sdk
- Visuels : https://support.google.com/googleplay/android-developer/answer/9866151
- Signature : https://developer.android.com/studio/publish/app-signing

La présence des fichiers ne constitue ni une soumission ni une approbation Google Play. Signature privée, accès Play Console, e-mail de support et URL publique de confidentialité restent à fournir.
