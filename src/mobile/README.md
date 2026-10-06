# Tarot Divinatoire mobile

La source mobile est **le site actuel** `src/website/index.php`, exporté hors ligne
par `src/mobile/scripts/export.php`. Le même HTML, CSS et JavaScript que la version
web sont intégrés à Android et iOS ; seul le routage PHP des ressources est remplacé
par des fichiers locaux. Le vault fournit les illustrations des trois jeux, les
associations, les symboles et les polices. `src/mobile/src/` ne contient que la
politique de confidentialité, **pas une seconde interface**.
La taille du texte (Petit, Normal, Grand) est commune au site et au bundle ;
elle est enregistrée localement dans chaque installation. L'icône native vient
du `icon.png` de la racine, sans illustration de remplacement.

L'interface reprend les thèmes, les trois jeux, la bibliothèque des 78 cartes,
les associations et les tirages complets du site (coupe, éventail, croix,
passé-présent-futur, prénom, modes piocher/rapide et ordre libre). Il n'y a pas
de journal de notes dans le site ni dans ce bundle mobile.

`node_modules/` n'est pas nécessaire pour consulter le site ni pour l'export PHP :
les dépendances pnpm/Capacitor servent uniquement à empaqueter les plateformes
natives, en CI. Le dossier local peut être supprimé et recréé avec `pnpm install`.

## Packages livrés

- `release/android/tarot-divinatoire-release.apk` : APK installable signé avec la clé durable du projet.
- `release/android/tarot-divinatoire-debug.apk` : APK de développement, signature temporaire ; ne pas utiliser pour les mises à jour.
- `release/android/tarot-divinatoire-signed.aab` : bundle Android signé pour Google Play.
- `release/ios/TarotDivinatoire-unsigned.app` : build iOS non signé, utile pour vérifier la
  compilation. Un IPA App Store nécessite le compte Apple Developer, un identifiant d’app
  enregistré et des certificats/profils de provisionnement.

Les artefacts sont générés par les workflows GitHub Actions **Android release**
et **Mobile release packages**, puis téléchargeables depuis leurs runs.
Si un ancien APK debug est déjà installé, le désinstaller avant la première
installation de l'APK release : les signatures diffèrent. Cette désinstallation
efface les préférences locales. Ensuite, les versions release signées avec la
même clé pourront se mettre à jour par-dessus.

## Publication Store

Le build est conçu pour les Stores : pas de serveur embarqué, aucun compte, aucune télémétrie,
aucun contenu distant et une politique de confidentialité incluse. Avant publication, renseigner
les fiches Google Play et App Store Connect avec le même identifiant `fr.mondary.tarotdivinatoire`
et fournir une icône de store carrée de 1024 × 1024 px.
