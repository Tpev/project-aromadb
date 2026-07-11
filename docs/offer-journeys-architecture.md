# Parcours d'offre - architecture et exploitation

## Objectif

Le module `OfferJourneys` permet a un praticien Premium pilote de creer un parcours public relie aux fonctions Olithea existantes. Il ne remplace ni l'agenda, ni les rendez-vous, ni les evenements, ni les formations, ni les bons cadeaux, ni Stripe. Ces domaines restent les sources de verite.

Le module est livre eteint. Une migration de schema ne rend aucun ecran ni aucune URL publique accessible.

## Garde-fous de lancement

Les controles sont cumulatifs:

- `OFFER_JOURNEYS_ENABLED`: interface praticien;
- `OFFER_JOURNEYS_ALLOW_ALL`: ouverture explicite a tous les Premium eligibles;
- `OFFER_JOURNEYS_BETA_USER_IDS`: allowlist pilote quand `ALLOW_ALL=false`;
- `OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED`: rendu public;
- `OFFER_JOURNEYS_TRACKING_ENABLED`: mesure first-party;
- `OFFER_JOURNEYS_AUTOMATION_ENABLED`: execution des sequences;
- `OFFER_JOURNEYS_EMAIL_ENABLED`: transport email du module;
- `OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS`: arret marketing global, actif par defaut.

Ordre recommande: migrations, interface allowlistee, pages publiques, tracking, automatisation, email transactionnel, puis marketing. Chaque etape peut etre arretee sans modifier les autres fonctions Olithea.

## Bounded context

Le code metier vit dans `app/Domain/OfferJourneys`. Les tables sont prefixees `offer_journey_`. Les routes sont dans `routes/offer-journeys.php`. Les vues sont dans `resources/views/offer-journeys`.

Points d'integration limites:

- licence Premium via `config/license_features.php`;
- navigation desktop/mobile conditionnee par `OfferJourneyAccess`;
- profil public: lecture facultative d'un parcours publie uniquement lorsque le module public est actif;
- rendez-vous: parametre optionnel `product_id`, verifie comme appartenant au praticien et reservable;
- conversions: observers passifs sur rendez-vous, reservations, inscriptions formation et commandes de bons;
- newsletter: synchronisation de la desinscription avec `NewsletterOptOut`;
- scheduler: dispatch des executions dues et reconciliation quotidienne.

Aucune route historique n'est renommee. Aucun schema historique n'est modifie. Une panne d'analytics ou d'attribution est journalisee et n'annule jamais l'action metier d'origine.

## Entites

- `OfferJourney`: brouillon, statut, objectif et adaptateur source;
- `OfferJourneyPage`: contenu editable d'une etape;
- `OfferJourneyVersion` et `OfferJourneyPageVersion`: snapshots immuables publies;
- `OfferJourneyTransition`: graphe dirige, sans boucle et borne;
- `OfferJourneyForm` et `OfferJourneyFormField`: formulaire standard minimise;
- `OfferJourneyContact`, `Entry`, `Consent`, `Suppression`: identite marketing distincte du dossier client;
- `CampaignLink`, `Event`, `Conversion`: attribution et mesure;
- `Automation`, `AutomationVersion`, `AutomationNode`, `AutomationRun`: execution versionnee;
- `MessageDelivery`, `AutomationAction`: idempotence et diagnostic;
- `PipelineStage`, `Tag`, `Segment`, `Task`, `ContactActivity`: suivi relationnel.

La suppression/anonymisation d'un contact marketing ne supprime jamais un client, un rendez-vous, une facture ou une piece comptable.

## Inventaire des surfaces

Praticien:

- `/dashboard-pro/parcours-offres` liste et indicateurs;
- `/create` assistant a six objectifs et modeles multi-etapes;
- `/{journey}` vue d'ensemble et carte ordonnee;
- `/{journey}/pages/{page}/edit` contenu, formulaire, SEO et transition;
- `/{journey}/suivis` messages, graphe borne, simulation et controles;
- `/{journey}/resultats` vues, contacts, conversions, revenu et abandons;
- `/{journey}/partage` URL, QR et liens de campagne;
- `/utilisation` parcours, contacts, email et stockage prive;
- `/dashboard-pro/contacts-interesses` liste, fiche, pipeline et segments.

Public:

- `/pro/{therapist}/offres/{journey}/{page?}`;
- capture publique limitee a 10 requetes/minute;
- liens de ressource temporaires signes;
- apercu brouillon temporaire signe, `noindex`, formulaire desactive;
- desinscription signee.

## Modeles livres

- seance: page d'offre vers etape de reservation Olithea;
- atelier: page d'offre vers inscription a l'evenement existant;
- ressource gratuite: opt-in vers contenu/fichier prive;
- formation: page d'offre vers formation digitale existante;
- bon cadeau: page d'offre vers checkout existant;
- demande qualifiee: formulaire vers confirmation.

Toutes les pages sont creees en brouillon. Une publication cree un snapshot; modifier le brouillon ne modifie pas la version publique. Les anciens slugs de parcours et de page redirigent en 301 apres republication.

## Fichiers prives

Les fichiers sont stockes sur le disque Laravel `local` sous `private/offer-journeys/{user_id}`. Le chemin n'est jamais rendu dans le HTML. Un telechargement exige une URL signee temporaire et une version encore rattachee a un parcours publie et autorise. Remplacer un fichier de brouillon ne supprime pas le fichier d'une ancienne version publiee.

## Automatisation

Le moteur accepte au maximum 20 noeuds, uniquement vers l'avant:

- email;
- attente;
- condition de consentement, conversion, tag ou inactivite;
- action d'ajout de tag, statut ou tache;
- fin.

Chaque envoi et action possede une cle d'idempotence. Les gardes sont reevaluees juste avant execution: flags, licence, statut du parcours, source disponible, conversion, statut contact, consentement, suppression, quota et heures silencieuses. Une conversion, une source supprimee ou un contact inactif arrete la sequence.

La simulation traverse le graphe en memoire. Elle ne cree aucune execution, livraison, action, tache ou consommation de quota.

## Attribution et reprise

Le contexte d'attribution est chiffre et borne dans le temps. Les paiements ou confirmations asynchrones peuvent retrouver l'entree sans cookie navigateur. Une conversion confirmee arrete les relances. Les annulations et remboursements mettent a jour la conversion sans supprimer l'historique.

La commande suivante repasse les enregistrements recents dans l'attribution idempotente:

```bash
php artisan offer-journeys:reconcile-conversions --days=35 --dry-run
php artisan offer-journeys:reconcile-conversions --days=35
```

## Queue et scheduler

Le worker existant traite `ProcessOfferJourneyAutomationRun`. Le scheduler lance:

```text
offer-journeys:dispatch-due                 chaque minute
offer-journeys:reconcile-conversions        chaque jour a 04:20
```

Les commandes sont sans effet lorsque le module est desactive. Avant d'activer l'email en production, verifier le worker, `schedule:run`, l'expediteur, Reply-To, SPF/DKIM/DMARC et la boite de retour.

## Compatibilite et rollback

Rollback fonctionnel immediat:

1. `OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=true`;
2. couper `EMAIL_ENABLED` puis `AUTOMATION_ENABLED`;
3. couper `PUBLIC_PAGES_ENABLED` si les pages ne doivent plus repondre;
4. couper `OFFER_JOURNEYS_ENABLED` pour masquer l'interface.

Les rendez-vous, paiements, profils et autres domaines continuent. Ne rollbacker les migrations qu'apres export/verification des donnees du module; les kill switches suffisent pour un incident applicatif.

## Couverture V1.0, V1.5 et V2

V1.0:

- six objectifs, assistant, editeur guide, page publique responsive;
- formulaire minimise, deduplication, consentement et desinscription;
- trois suivis, statistiques, partage/QR, publication/pause;
- adaptateurs vers toutes les sources demandees;
- limites et diagnostic d'utilisation.

V1.5:

- pages multiples, transitions conditionnelles et fallback;
- carte ordonnee, snapshots par page et redirects stables;
- modeles multi-etapes, analytics d'abandon par etape;
- blocs guides bornes (presentation, audience, resultats, etapes, pratique, FAQ, formulaire);
- SEO, canonical et Open Graph.

V2:

- moteur declencheur-condition-action borne et simulation;
- tags, segments, pipeline, taches et activite;
- reentree, sortie sur conversion/source/contact et heures silencieuses;
- liens de campagne, rapports source/campagne/revenu;
- reconciliation idempotente.

V2.5 et V3 restent explicitement hors livraison: A/B test, SMS, domaine personnalise, affiliation, API/webhooks publics, upsell, import Systeme.io et edition HTML libre.

## Extensions P0/P1 ajoutees en juillet 2026

### Delivrabilite et support

- `offer_journey_deliverability_events`: evenements SES normalises et idempotents;
- `offer_journey_sender_controls`: pauses globales ou marketing par praticien;
- `offer_journey_support_audits`: journal append-only des actions support;
- webhook SNS signe, ARN allowliste et traitement asynchrone;
- diagnostic DNS SPF/DKIM/DMARC en lecture seule.

### Creation et formulaires

- `offer_journey_reusable_sections`: blocs reutilisables appartenant au praticien;
- `offer_journey_form_answers`: reponses aux trois questions personnalisees maximum;
- blocs visuels bornes et ordre conserve dans le snapshot publie;
- assistant de redaction deterministe, sans publication automatique.

### Campagnes et abandon

- `offer_journey_message_campaigns` et table pivot: campagne programmee reutilisable sur plusieurs parcours;
- `offer_journey_abandonment_candidates`: une seule relance possible par objet metier commence;
- toutes les sorties utilisent `offer_journey_message_deliveries`, les suppressions communes et le meme retour SES;
- consentement, conversion, annulation, desinscription, frequence et reputation sont reverifies immediatement avant envoi.

### Suivi commercial et import

- `offer_journey_saved_filters`: filtres appartenant au praticien;
- `offer_journey_pipeline_goals`: objectifs mensuels globaux ou par parcours;
- `offer_journey_contact_imports`: apercu, rapport, preuve de consentement et liste des fiches creees;
- la fusion porte uniquement sur les fiches marketing et conserve le lien eventuel vers le dossier client;
- les rendez-vous, achats et inscriptions sont affiches en lecture seule sans notes cliniques dans l'interface.

## Risques residuels avant pilote

- faire valider les textes de consentement et de confidentialite par la personne responsable;
- fournir de vrais seuils commerciaux de quota et stockage;
- verifier la delivrabilite sur le domaine de production;
- definir le contact support et la procedure de moderation;
- mesurer la charge avec un volume superieur au pilote;
- ne pas activer `ALLOW_ALL` avant retour des premiers praticiens allowlistes.

Voir aussi `docs/offer-journeys-rollout.md` pour la checklist de deploiement.
