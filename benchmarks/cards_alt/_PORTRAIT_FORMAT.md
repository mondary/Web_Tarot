# FORMAT _portrait.md — Lecture incarnée d'une carte

> Un fichier par carte, nommé `{prefix}_{num}_{slug}_portrait.md`
> (ex : `a_00_Fou_portrait.md`, `d_09_Neuf_de_Denier_portrait.md`)

## Principe

Décrire une carte comme on dresserait le **portrait d'une personne réelle** :
non pas lister ses symboles, mais dire **qui elle est** et **ce qui s'y joue**,
en partant de ce que l'image **montre** avant de dire ce qu'elle **signifie**.

L'objectif n'est pas l'exhaustivité (ça, c'est le rôle du fichier principal
et du `_symbols.md`), mais de **fixer la carte dans la mémoire** par un
angle unique, repérable au premier coup d'œil.

## Format exact (coller tel quel)

```
{emoji_thématique} {numéro} · {NOM DE LA CARTE}
🧠 Idée centrale : Je + verbe d'action ou d'état
💭 Ce qui se passe réellement : 1 à 2 phrases, 1re personne, situation concrète (pas le symbole).
🔑 Mot-clé distinctif : TRAIT COURT (1 à 3 MOTS) EN MAJUSCULES

🖼️ Ce que me raconte l'image :

{Élément visible}, idéalement avec un verbe d'état → sens interprété.

{Autre élément visible} → sens interprété.

{Autre élément visible} → sens interprété.

{Autre élément visible} → sens interprété.

✨ L'image me dit : « Je… »
```

## Les 5 blocs (rôle et contraintes)

| Bloc | Rôle | Forme |
|------|------|-------|
| 🧠 **Idée centrale** | La phrase qui résume la carte, à la 1re personne | `Je + verbe` |
| 💭 **Ce qui se passe réellement** | La situation concrète, pas le symbole | 1 à 2 phrases, 1re personne |
| 🔑 **Mot-clé distinctif** | Un trait court (1 à 3 mots), en majuscules, qui signe la carte | Un **trait de caractère**, pas un domaine de vie |
| 🖼️ **Ce que me raconte l'image** | Lecture visuelle, du détail vers le sens | 4 à 5 paragraphes courts, format `visible → sens` |
| ✨ **L'image me dit** | Phrase de clôture, citation directe | `« Je… »` (1re personne, courte) |

## Règles strictes

- **Emoji thématique en tête** : choisi en fonction d'un élément visuel central
  de la carte (🦁 pour la Force, 🗝️ pour le Pape, ⚡ pour la Tour…). Pas
  d'emoji générique.
- **1re personne partout** (« je », pas « le consultant » / « la carte »).
  La carte parle, on ne parle pas sur elle.
- **Visuel d'abord** : chaque puce du bloc 🖼️ part d'un élément **visible**
  dans l'illustration (posture, regard, objet, décor), jamais d'un concept abstrait.
- **Un trait court (1 à 3 mots)** : le mot-clé peut être un mot seul
  (ÉLAN, UNION, FIN) ou une expression courte (SAVOIR CACHÉ, LÂCHER-PRISE).
  Critère : qu'il reste frappant et distinctif. Éviter les mots-valise
  seuls (amour, succès, sécurité…) qui peuvent s'appliquer à plusieurs cartes.
- **Pas de doublon avec les autres fichiers** : pas de liste de mots-clés
  à l'endroit/envers, pas de section Amour/Travail/Finances, pas
  d'associations — tout ça vit déjà ailleurs. Le portrait est une **porte
  d'entrée**, pas un manuel.
- **Pas de gras / pas de `#` markdown** : les 5 emojis sont la grammaire
  du format, le texte suit en clair.
- **FR par défaut**. Pour un portrait EN : `_portraitEN.md`.
- **Citation finale entre guillemets français** : « … »

## Quand le format prend tout son sens

Le portrait est le plus utile quand une carte **ressemble à une autre** et
qu'on a besoin d'un coin distinctif immédiat. Dans ce cas, la lecture visuelle
doit faire apparaître — implicitement ou explicitement — ce qui sépare la
carte de ses voisines.

Exemples validés :
- 9 / Reine / Roi de Deniers → le **regard** (profite / s'en occupe / ne voit même plus).
- Fou vs Magicien → **mouvement vs immobilité**.
- 4 / 8 / 9 d'Épées → **retrait / enfermement / rumination** (la posture du corps).

## Convention de nommage

- Fichier : `{prefix}_{num}_{slug}_portrait.md`
- Slug aligné sur le fichier principal de la carte (ex : `a_04_Emperor` et pas
  `a_04_Empereur`, parce que le `.md` principal est en anglais).
- Pour les portraits croisés multi-cartes (ex : les 3 Deniers ensemble),
  créer un dossier séparé `cards_alt/portraits_croisés/` plutôt que de
  dupliquer dans chaque fichier carte.

## Anti-patterns

- ❌ Reprendre les mots-clés du fichier `_ES.md` comme « mot-clé distinctif ».
  Le mot-clé distinctif est un **trait de caractère** (1 à 3 mots), pas un
  domaine de vie.
- ❌ Faire un paragraphe long en 🧠 ou 💭. Ces blocs sont des **phrases courtes**.
- ❌ Mélanger description visuelle et interprétation abstraite dans une même puce.
  Toujours : `visible → sens`.
- ❌ Plusieurs phrases en ✨. Une seule, qui clôt.
- ❌ Cross-reference explicite à une autre carte dans le corps (sauf si
  pédagogiquement indispensable — ex : « contrairement au Fou »). Privilégier
  la différentiation implicite via le choix visuel.
