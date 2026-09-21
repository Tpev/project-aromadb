# Import de contacts newsletter par l’administration

Depuis **Administration → Praticiens → fiche du praticien → Importer des contacts newsletter**, un administrateur peut préparer une liste CSV, consulter son aperçu puis confirmer sa création dans une audience dédiée. Le praticien voit cette audience dans ses newsletters sur ordinateur et mobile ; il ne dispose pas de l’outil d’import.

## Parcours

1. Vérifier le nom, l’email et l’identifiant du praticien affichés en tête de page.
2. Nommer l’audience et sélectionner le CSV.
3. Cliquer sur **Analyser le fichier**. L’aperçu et son historique sont enregistrés, sans créer de contact ni envoyer d’email.
4. Contrôler les compteurs et le détail des lignes, puis cocher la confirmation et cliquer sur **Valider l’import**.
5. Le praticien peut sélectionner cette audience dans son formulaire habituel de newsletter.

Les imports restent limités au praticien de la fiche admin. Les quatre routes contrôlent les droits administrateur ; l’affichage et la validation vérifient aussi le propriétaire de l’import.

## Formats et règles

- CSV UTF-8, avec ou sans BOM, ou Windows-1252 ; séparateur virgule, point-virgule ou tabulation.
- Limites : 4 Mo et 10 000 lignes de contacts. Les lignes entièrement vides sont ignorées.
- Colonne `EMAIL` obligatoire et unique. Alias acceptés : `E_MAIL`, `EMAIL_ADDRESS`, `ADRESSE_EMAIL`.
- Colonnes facultatives : `PRENOM` / `FIRSTNAME` / `FIRST_NAME`, `NOM` / `LASTNAME` / `LAST_NAME`, `OPT_IN` et `DOUBLE_OPT-IN` / `DOUBLE_OPT_IN`.
- Adresses normalisées en minuscules, sans espaces aux extrémités. Adresses invalides, colonnes incohérentes ou noms de plus de 255 caractères : ligne exclue.
- Nouveau contact actif seulement si au moins un statut d’inscription est renseigné et si tous les statuts renseignés valent `YES`, `OUI`, `TRUE` ou `1`, sans distinction de casse. Les autres nouveaux contacts restent **À vérifier**, exclus des envois.
- Un doublon dans le fichier est ignoré. Des statuts contradictoires entre doublons ne peuvent pas activer le premier contact.
- Un désabonnement déjà enregistré pour ce praticien bloque l’ajout à l’audience. Il est vérifié à nouveau lors de la validation et lors de l’envoi.
- Un contact newsletter déjà enregistré conserve son nom et son statut, y compris son statut « À vérifier ». Cette version ne propose pas d’activation manuelle des contacts à vérifier.

Les contacts newsletter sont distincts des fiches clients. L’import ne crée aucun dossier client et n’envoie aucun message, y compris de confirmation d’inscription. Les statuts du CSV ne constituent pas une vérification externe de l’adresse ou de son historique.

## Réimport et envoi

Le même contenu de fichier, pour le même praticien, ouvre l’import existant. Une seconde validation ne recrée ni audience ni contact. Un autre fichier crée une autre audience mais réutilise les contacts déjà connus de ce praticien. Si une audience a ensuite été supprimée, le réimport strictement identique conserve cet historique sans recréer l’audience.

Les contacts importés apparaissent en lecture seule dans les audiences du praticien. La modification des clients sélectionnés conserve les contacts importés. Le choix **Tous mes clients avec email** garde son périmètre habituel ; pour envoyer à la liste importée, il faut sélectionner son audience.

Un envoi déduplique les adresses entre fiches clients et contacts importés. Il exclut les contacts importés inactifs, les adresses invalides et les désabonnements du praticien. Les quotas existants sont conservés. Les compteurs d’audience comptent les contacts enregistrés ; le volume réellement envoyé dépend des adresses uniques et éligibles.

Le lien de désabonnement fonctionne sans connexion et bloque les futurs envois de ce praticien, même après un réimport. L’export de compte comprend les contacts, l’historique des imports et les appartenances aux audiences, avec contrôle du propriétaire de chaque relation.

## Données et déploiement

Migration additive : `2026_09_21_150000_add_admin_newsletter_imports.php`. Elle ajoute les tables d’imports, de contacts et d’appartenances aux audiences, ainsi qu’une référence nullable dans les destinataires de newsletters. Elle ne réécrit pas les clients, audiences ou historiques d’envoi existants.

Appliquer les migrations avant la remise en service du nouveau code et compiler les assets avec le processus de déploiement habituel :

```sh
php artisan migrate --force
npm run build
php artisan view:clear
php artisan queue:restart
```

L’historique conserve les lignes analysées, les résultats, le nom et l’empreinte du fichier, l’administrateur ayant préparé l’import et la date de validation. Le CSV original n’est pas publié ni copié dans le dépôt. Un rollback de cette migration supprime les nouvelles données d’import ; il conserve les anciennes fiches clients, audiences et newsletters.

## Vérifications

Les tests utilisent SQLite en mémoire, des comptes fictifs et des emails simulés. Ils couvrent les droits, l’isolation des praticiens, les formats CSV, les consentements manquants, les doublons, les réimports, les quotas, les désabonnements, l’échappement HTML, les formulaires d’audience et la conservation des données historiques pendant le rollback et la remise à niveau du schéma.

Les contrôles navigateur utilisent les vues réelles avec des données fictives, à 1440 et 390 pixels de largeur. Le fichier fourni a été analysé en lecture seule : 100 lignes, dont 96 avec opt-in, 3 à vérifier et 1 invalide. Aucun import réel ni envoi d’email n’a été effectué.

Résultats du 21 septembre 2026 : **807 tests réussis, 4 892 assertions**, avec un test préexistant ignoré. Les 12 contrôles de pages dans le navigateur passent, ainsi que la compilation de production, la syntaxe PHP et `git diff --check`. Les migrations ont été vérifiées sur SQLite isolé ; aucune migration n’a été exécutée sur la base de production.
