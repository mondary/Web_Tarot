# Soixante-Dix-Huit — Tarot v13

Showcase éditorial des 78 lames, façon « best-sellers book showcase » : grande typographie serif (Iowan Old Style, interlettrage -0.085), papier crème, accent `#c3a47b`. Les lames sont reliées comme une collection de livres rares présentés en perspective 3D (DOM/CSS pur, aucun WebGL), avec dos numérotés, fiche-livre au clic, filtres par famille, molette/glisser/clavier.

## Lancer

Depuis la racine du projet :

```sh
php -n -d auto_prepend_file= -S 127.0.0.1:8778 -t src/website/v13
```

Ouvrir `http://127.0.0.1:8778/`.

La v13 lit le `vault.sqlite` de `../v9/` en lecture seule (données et images non dupliquées). Interface isolée dans ce dossier, la V9 n'est pas modifiée.
