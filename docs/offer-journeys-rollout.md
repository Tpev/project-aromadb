# Parcours d'offre - déploiement pilote

Ce module est isolé derrière des feature flags et une liste explicite de praticiens pilotes. Les tables sont additives et aucun rendez-vous, paiement, événement, formation, bon cadeau, dossier client ou email existant n'est remplacé.

## État par défaut

Toutes les fonctions restent invisibles et inactives après déploiement:

```env
OFFER_JOURNEYS_ENABLED=false
OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED=false
OFFER_JOURNEYS_AUTOMATION_ENABLED=false
OFFER_JOURNEYS_EMAIL_ENABLED=false
OFFER_JOURNEYS_TRACKING_ENABLED=false
OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=true
OFFER_JOURNEYS_ALLOW_ALL=false
OFFER_JOURNEYS_BETA_USER_IDS=
OFFER_JOURNEYS_SES_EVENTS_ENABLED=false
OFFER_JOURNEYS_SUPPORT_CONSOLE_ENABLED=false
OFFER_JOURNEYS_PUBLICATION_ASSISTANCE_ENABLED=false
OFFER_JOURNEYS_TEMPLATE_LIBRARY_ENABLED=false
OFFER_JOURNEYS_RICH_EDITOR_ENABLED=false
OFFER_JOURNEYS_WRITING_ASSISTANT_ENABLED=false
OFFER_JOURNEYS_CUSTOM_FORMS_ENABLED=false
OFFER_JOURNEYS_MESSAGE_TOOLS_ENABLED=false
OFFER_JOURNEYS_CAMPAIGNS_ENABLED=false
OFFER_JOURNEYS_ABANDONMENT_REMINDERS_ENABLED=false
OFFER_JOURNEYS_COMMERCIAL_TOOLS_ENABLED=false
OFFER_JOURNEYS_CONTACT_IMPORT_ENABLED=false
OFFER_JOURNEYS_RETENTION_ENABLED=false
```

Une liste pilote vide n'autorise personne. `OFFER_JOURNEYS_ALLOW_ALL` doit rester à `false` pendant tout le pilote.

## Déploiement sans activation

1. Sauvegarder la base de production et vérifier la restauration.
2. Déployer le code avec tous les flags ci-dessus.
3. Exécuter les migrations additives.
4. Recréer les caches Laravel.
5. Redémarrer proprement les workers de queue.
6. Vérifier les parcours existants: connexion, agenda, réservation, Stripe, événements, formations, bons cadeaux, Google et visio.

Commandes usuelles à adapter au serveur:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Le scheduler existant doit continuer à lancer `php artisan schedule:run` chaque minute. La commande `offer-journeys:dispatch-due` ne fait rien tant que le module ou les automatisations sont coupés.

## Activation progressive

Utiliser un identifiant utilisateur réel dans `OFFER_JOURNEYS_BETA_USER_IDS`.

### 1. Back-office uniquement

```env
OFFER_JOURNEYS_ENABLED=true
OFFER_JOURNEYS_BETA_USER_IDS=123
OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED=false
OFFER_JOURNEYS_AUTOMATION_ENABLED=false
OFFER_JOURNEYS_EMAIL_ENABLED=false
OFFER_JOURNEYS_TRACKING_ENABLED=false
```

Le pilote peut créer, prévisualiser et configurer un brouillon. Aucune page publique et aucun envoi ne sont possibles.

### 2. Page publique et mesure

```env
OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED=true
OFFER_JOURNEYS_TRACKING_ENABLED=true
```

Tester l'URL publique, le formulaire, la déduplication, le consentement, la désinscription, les UTM, le QR code et les destinations existantes.

### 3. Automatisations sans email

```env
OFFER_JOURNEYS_AUTOMATION_ENABLED=true
OFFER_JOURNEYS_EMAIL_ENABLED=false
```

Vérifier les runs, délais, sorties sur conversion, tâches et actions idempotentes. Aucun email ne part.

### 4. Réponse demandée uniquement

```env
OFFER_JOURNEYS_EMAIL_ENABLED=true
OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=true
```

La livraison ou confirmation demandée peut partir. Les suivis marketing restent bloqués.

### 5. Suivis marketing du pilote

Après validation juridique des textes et contrôle de l'identité d'expéditeur:

```env
OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=false
```

Surveiller les envois, échecs, désinscriptions, plaintes, quota mensuel et conversions chaque jour du pilote.

Après chaque modification de `.env`:

```bash
php artisan config:cache
php artisan queue:restart
```

## Arrêt d'urgence

- Couper les suivis marketing: `OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=true`.
- Couper tous les emails du module: `OFFER_JOURNEYS_EMAIL_ENABLED=false`.
- Couper les actions en attente: `OFFER_JOURNEYS_AUTOMATION_ENABLED=false`.
- Masquer les pages publiques: `OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED=false`.
- Masquer tout le module: `OFFER_JOURNEYS_ENABLED=false`.

Les runs en attente restent enregistrés pour diagnostic. Aucun kill switch ne supprime les données ni les objets métier existants.

## Préparation SES avant tout email marketing

Capture de référence de la console interne: `docs/offer-journeys-guide-assets/23-console-support-delivrabilite.png`.

1. Dans Amazon SES, créer ou réutiliser un **Configuration Set** réservé aux événements Olithea.
2. Créer un topic SNS dans la même région pour les événements de livraison, rejet, bounce et plainte.
3. Abonner l’URL HTTPS `https://olithea.fr/api/webhooks/offer-journeys/ses` au topic.
4. Renseigner exactement l’ARN du topic dans `OFFER_JOURNEYS_SES_SNS_TOPIC_ARNS`. Plusieurs ARN sont séparés par des virgules.
5. Laisser `OFFER_JOURNEYS_SES_AUTO_CONFIRM_SUBSCRIPTION=false` par défaut. Confirmer manuellement l’abonnement SNS depuis la console AWS. L’activation automatique ne doit être utilisée que pendant une opération contrôlée.
6. Renseigner le nom du configuration set dans `OFFER_JOURNEYS_SES_CONFIGURATION_SET`.
7. Vérifier la réception d’un événement de livraison test dans la console support avant d’activer les suivis.
8. Provoquer uniquement sur une adresse de test maîtrisée les scénarios bounce/complaint fournis par SES, puis vérifier la suppression automatique.

```env
OFFER_JOURNEYS_SES_EVENTS_ENABLED=true
OFFER_JOURNEYS_SES_SNS_TOPIC_ARNS=arn:aws:sns:REGION:COMPTE:TOPIC
OFFER_JOURNEYS_SES_AUTO_CONFIRM_SUBSCRIPTION=false
OFFER_JOURNEYS_SES_CONFIGURATION_SET=nom-du-configuration-set
OFFER_JOURNEYS_EMAIL_DOMAIN=olithea.fr
OFFER_JOURNEYS_DKIM_SELECTORS=selecteur1,selecteur2,selecteur3
```

Ne jamais inventer les sélecteurs DKIM: recopier ceux fournis par SES. Le diagnostic SPF/DKIM/DMARC est en lecture seule; il ne modifie aucun enregistrement DNS.

## Ordre d’activation des extensions P0/P1

Activer chaque ligne pour un seul praticien pilote, contrôler l’écran concerné et attendre au moins un cycle d’usage réel avant la suivante:

1. `OFFER_JOURNEYS_PUBLICATION_ASSISTANCE_ENABLED=true`;
2. modèles, éditeur, assistant et formulaires;
3. `OFFER_JOURNEYS_MESSAGE_TOOLS_ENABLED=true`, avec emails réels toujours coupés;
4. `OFFER_JOURNEYS_COMMERCIAL_TOOLS_ENABLED=true`;
5. `OFFER_JOURNEYS_CONTACT_IMPORT_ENABLED=true`, sur un petit fichier de test;
6. `OFFER_JOURNEYS_CAMPAIGNS_ENABLED=true`, sans campagne planifiée;
7. `OFFER_JOURNEYS_ABANDONMENT_REMINDERS_ENABLED=true`, après validation juridique du texte et du délai;
8. `OFFER_JOURNEYS_RETENTION_ENABLED=true`, d’abord avec `--dry-run` et rapport vérifié;
9. seulement ensuite, activer les emails marketing.

Après chaque modification de `.env`:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan queue:restart
```

Vérifier aussi que le cron exécute `php artisan schedule:run` chaque minute. Les campagnes sont évaluées chaque minute et les abandons toutes les cinq minutes.

## Contrôles avant pilote

- Valider les textes de consentement et la base légale avec le conseil RGPD.
- Confirmer le nom d'expéditeur, le Reply-To et la délivrabilité SES.
- Brancher les événements de bounce et plainte SES sur la suppression commune avant une ouverture large.
- Choisir les praticiens pilotes et les limites de support.
- Fournir des exemples d'offres et ressources réelles.
- Vérifier le volume mensuel de 2 000 emails marketing par praticien.
- Tester un parcours complet par destination: rendez-vous, événement, formation et bon cadeau.
- Vérifier les chiffres d'attribution sur une réservation ou vente confirmée côté serveur.

## Retour arrière

Mettre tous les flags à `false`, reconstruire le cache de configuration et redémarrer les workers. Laisser les tables du module en place pendant l'analyse; leur suppression n'est pas nécessaire pour restaurer le comportement antérieur.
