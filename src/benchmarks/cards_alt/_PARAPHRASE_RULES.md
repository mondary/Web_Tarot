# RÈGLES DE PARAPHRASE — Site Tarot alternatif

## Objectif
Créer une version alternative du site où **tout le contenu textuel est paraphrasé** en style tarot (très proche de sens, mais bien reformulé), sans toucher au design ni à la structure.

## Fichiers à paraphraser (par carte)
Pour chaque carte, travailler dans `/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website_alt/cards/` :
1. `{prefix}_{num}_{slug}.md` — fichier principal (Description, Interprétation, Amour, Travail, Finances, Guidance…)
2. `{prefix}_{num}_{slug}_associations.md` — combinaisons à 2 cartes
3. `{prefix}_{num}_{slug}_ES.md` — Réponse / Mots-clés / Affirmation

## À CONSERVER EXACTEMENT (ne pas modifier)
- **Noms des cartes** : « Le Fou », « La Papesse », « As de Bâtons », « Trois de Coupes », « Cavalier de Deniers », etc.
- **Structure Markdown** : titres `#`/`##`/`###`, listes `-`, gras `**`, italique `*`, citations `>`, traits `---`
- **Références d'images** : `![...](...)`
- **Champs metadata** (ligne de méta en haut) : `**Type de Carte :**`, `**Élément :**`, `**Numérologie / Rang :**`, `**Planète :**`, `**Pierre / Cristal :**`, `**Plante :**`, `**Source :**`, `**Nom :**`, `**RÉPONSE :**`
- **Valeurs factuelles** : élément, planète, pierre, plante, numérologie — tels quels
- **Crédits sources** en bas de fichier : `*Source : [...]*, *Illustration : ...*`
- **Lignes de méthode** dans les `_associations.md` : `> **Méthode (tirage à 2 cartes)** : ...`
- **Majuscules mystiques** : Énergie, Intuition, Cœur, Univers, Autre, Soi, Destin, Aventure… — garder la majuscule
- **Titres de sections** comme `## Description`, `## Mots-clés`, `## Interprétation`, `## Le Fou et l'Amour`, `## Affirmation`, `## Associations avec…` — tels quels

## À PARAPHRASER (reformuler en restant très proche du sens)
- Tous les **paragraphes descriptifs** : Description, Interprétation, sections Amour/Travail/Finances/Guidance
- **Le texte explicatif** après chaque `**CarteA + CarteB** :` dans les fichiers `_associations.md`
- **Les items de mots-clés** sous `### À l'endroit` / `### À l'envers` (fichier principal) — reformuler chaque puce
- **Les affirmations citées** : `> « ... »` — reformuler la citation en gardant le sens et l'auteur
- **Les listes de mots-clés** des `_ES.md` (sous `**Mots-clés (à l'endroit) :**` / `**Mots-clés (à l'envers) :**`) — réordonner, synonymiser certaines entrées
- **L'affirmation courte** des `_ES.md` (après `**Affirmation :**`) — reformuler

## À REMPLACER PAR « VOUS »
Toutes les références à la troisième personne du consultant doivent devenir « vous » :
- « le consultant » → « vous »
- « le/la consultant(e) » → « vous »
- « du consultant » → « de vous » / « votre »
- « au consultant » → « à vous »
- « un consultant » → « vous »
- Exemple : « Le consultant se sent en difficulté » → « Vous vous sentez en difficulté »
- Exemple : « Le consultant jongle » → « Vous jonglez »
- Exemple : « Le consultant cherche à équilibrer » → « Vous cherchez à équilibrer »

Toute autre tournure impersonnelle ou troisième personne parlant du lecteur doit aussi passer au « vous ».

## STYLE
- **Rester très proche du sens** : pas d'ajout d'information, pas de suppression, pas d'embellissement hors-thème.
- **Bien reformuler** : varier la structure des phrases (actif/passif, inversions, éclater/fusionner des phrases), utiliser des synonymes.
- Exemples de synonymes utiles : « indique » → « suggère / révèle / annonce » ; « aventure » → « quête / péripétie » ; « élan » → « impulsion / souffle » ; « montre » → « révèle / met en lumière » ; « vous invite à » → « vous encourage à / vous pousse à ».
- **Garder le ton mystique/spirituel/ésotérique** — vocabulaire tarot conservé.
- **Légèrement plus direct ou poétique** quand c'est naturel, mais sans dénaturer.
- **Fidélité absolue** au sens de chaque phrase.

## EXEMPLES AVANT → APRÈS

### Description
AVANT : « Un jeune homme, avec en guise de bagage son petit baluchon, se met en route pour un voyage… Le sourire aux lèvres, il regarde le Soleil. »
APRÈS : « Un jeune homme s'élance sur le chemin, muni d'un modeste baluchon pour tout bagage… Le sourire aux lèvres, les yeux tournés vers le Soleil. »

### Interprétation
AVANT : « Si vous cherchez l'Amour, le Fou vous conseille de foncer, et de vous lancer pleinement dans l'aventure de la découverte de l'Autre. »
APRÈS : « Si vous êtes en quête d'Amour, le Fou vous invite à foncer et à vous engager de tout votre être dans l'aventure de la rencontre de l'Autre. »

### Section domaine (Travail)
AVANT : « Dans le domaine du Travail, Le Fou est de bon augure pour un nouvel emploi, un changement professionnel ou un nouveau projet. »
APRÈS : « Côté professionnel, le Fou augure favorablement d'un nouvel emploi, d'un changement de carrière ou d'un projet inédit. »

### Mots-clés (puces)
AVANT : « - Nouveau départ ou opportunité »
APRÈS : « - Renouveau ou occasion à saisir »

AVANT : « - Innocence, insouciance, liberté »
APRÈS : « - Innocence, légèreté, libre arbitre »

### Association (2 cartes)
AVANT : « **Le Fou / Le Mat + Le Magicien / Le Bateleur** : L'impulsion du Fou prend forme avec l'action du Magicien : une idée folle devient projet. C'est le potentiel qui cherche à s'exprimer par la volonté consciente. »
APRÈS : « **Le Fou / Le Mat + Le Magicien / Le Bateleur** : L'élan du Fou se matérialise grâce à l'action du Magicien : une idée insolite se mue en projet. C'est un potentiel qui demande à s'incarner par la volonté consciente. »

### « Le consultant » → « vous »
AVANT : « Le consultant se sent en difficulté au travail. Les circonstances peuvent être très diverses avec en point commun l'incapacité à prendre en main les choses. »
APRÈS : « Vous vous sentez en difficulté au travail. Les circonstances peuvent être très diverses, avec en point commun l'incapacité à prendre les choses en main. »

AVANT : « Le consultant jongle entre différentes options sans trop se soucier des conséquences. »
APRÈS : « Vous jonglez entre différentes options sans trop vous soucier des conséquences. »

### Affirmation
AVANT : > « Le Passé n'a pas de prise sur moi car je suis prêt.e à apprendre et à changer. » — Louise Hay
APRÈS : > « Le passé n'a plus d'emprise sur moi, car je suis prêt·e à apprendre et à évoluer. » — Louise Hay

### `_ES.md` mots-clés
AVANT : « Innocence, liberté, originalité, aventure, voyage, folie, insouciance, idéalisme, jeunesse, spontanéité, manque d'engagement, nouveaux départs »
APRÈS : « Liberté, innocence, esprit libre, quête, périple, folie douce, légèreté, idéalisme, souffle de jeunesse, spontanéité, engagement faible, nouveaux départs »

## EXÉCUTION
- Utiliser l'outil `read` pour lire chaque fichier source.
- Utiliser l'outil `write` pour écrire la version paraphrasée au **même chemin** (dans `website_alt/cards/`).
- **Préserver exactement la structure du fichier** : mêmes lignes vides, mêmes titres, même ordre des sections et puces.
- **Ne PAS ajouter de nouveaux fichiers** — uniquement réécrire ceux existants.
- **Ne PAS modifier** les fichiers `_symbols.md`, `_symboles_pcd.md`, `_affirmations.md` (non utilisés par le site).
- Après traitement, vérifier qu'il ne reste plus aucune occurrence de « consultant » dans les fichiers traités.
