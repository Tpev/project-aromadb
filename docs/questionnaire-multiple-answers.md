# Plusieurs réponses par question

Dans les formulaires de création et de modification, sur ordinateur et mobile, chaque question de type « Choix multiple » dispose d’une case facultative « Autoriser plusieurs réponses ».

- Case décochée : une seule réponse, avec le menu déroulant habituel.
- Case cochée : cases à cocher côté client ; au moins une réponse est nécessaire.
- Le réglage est indépendant pour chaque question. Il est désactivé pour les questions de type texte.

## Compatibilité

La migration ajoute uniquement `questions.allow_multiple`, booléen nullable avec une valeur par défaut `false`. Les valeurs absentes ou `null` se comportent comme un choix unique. Les questions et les réponses existantes ne sont pas réécrites.

Les anciens formulaires d’édition qui n’envoient pas ce champ conservent le réglage enregistré. Un champ explicitement nul ou décoché le désactive. Un formulaire client à choix unique ouvert avant l’activation peut toujours soumettre sa réponse.

L’affichage des réponses et l’email acceptent les anciennes réponses simples, les anciens JSON encodés en chaîne, les tableaux de réponses et les valeurs vides. Les réponses historiques restent lisibles après un changement de réglage. Les valeurs affichées sont échappées.

Le serveur contrôle les questions du questionnaire, les options proposées, l’absence de doublons et la présence d’au moins une réponse. Les sélections sont conservées lors d’une erreur de validation.

## Déploiement

Appliquer la migration additive `2026_09_21_120000_add_allow_multiple_to_questions_table.php` avant d’activer le nouveau code, puis redémarrer les workers pour utiliser le nouveau rendu des emails :

```sh
php artisan migrate --force
php artisan queue:restart
```

Les tests de compatibilité et de migration utilisent une base SQLite isolée en mémoire. Les contrôles navigateur utilisent les vues de l’application rendues avec des données de test, sans modifier les questionnaires réels.

## Vérification du 21 septembre 2026

- Suite complète : 772 tests réussis, 4 652 assertions et un test ignoré pour une limitation SQLite préexistante concernant les achats de formations.
- Parcours questionnaires et automatisation : 53 tests réussis, dont 37 nouveaux tests couvrant les réglages facultatifs, les valeurs nulles, les anciennes réponses et la validation.
- Navigateur : création, modification et réponse contrôlées sur ordinateur et mobile, y compris l’ajout dynamique de questions, la transmission du réglage coché/décoché et la sélection de deux réponses. Aucune erreur JavaScript ni débordement horizontal.
- Compilation de production et syntaxe PHP : réussies.
