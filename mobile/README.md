# Tarot Divinatoire mobile

La source mobile est une application Capacitor **hors ligne**. Au build,
`mobile/scripts/export.php` extrait les données, les 78 illustrations et les polices
du vault V9 vers `mobile/www/`, puis Capacitor les intègre à Android et iOS.

## Packages livrés

- `release/android/tarot-divinatoire.aab` : bundle Android à signer et publier sur Google Play.
- `release/ios/TarotDivinatoire-unsigned.app` : build iOS non signé, utile pour vérifier la
  compilation. Un IPA App Store nécessite le compte Apple Developer, un identifiant d’app
  enregistré et des certificats/profils de provisionnement.

Les dossiers sont créés par le workflow GitHub Actions **Mobile release packages** ; les
paquets sont ensuite disponibles comme artefacts du workflow.

## Publication Store

Le build est conçu pour les Stores : pas de serveur embarqué, aucun compte, aucune télémétrie,
aucun contenu distant et une politique de confidentialité incluse. Avant publication, renseigner
les fiches Google Play et App Store Connect avec le même identifiant `fr.mondary.tarotdivinatoire`
et fournir une icône de store carrée de 1024 × 1024 px.
