# Arcana Index — Tarot v11

Version expérimentale orientée recherche : les 78 lames sont de vrais meshes Three.js, regroupés en rayonnages 3D compacts par famille et numéro, avec recherche, filtres, drag orbital, zoom et fiche latérale.

## Lancer

Depuis la racine du projet :

```sh
php -n -d auto_prepend_file= -S 127.0.0.1:8776 -t src/website/v11
```

Ouvrir `http://127.0.0.1:8776/`.

La v11 lit le `vault.sqlite` de `../v9/` en lecture seule. Les données ne sont pas dupliquées, mais l'interface est isolée dans ce dossier.
