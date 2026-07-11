# Specification produit - Parcours d'offre Premium

Statut: spécification produit approfondie - prête pour découpage technique  
Date: 2026-07-10  
Produit: Olithea  
Nom interne possible: tunnels de vente  
Nom utilisateur recommande: Parcours d'offre

Guide de lecture:

- sections 1 à 25: cadrage produit et MVP initial;
- sections 26 à 51: spécification approfondie et cible concurrentielle;
- en cas de différence sur l'architecture, le modèle de données, les pages ou la roadmap, les sections 26 à 51 font référence.

## 1. Résumé

Olithea dispose déjà de nombreuses briques qui permettent aux praticiens de transformer un visiteur en client: portail public, prestations, réservation en ligne, événements, bons cadeaux, formations digitales, newsletters, questionnaires, paiement en ligne, avis et dossiers clients.

La fonctionnalité "Parcours d'offre" doit assembler ces briques en un parcours guidé et mesurable:

1. Le praticien choisit une offre ou un objectif.
2. Olithea génère une page publique dédiée.
3. Le visiteur lit, s'inscrit, réserve, achète, télécharge ou demande un contact.
4. Olithea enregistre le prospect ou le client.
5. Olithea peut envoyer une suite d'emails simples.
6. Le praticien suit les vues, clics, prospects, réservations et ventes.

L'objectif est de rendre inutile un outil externe comme Systeme.io pour la grande majorité des besoins d'un praticien, d'un coach ou d'un accompagnant. Olithea ne cherchera pas la parité horizontale avec tous les cas d'usage du commerce en ligne: elle cherchera une supériorité verticale sur les parcours qui mènent à une prise de rendez-vous, une inscription, une vente de contenu, une demande qualifiée ou un suivi d'accompagnement.

Le principe produit est donc double:

1. rendre la V1 beaucoup plus simple qu'un outil marketing généraliste;
2. concevoir dès le départ un socle capable d'évoluer vers des parcours multi-étapes, des automatisations, un suivi de prospects et des offres complémentaires sans réécriture destructive.

## 2. Positionnement commercial

### Message court

Créez une page claire pour chaque offre, partagez un lien, captez les personnes intéressées, puis transformez-les en rendez-vous, inscriptions ou ventes grâce à des emails automatiques simples.

### Message dans le pack Premium

Avec le Premium, créez des parcours d'offre complets: une page dédiée, un formulaire ou une action de réservation, des emails de suivi et des statistiques pour comprendre ce qui fonctionne.

### Vocabulaire recommandé

- Interface produit: "Parcours d'offre".
- Page commerciale: "Pages d'offres et parcours clients".
- Support / interne: "tunnels de vente".
- Eviter dans l'interface: "funnel", "growth hacking", "upsell agressif", "automation marketing".

## 3. Référence marché

Les outils de tunnel de vente généralistes vendent principalement l'assemblage suivant:

- pages de vente;
- capture de prospects;
- email marketing;
- automatisations;
- paiement;
- formations ou contenus digitaux;
- statistiques;
- parfois affiliation, communautés et domaines personnalisés.

Références officielles consultées le 2026-07-10:

- Systeme.io présente son produit comme une plateforme pour "sales funnels", email marketing, cours en ligne, vente en ligne et blogging: https://systeme.io/
- Sa page fonctionnalités met en avant les tunnels, campagnes email automatisées, cours, produits digitaux et physiques: https://systeme.io/features
- Sa page automatisation parle de déclencheurs, actions, délais, tags, séquences email et règles d'accès aux cours: https://systeme.io/marketing-automation
- Sa page tarifaire confirme une logique de plans principalement limitée par les contacts, tunnels et formations: https://systeme.io/pricing
- Sa présentation des parcours de formation relie lead magnet, contenu, page de vente, checkout, suivi et progression: https://systeme.io/glossary/course-funnel/

Opportunité pour Olithea: devenir la meilleure plateforme de parcours commerciaux pour les praticiens, sans devenir un outil marketing généraliste. Le praticien doit pouvoir attirer, rassurer, qualifier, faire réserver, vendre, délivrer et suivre depuis son environnement métier.

Le benchmark actuel de Systeme.io montre notamment: constructeur de tunnels, campagnes email, automatisations à base de déclencheurs/conditions/actions, cours, réservation, paiements, CRM en pipeline, communautés, webinaires, affiliation, domaines personnalisés, A/B tests, offres complémentaires et ventes additionnelles. La réponse Olithea doit être explicite pour chacune de ces capacités: parité, adaptation métier, report à une phase ultérieure ou exclusion assumée.

## 4. Inventaire des briques existantes dans Olithea

Cette spécification s'appuie sur l'existant observé dans le dépôt.

| Brique existante | Rôle dans un parcours d'offre | Exemples de routes / fichiers |
|---|---|---|
| Portail public praticien | Point d'entrée de base et crédibilité | `therapist.show`, `/pro/{slug}`, `PublicTherapistController` |
| Prestations | Offre réservable, durée, prix, paiement, questionnaire | `Product`, `products.*`, `appointments.createPatient`, `appointments.storePatient` |
| Réservation | Conversion vers rendez-vous | `/book-appointment/{therapist}`, `AppointmentController` |
| Evénements / ateliers | Conversion vers inscription, avec paiement possible | `events.public.show`, `events.reserve.create`, `events.reserve.store` |
| Bons cadeaux | Vente d'une offre cadeau | `pro.gift-vouchers.*`, `gift-vouchers.checkout.*` |
| Formations digitales | Lead magnet, contenu gratuit, produit payant | `digital-trainings.public.show`, `DigitalTraining`, `DigitalTrainingEnrollment` |
| Newsletters | Relance et communication | `Newsletter`, `NewsletterRecipient`, `Audience`, `newsletters.*` |
| Questionnaires | Qualification avant séance ou capture structurée | `questionnaires.fill`, `Questionnaire` |
| Paiement Stripe | Paiement de facture, rendez-vous, événement ou checkout | `StripeController`, `create-payment-link`, public checkout |
| Articles / blog | Contenu d'acquisition | `dashboardpro.articles.*`, `pro.articles.*` |
| Avis clients | Preuve sociale | témoignages / demandes d'avis existantes |
| Dossiers clients | Transformation du prospect en client suivi | `ClientProfile` |
| CRM admin | Inspiration pour pipeline, suivi et statuts | `CrmLead`, `CrmLeadActivity` côté admin |

Conclusion: la V1 doit surtout créer une couche d'assemblage et de mesure au-dessus de l'existant.

## 5. Utilisateurs concernés

### Praticien Premium

Il veut:

- vendre ou présenter une offre sans créer un site externe;
- partager un lien propre sur Instagram, Google Business, newsletter, QR code ou carte de visite;
- récupérer les coordonnées des personnes intéressées;
- relancer sans tout faire manuellement;
- savoir si son lien fonctionne;
- transformer en rendez-vous, inscription, vente ou accès à un contenu.

### Visiteur public

Il veut:

- comprendre rapidement à qui s'adresse l'offre;
- savoir ce qu'il va obtenir;
- connaître le prix ou les conditions;
- être rassuré sur le praticien;
- agir sans friction: réserver, payer, s'inscrire, télécharger ou demander un appel.

### Client existant

Il peut recevoir:

- un lien vers une offre complémentaire;
- une proposition de pack;
- une formation ou ressource;
- une invitation à un atelier;
- un bon cadeau à acheter ou offrir.

### Equipe Olithea

Elle veut:

- renforcer la valeur du Premium;
- éviter que les praticiens utilisent Systeme.io ou Mailchimp à côté;
- garder les parcours dans un environnement cohérent;
- suivre l'adoption et les conversions de la fonctionnalité.

## 6. Objectifs produit

1. Augmenter la valeur perçue du pack Premium.
2. Aider les praticiens à transformer leur visibilité en réservations ou ventes.
3. Réduire la dépendance à des outils externes de tunnel de vente.
4. Utiliser les briques existantes au lieu de créer un second CMS complet.
5. Rendre la création d'un parcours suffisamment simple pour un praticien non-marketeur.
6. Mesurer les résultats: vues, clics, prospects, réservations, ventes.

## 7. Non-objectifs V1

La V1 ne doit pas essayer de reproduire tout Systeme.io.

Hors périmètre V1:

- constructeur visuel de workflow libre;
- A/B testing avancé;
- domaines personnalisés par praticien;
- affiliation;
- upsell/downsell en un clic;
- panier multi-produits complet;
- segmentation comportementale avancée;
- éditeur page builder libre façon Webflow;
- scoring IA des prospects;
- automatisations multi-branches complexes.

## 8. Types de parcours à proposer

### 8.1 Parcours "Réserver une séance"

Objectif: transformer un visiteur en rendez-vous.

Source:

- une prestation existante (`Product`);
- ou une prestation créée depuis le parcours.

CTA principal:

- "Réserver cette séance";
- "Choisir un créneau";
- "Demander un appel découverte".

Actions possibles:

- réserver directement via le flux de rendez-vous;
- payer en ligne si la prestation collecte le paiement;
- remplir un questionnaire avant le rendez-vous;
- créer ou rattacher un dossier client.

Exemple:

Une sophrologue crée une page "Séance découverte sommeil". La page explique le problème, la méthode, la durée et le prix. Le visiteur réserve un créneau. Olithea envoie la confirmation, le rappel et le questionnaire pré-séance.

### 8.2 Parcours "Inscription atelier"

Objectif: remplir un atelier, conférence ou événement.

Source:

- un événement existant (`Event`);
- ou un événement créé depuis le parcours.

CTA principal:

- "S'inscrire à l'atelier";
- "Réserver ma place".

Actions possibles:

- inscription gratuite;
- inscription payante;
- liste de participants;
- email de rappel;
- conversion en client après l'atelier.

### 8.3 Parcours "Lead magnet"

Objectif: capter un prospect en échange d'un contenu gratuit.

Source:

- formation digitale gratuite;
- guide PDF;
- vidéo;
- questionnaire diagnostic;
- article enrichi;
- contenu de conseil.

CTA principal:

- "Recevoir le guide";
- "Faire le diagnostic";
- "Accéder à la vidéo".

Actions possibles:

- formulaire email + prénom;
- consentement newsletter;
- création d'un contact prospect;
- envoi automatique du contenu;
- séquence de relance courte vers une réservation.

Exemple:

Une naturopathe partage "Checklist: préparer son premier bilan naturopathique". Le visiteur laisse son email. J0: il reçoit la checklist. J+2: il reçoit un conseil. J+5: il reçoit une invitation à réserver un bilan.

### 8.4 Parcours "Vendre une formation digitale"

Objectif: vendre ou distribuer un contenu en ligne.

Source:

- `DigitalTraining`.

CTA principal:

- "Accéder à la formation";
- "Commencer le programme";
- "Acheter l'accès".

Actions possibles:

- accès gratuit avec identité;
- accès libre;
- paiement;
- envoi du lien magique;
- progression dans la formation.

### 8.5 Parcours "Bon cadeau"

Objectif: vendre une offre cadeau.

Source:

- `GiftVoucher`.

CTA principal:

- "Offrir cette séance";
- "Acheter un bon cadeau".

Actions possibles:

- checkout public;
- génération PDF;
- email au bénéficiaire;
- suivi des utilisations.

### 8.6 Parcours "Demande de contact qualifiée"

Objectif: recevoir des demandes mieux qualifiées.

Source:

- formulaire court;
- questionnaire;
- offre sans réservation directe.

CTA principal:

- "Demander un premier échange";
- "Vérifier si cet accompagnement est adapté".

Actions possibles:

- collecte du besoin;
- consentement;
- création d'un prospect;
- notification au praticien;
- proposition de créneaux.

## 9. Expérience praticien

### 9.1 Entrée dans le produit

Emplacement recommandé:

- navigation principale: "Marketing" ou "Développement";
- ou dans Configuration: "Parcours d'offre";
- accès secondaire depuis les écrans existants: prestation, événement, formation, bon cadeau.

Gating:

- visible pour tous avec écran d'explication;
- création et publication réservées au Premium;
- Pro peut avoir un aperçu ou un nombre limité de parcours simples si souhaité.

Nom de permission proposé:

- `sales_funnels`

Ajout possible dans `config/license_features.php`:

- `premium`: `sales_funnels`
- `trial`: `sales_funnels`

### 9.2 Liste des parcours

Vue praticien:

- titre du parcours;
- type: séance, atelier, lead magnet, formation, bon cadeau, contact;
- statut: brouillon, publié, archivé;
- lien public;
- vues;
- prospects;
- conversions;
- revenu attribué si applicable;
- dernière activité;
- bouton "Partager".

Actions:

- créer;
- dupliquer;
- modifier;
- prévisualiser;
- publier / dépublier;
- archiver;
- voir les statistiques;
- copier le lien;
- générer QR code.

### 9.3 Assistant de création

L'assistant doit être plus guidé qu'un page builder.

Etape 1: choisir l'objectif

- Réserver une séance.
- Remplir un atelier.
- Offrir un contenu gratuit.
- Vendre une formation.
- Vendre un bon cadeau.
- Recevoir une demande qualifiée.

Etape 2: choisir la source

- sélectionner une prestation existante;
- sélectionner un événement;
- sélectionner une formation;
- sélectionner un bon cadeau;
- créer une nouvelle offre simple;
- partir d'un modèle.

Etape 3: construire la page

Champs:

- titre public;
- promesse courte;
- problème traité;
- pour qui;
- ce que la personne va obtenir;
- déroulé;
- prix ou conditions;
- durée;
- modalités: cabinet, visio, domicile, en ligne;
- image;
- témoignages;
- FAQ;
- CTA;
- message de réassurance;
- mentions légales / confidentialité.

Etape 4: action après inscription

Selon le type:

- rediriger vers réservation;
- déclencher paiement;
- envoyer contenu;
- donner accès formation;
- afficher page de confirmation;
- proposer un questionnaire;
- créer un prospect;
- créer un dossier client si conversion.

Etape 5: emails automatiques

V1: séquence simple, non visuelle:

- email immédiat;
- email J+2;
- email J+5;
- email de rappel avant événement;
- email post-événement ou post-contenu.

Chaque email peut être:

- désactivé;
- édité depuis un modèle;
- envoyé uniquement si la personne n'a pas réservé/acheté;
- conforme désinscription si email marketing.

Etape 6: publier et partager

Sorties:

- lien public;
- QR code;
- bouton "Ajouter à mon portail";
- proposition de texte Instagram;
- proposition de texte Google Business;
- proposition de texte newsletter.

## 10. Expérience visiteur public

### 10.1 Page publique

Structure recommandée:

1. En-tête praticien: nom, photo/logo, spécialité, ville ou format.
2. Titre de l'offre.
3. Promesse claire.
4. CTA visible au-dessus de la ligne de flottaison.
5. Section "Pour qui".
6. Section "Ce que vous allez obtenir".
7. Déroulé / contenu.
8. Prix ou conditions.
9. Témoignages ou avis.
10. FAQ.
11. CTA final.
12. Mentions: confidentialité, désinscription, informations praticien.

La page doit être simple, rapide, mobile-first et cohérente avec Olithea.

### 10.2 Actions possibles

Le visiteur peut:

- réserver un rendez-vous;
- s'inscrire à un événement;
- payer;
- acheter un bon cadeau;
- accéder à une formation;
- recevoir un contenu gratuit;
- remplir un questionnaire;
- demander un appel;
- partager le lien.

### 10.3 Page de confirmation

Après action:

- message de confirmation clair;
- rappel de ce qui va se passer;
- bouton ajouter au calendrier si rendez-vous ou événement;
- lien vers l'espace client si applicable;
- proposition douce: découvrir le profil du praticien, lire un article, consulter une offre complémentaire.

## 11. Emails automatiques

### 11.1 Types d'emails V1

Parcours lead magnet:

- J0: livraison du contenu.
- J+2: conseil ou ressource complémentaire.
- J+5: invitation à réserver.

Parcours séance:

- immédiat: confirmation / rappel de l'offre.
- si pas de réservation après 48h: relance douce.
- si réservation: questionnaire pré-séance existant si configuré.

Parcours événement:

- confirmation.
- rappel 24h avant.
- suivi après événement avec lien vers réservation ou formation.

Parcours formation:

- accès formation.
- rappel si non commencé.
- suivi après quelques jours.

### 11.2 Règles importantes

- Les emails transactionnels et marketing doivent être distingués.
- Un lien de désinscription doit exister pour les emails marketing.
- Le consentement newsletter ne doit pas être implicite.
- Les relances doivent rester sobres et adaptées au secteur bien-être/santé.

## 12. Statistiques

### 12.1 Tableau de bord parcours

Métriques V1:

- vues de page;
- clics CTA;
- formulaires envoyés;
- réservations commencées;
- réservations confirmées;
- inscriptions événement;
- accès formation;
- achats;
- revenu attribué;
- taux de conversion vue -> action;
- source: lien direct, UTM, portail, newsletter, Google, Instagram.

### 12.2 Journal d'activité

Exemples:

- "Marie D. a téléchargé le guide".
- "Paul R. a réservé depuis le parcours Sommeil".
- "12 visiteurs cette semaine, 3 inscriptions".
- "Email J+2 envoyé à 18 prospects".

## 13. Gestion des prospects

### 13.1 Nouveau statut de contact

Il faut distinguer:

- prospect;
- client;
- ancien client;
- inscrit événement;
- inscrit formation;
- abonné newsletter.

La V1 peut éviter un CRM complet en ajoutant un objet dédié `FunnelLead` et un rattachement optionnel à `ClientProfile`.

### 13.2 Déduplication

Si un email existe déjà:

- rattacher au dossier client existant si même praticien;
- ajouter l'activité au profil;
- ne pas créer de doublon;
- mettre à jour les tags/segments.

### 13.3 Tags proposés

Tags automatiques:

- `prospect`;
- `parcours:{slug}`;
- `lead-magnet:{id}`;
- `a-reserve`;
- `a-achete`;
- `atelier-inscrit`;
- `formation-inscrit`.

## 14. Modèle de données proposé

Cette section décrit le modèle simplifié envisagé pour le MVP initial. La cible technique extensible et multi-étapes est définie en section 32 et la remplace pour le découpage d'implémentation.

### 14.1 `sales_funnels`

Champs:

- `id`
- `user_id`
- `name`
- `slug`
- `status`: draft, published, paused, archived
- `type`: appointment, event, lead_magnet, training, gift_voucher, contact_request
- `source_type`: product, event, digital_training, gift_voucher, questionnaire, custom
- `source_id`
- `title`
- `subtitle`
- `hero_image_path`
- `content_json`
- `theme_json`
- `cta_label`
- `cta_action`
- `thank_you_json`
- `seo_title`
- `seo_description`
- `published_at`
- `archived_at`
- timestamps

### 14.2 `sales_funnel_leads`

Champs:

- `id`
- `sales_funnel_id`
- `user_id`
- `client_profile_id`
- `email`
- `first_name`
- `last_name`
- `phone`
- `status`: new, contacted, converted, ignored
- `consent_marketing`
- `consent_at`
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `metadata`
- timestamps

### 14.3 `sales_funnel_events`

Champs:

- `id`
- `sales_funnel_id`
- `sales_funnel_lead_id`
- `session_id`
- `event_name`: view, cta_click, lead_submit, booking_started, booking_completed, payment_completed, email_sent, email_open, email_click
- `url`
- `referer`
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `metadata`
- timestamps

### 14.4 `sales_funnel_email_steps`

Champs:

- `id`
- `sales_funnel_id`
- `name`
- `trigger`: lead_created, no_booking_after_delay, booking_completed, event_completed, training_access_created
- `delay_minutes`
- `subject`
- `preheader`
- `content_json`
- `is_enabled`
- timestamps

### 14.5 `sales_funnel_email_logs`

Champs:

- `id`
- `sales_funnel_email_step_id`
- `sales_funnel_lead_id`
- `status`: queued, sent, failed, skipped
- `sent_at`
- `failure_reason`
- timestamps

## 15. Intégrations avec l'existant

### 15.1 Prestations

Un parcours peut pointer vers une prestation existante.

Règles:

- si `can_be_booked_online = true`, CTA vers réservation;
- si `collect_payment = true`, garder le flux paiement existant;
- si questionnaire attaché, le proposer dans le parcours ou après réservation;
- si prestation masquée du portail, le parcours peut être public mais doit avertir le praticien.

### 15.2 Evénements

Un parcours événement doit réutiliser:

- page publique événement;
- réservation événement;
- paiement événement;
- liste des participants.

Ajout utile:

- page d'offre enrichie avant la page d'inscription;
- emails de rappel et de suivi;
- stats par source.

### 15.3 Formations digitales

Un parcours formation doit réutiliser:

- page publique formation;
- accès gratuit avec identité;
- accès libre;
- inscription à la formation;
- lien magique d'accès.

Ajout utile:

- page de vente plus structurée;
- séquence email avant/après achat;
- relance si inscription sans démarrage.

### 15.4 Newsletters et audiences

Les prospects d'un parcours peuvent:

- alimenter une audience;
- recevoir une séquence automatique;
- être exclus si désinscrits.

Attention:

- ne pas mélanger consentement transactionnel et consentement marketing;
- garder le mécanisme de désinscription existant.

### 15.5 Dossiers clients

Lorsqu'une personne convertit:

- créer un `ClientProfile` si nécessaire;
- sinon rattacher au client existant;
- ajouter une activité dans l'historique si une timeline existe;
- conserver l'origine: parcours, UTM, source.

### 15.6 Portail public

Le portail peut afficher:

- parcours mis en avant;
- offres gratuites;
- ateliers;
- formations;
- bons cadeaux.

Paramètre par parcours:

- afficher sur le portail: oui/non;
- position: haut, section offres, section ressources;
- badge: nouveau, gratuit, places limitées.

## 16. Templates V1

### 16.1 Séance découverte

Pour:

- séance d'appel;
- première consultation;
- bilan.

Sections:

- "Vous vous reconnaissez ?"
- "Ce que nous verrons ensemble"
- "Comment se déroule la séance"
- "Réserver un créneau"

### 16.2 Atelier ou conférence

Sections:

- problème abordé;
- ce que les participants repartent avec;
- date, durée, lieu;
- nombre de places;
- inscription.

### 16.3 Guide gratuit

Sections:

- promesse du guide;
- à qui cela s'adresse;
- formulaire prénom/email;
- consentement;
- accès immédiat.

### 16.4 Formation digitale

Sections:

- transformation visée;
- modules;
- accès;
- prix;
- FAQ;
- achat / accès gratuit.

### 16.5 Bon cadeau

Sections:

- occasion;
- ce qui est inclus;
- comment le bénéficiaire l'utilise;
- achat.

## 17. Règles de sécurité, RGPD et conformité

Obligatoire:

- consentement explicite pour marketing;
- trace du consentement;
- désinscription;
- politique de confidentialité accessible;
- pas de promesse médicale non prouvée;
- éviter les témoignages trompeurs;
- ne pas afficher de données de santé dans les statistiques ou logs;
- suppression ou anonymisation si demande utilisateur.

Texte de consentement exemple:

"J'accepte de recevoir ce contenu et les emails liés à cette demande. Je peux me désinscrire à tout moment."

Si inscription newsletter séparée:

"J'accepte aussi de recevoir les actualités et conseils de ce praticien."

## 18. Abonnements et limites

Recommandation:

| Plan | Accès |
|---|---|
| Starter | Pas d'accès, teaser uniquement |
| Pro | Peut voir les parcours disponibles, éventuellement 1 parcours simple sans email automatique |
| Premium | Parcours illimités raisonnables, emails automatiques, stats, templates |
| Trial | Accès Premium pour tester |

Limites Premium à définir:

- nombre de parcours actifs;
- nombre d'emails envoyés par mois;
- stockage fichiers lead magnets;
- nombre de contacts marketing;
- accès aux statistiques avancées.

Suggestion initiale:

- Premium: 10 parcours actifs;
- 2 000 emails marketing/mois;
- 5 lead magnets actifs;
- statistiques 12 mois.

Ces limites peuvent être augmentées plus tard selon coûts email et usage.

## 19. Architecture fonctionnelle V1

Cette section décrit le minimum de routes nécessaire à une première tranche. La carte complète des écrans et routes est définie en section 29.

### 19.1 Navigation

Ajouter dans le menu praticien:

- "Développement" ou "Marketing";
- sous-menu: "Parcours d'offre";

Ou, si on veut éviter un nouveau menu:

- Configuration -> Parcours d'offre;
- Portail -> Parcours d'offre;

Recommandation: créer un menu "Développement" à terme, mais démarrer dans Configuration est acceptable.

### 19.2 Routes proposées

Routes praticien:

- `GET /dashboard-pro/parcours-offres`
- `GET /dashboard-pro/parcours-offres/create`
- `POST /dashboard-pro/parcours-offres`
- `GET /dashboard-pro/parcours-offres/{salesFunnel}`
- `GET /dashboard-pro/parcours-offres/{salesFunnel}/edit`
- `PUT /dashboard-pro/parcours-offres/{salesFunnel}`
- `POST /dashboard-pro/parcours-offres/{salesFunnel}/publish`
- `POST /dashboard-pro/parcours-offres/{salesFunnel}/pause`
- `POST /dashboard-pro/parcours-offres/{salesFunnel}/duplicate`
- `GET /dashboard-pro/parcours-offres/{salesFunnel}/stats`

Routes publiques:

- `GET /p/{therapistSlug}/{funnelSlug}`
- `POST /p/{therapistSlug}/{funnelSlug}/lead`
- `GET /p/{therapistSlug}/{funnelSlug}/merci`

Pourquoi `/p/...`:

- plus court pour partage;
- évite de trop charger `/pro/{slug}`;
- reste indépendant du portail principal.

Alternative:

- `/pro/{slug}/offres/{funnelSlug}`

Cette alternative est plus explicite et SEO-friendly.

Recommandation finale:

- utiliser `/pro/{slug}/offres/{funnelSlug}` pour la cohérence et le SEO;
- générer un lien court plus tard si besoin.

## 20. MVP recommandé

### V1.0 - Parcours simples

Objectif: livrer vite une valeur Premium visible.

Inclus:

- CRUD parcours;
- 5 templates;
- page publique;
- liaison avec prestation, événement, formation, bon cadeau ou lead magnet;
- formulaire de capture;
- création/rattachement prospect;
- lien partageable;
- QR code;
- statistiques basiques;
- 3 emails automatiques simples;
- gating Premium.

Non inclus:

- workflow visuel;
- A/B test;
- branches conditionnelles complexes;
- domaines personnalisés.

### V1.1 - Meilleure conversion

Inclus:

- sections FAQ/témoignages réutilisables;
- blocs "preuve sociale";
- badges "places limitées", "gratuit", "nouveau";
- tracking UTM;
- textes de partage générés.

### V2 - Automatisation relationnelle

Inclus:

- tags;
- segments/audiences;
- conditions simples: si pas de réservation, envoyer relance;
- relance post-événement;
- relance formation non commencée;
- dashboard conversions/revenu.

### V3 - Avancé

Inclus:

- builder visuel de séquences;
- A/B test;
- offres complémentaires;
- domaines personnalisés;
- programme ambassadeur/affiliation;
- bibliothèque de modèles par métier.

## 21. Parcours détaillés

### 21.1 Praticien crée un lead magnet

1. Le praticien ouvre "Parcours d'offre".
2. Il clique "Créer".
3. Il choisit "Offrir un contenu gratuit".
4. Il choisit un template "Guide gratuit".
5. Il ajoute titre, promesse, fichier ou formation gratuite.
6. Il choisit les champs: prénom, email, téléphone optionnel.
7. Il active la séquence email:
   - J0: envoyer le guide;
   - J+2: conseil complémentaire;
   - J+5: proposer une séance découverte.
8. Il publie.
9. Il partage le lien.
10. Les prospects apparaissent dans les statistiques et peuvent être rattachés à une audience.

### 21.2 Public télécharge un guide

1. Le visiteur arrive depuis Instagram.
2. Il lit la page.
3. Il renseigne prénom/email.
4. Il accepte les conditions nécessaires.
5. Il reçoit le guide.
6. Il arrive sur une page de confirmation.
7. Quelques jours plus tard, il reçoit une invitation à réserver.
8. S'il réserve, il devient client ou est rattaché à un dossier existant.

### 21.3 Praticien vend un atelier

1. Il sélectionne un événement existant.
2. Olithea crée une page plus persuasive que la page événement standard.
3. Le CTA mène vers l'inscription événement existante.
4. Les paiements restent gérés par le flux événement.
5. Le praticien voit:
   - vues;
   - clics;
   - inscriptions;
   - revenu;
   - source des inscriptions.

## 22. Critères d'acceptation V1

### Côté praticien

- Un praticien Premium peut créer un parcours depuis une prestation.
- Un praticien Premium peut créer un parcours lead magnet.
- Un praticien Premium peut publier/dépublier un parcours.
- Un praticien Premium peut copier le lien public.
- Un praticien Premium peut voir les statistiques basiques.
- Un praticien non Premium voit un écran de présentation et un CTA upgrade.
- Les parcours respectent l'appartenance utilisateur.
- Un parcours archivé n'est plus accessible publiquement.

### Côté public

- Un visiteur peut consulter la page sans compte.
- Un visiteur peut remplir un formulaire de capture.
- Un visiteur peut réserver si le parcours pointe vers une prestation réservable.
- Un visiteur peut s'inscrire si le parcours pointe vers un événement.
- Un visiteur peut accéder au contenu gratuit si le parcours est un lead magnet.
- Les erreurs sont compréhensibles: offre indisponible, événement complet, lien expiré.

### Emails

- Les emails configurés sont envoyés au bon délai.
- Les emails marketing contiennent un lien de désinscription.
- Les emails ne sont pas envoyés si le prospect s'est désinscrit.
- Les emails conditionnels ne relancent pas une personne déjà convertie.

### Statistiques

- Une vue publique crée un événement de vue.
- Un clic CTA est enregistré.
- Une soumission formulaire est enregistrée.
- Une conversion réservation/inscription/achat est attribuée au parcours quand possible.

## 23. Risques

### Complexité produit

Risque: créer un mini-Systeme.io trop complexe.

Mitigation:

- templates guidés;
- peu de choix au départ;
- pas de builder libre V1.

### Délivrabilité email

Risque: hausse des emails marketing, spam, réputation domaine.

Mitigation:

- limites par plan;
- consentement;
- désinscription;
- envoi progressif;
- suivi des bounces.

### Promesses sensibles

Risque: praticiens écrivent des promesses santé trop fortes.

Mitigation:

- modèles de texte prudents;
- avertissements;
- champs FAQ/reassurance non médicaux.

### Attribution imparfaite

Risque: conversion commencée sur une page et terminée ailleurs.

Mitigation:

- session/cookie d'attribution simple;
- UTM;
- attribution dernier clic V1.

## 24. Questions ouvertes

1. Veut-on réserver cette fonctionnalité strictement au Premium ou permettre un parcours simple en Pro?
2. Doit-on créer un menu "Développement" ou rester dans "Configuration"?
3. Quel volume email Premium est acceptable économiquement?
4. Doit-on permettre les fichiers PDF lead magnets dès V1?
5. Doit-on créer une vraie notion "prospect" visible au praticien ou seulement enrichir les dossiers clients?
6. Le praticien peut-il utiliser un domaine personnalisé plus tard?
7. Doit-on proposer une validation Olithea des pages avant publication pour éviter les promesses sensibles?

## 25. Recommandation finale

Construire "Parcours d'offre" comme une couche Premium qui assemble les briques existantes:

- page publique dédiée;
- action claire;
- capture prospect;
- emails automatiques simples;
- statistiques;
- réutilisation des prestations, événements, formations, bons cadeaux, questionnaires et newsletters.

La V1 doit être très guidée. Le praticien ne doit pas avoir à comprendre le marketing automation. Il doit seulement répondre à:

1. Quelle offre voulez-vous présenter?
2. Que doit faire la personne intéressée?
3. Que doit-elle recevoir ensuite?
4. Comment voulez-vous relancer si elle n'agit pas?

Cette fonctionnalité peut devenir un argument Premium fort:

"Olithea ne vous donne pas seulement un agenda et une vitrine. Olithea vous aide à transformer vos offres en parcours concrets: attirer, rassurer, capter, réserver, vendre et suivre."

---

## 26. Définition précise de l'ambition concurrentielle

### 26.1 Ce que signifie « concurrencer Systeme.io »

Olithea n'a pas besoin de reproduire chaque fonctionnalité d'une plateforme horizontale. Le test concurrentiel utile est le suivant:

> Un praticien qui vend des séances, ateliers, accompagnements, formations ou ressources doit pouvoir lancer et suivre son parcours principal sans créer de compte Systeme.io, Mailchimp, Calendly ou sur un autre constructeur de pages.

Le produit est compétitif lorsque le praticien peut, dans Olithea:

1. définir une offre;
2. publier une ou plusieurs pages cohérentes;
3. capter un contact avec un consentement valide;
4. qualifier le besoin sans collecter inutilement de données sensibles;
5. déclencher des messages automatiques;
6. faire réserver, inscrire ou payer avec les flux Olithea;
7. délivrer un contenu, un accès ou une confirmation;
8. suivre la conversion et relancer les personnes pertinentes;
9. réutiliser le même contact dans un autre parcours;
10. comprendre les résultats sans connaissance marketing avancée.

### 26.2 Avantage vertical recherché

Systeme.io est plus large. Olithea doit être plus pertinent dans le métier du praticien.

Avantages défendables d'Olithea:

- la prestation, l'agenda et les disponibilités sont déjà connus;
- le parcours peut mener directement au bon type de rendez-vous;
- la visio, le cabinet et le domicile sont compris nativement;
- les ateliers, questionnaires, formations et bons cadeaux sont déjà reliés au compte;
- le prospect peut devenir un client sans ressaisie;
- le suivi peut s'arrêter automatiquement dès qu'une réservation ou un achat existe;
- la page peut reprendre l'identité, les avis et les informations du praticien;
- les textes et modèles sont adaptés à une relation d'accompagnement, pas à une vente agressive;
- les données restent dans le même environnement que l'activité;
- le praticien dispose d'un support cohérent sur tout le parcours.

### 26.3 Promesse de catégorie

Promesse principale proposée:

> Transformez chaque offre en un parcours simple à partager, qui présente votre accompagnement, recueille les demandes, automatise le suivi et conduit vers une réservation ou une vente.

Promesse courte dans la navigation Premium:

> Pages d'offres, contacts et suivis automatiques.

### 26.4 Critère de remplacement d'un outil externe

Pour chaque praticien pilote, mesurer:

- utilise-t-il encore un constructeur de landing pages externe?
- utilise-t-il encore un outil séparé pour la séquence email liée à son offre?
- les contacts doivent-ils être copiés manuellement entre outils?
- peut-il attribuer une réservation à une page ou une campagne?
- peut-il modifier et republier seul son parcours?
- peut-il expliquer ses résultats en moins de deux minutes?

Objectif de lancement mature: au moins 80 % des pilotes déclarent qu'ils n'ont plus besoin d'un outil de tunnel externe pour leur parcours principal.

### 26.5 Matrice de couverture concurrentielle

| Capacité marché | Systeme.io | Réponse Olithea | Phase cible | Décision |
|---|---|---|---|---|
| Pages de capture | Oui | Modèles guidés et blocs structurés | V1.0 | Parité adaptée |
| Pages de vente | Oui | Pages d'offre centrées séance, atelier, programme et formation | V1.0 | Parité adaptée |
| Tunnels multi-étapes | Oui | Suite de pages et d'actions avec transitions explicites | V1.5 | Parité adaptée |
| Formulaires | Oui | Capture, qualification, consentement et rattachement client | V1.0 | Supériorité métier |
| Emailing | Oui | Séquences liées à une offre et newsletters existantes | V1.0/V2 | Parité progressive |
| Automatisations visuelles | Oui | Modèles guidés d'abord, éditeur visuel borné ensuite | V2 | Parité simplifiée |
| Tags et segmentation | Oui | Tags automatiques, segments métier et statut de relation | V2 | Parité adaptée |
| Réservation | Oui | Agenda Olithea, lieux, visio, disponibilités et prestation | V1.0 | Supériorité métier |
| Paiement | Oui | Réutilisation des paiements rendez-vous, événements, formations et bons | V1.0 | Parité via existant |
| Cours en ligne | Oui | Formations digitales Olithea et progression existante | V1.0/V1.5 | Parité métier |
| CRM pipeline | Oui | Pipeline prospect vers rendez-vous/vente/client | V2 | Supériorité métier visée |
| Webinaires permanents | Oui | Replays, événements en visio et séquences associées | V2.5 | Adaptation |
| Communautés | Oui | Réutilisation des communautés Olithea si activées | V2.5 | Intégration |
| Vente additionnelle | Oui | Offre complémentaire sobre après achat ou réservation | V2.5 | Adaptation éthique |
| Order bump | Oui | Option complémentaire compatible au checkout | V2.5 | Adaptation |
| A/B testing | Oui | Variante de titre, promesse ou CTA avec garde-fous | V3 | Parité ciblée |
| Domaines personnalisés | Oui | Sous-domaine/domaine du praticien avec SSL géré | V3 | Parité |
| Affiliation | Oui | Programme ambassadeur avec liens et commissions | V3 | Parité ciblée |
| SMS | Oui | Rappels consentis via fournisseur dédié | V3 | Option |
| API / webhooks | Oui | Événements sortants et API d'import/export | V3 | Parité ciblée |
| Produits physiques | Oui | Hors coeur Olithea | Non prévu | Exclusion assumée |
| Boutique généraliste | Oui | Vitrine d'offres du praticien, sans gestion e-commerce généraliste | V2.5 | Adaptation |
| Sous-comptes agence | Oui | Cabinets partagés et équipes selon le modèle Olithea | V3+ | À évaluer |

La matrice ne constitue pas une promesse de date. Elle empêche deux erreurs: rétrécir le produit à une simple page statique ou investir dans des fonctions sans intérêt pour les praticiens.

## 27. Périmètre fonctionnel complet

### 27.1 Domaines du produit

Le module complet comporte huit domaines fonctionnels:

1. Parcours: objectif, étapes, transitions, statut et publication.
2. Pages: contenu, blocs, thème, SEO et versions.
3. Contacts: capture, identité, consentement, tags et historique.
4. Automatisations: déclencheurs, délais, conditions et actions.
5. Messages: emails transactionnels, emails marketing et modèles.
6. Conversion: réservation, inscription, paiement, accès et offre complémentaire.
7. Mesure: événements, attribution, statistiques et export.
8. Administration: limites, conformité, modération, support et observabilité.

### 27.2 Portée V1.0

La V1.0 doit être réellement vendable et pas seulement démontrable.

Inclus:

- un parcours composé d'une page d'offre, d'une action et d'une page de confirmation;
- six objectifs guidés;
- une liaison vers une ressource Olithea existante;
- un formulaire de capture configurable;
- un fichier ou contenu gratuit délivrable;
- trois messages automatiques maximum par parcours;
- une condition « ne pas envoyer si converti »;
- une liste de contacts par parcours;
- des statistiques essentielles;
- un lien public stable et un QR code;
- prévisualisation mobile et ordinateur;
- duplication, publication, pause et archivage;
- import de l'identité du praticien et de ses preuves existantes;
- consentement, désinscription, export et suppression;
- feature flag et gating Premium.

### 27.3 Portée V1.5

La V1.5 transforme la page d'offre en véritable parcours multi-étapes:

- plusieurs étapes publiques ordonnées;
- page de capture avant page de contenu ou de vente;
- page vidéo ou ressource;
- page de vente;
- réservation ou checkout;
- page de confirmation;
- transitions conditionnelles simples;
- modèles complets de parcours par métier;
- brouillon et version publiée séparés;
- aperçu du chemin du visiteur;
- liens de campagne avec UTM préremplis.

### 27.4 Portée V2

La V2 apporte l'automatisation relationnelle et le suivi commercial:

- éditeur visuel borné de séquences;
- déclencheurs, délais, conditions et actions;
- tags et segments;
- pipeline de contacts;
- tâches et rappels praticien;
- séquences réutilisables;
- règles de sortie et de réentrée;
- tableaux de bord par canal et par offre;
- détection de contacts inactifs;
- relance de réservation ou inscription abandonnée avec consentement adapté.

### 27.5 Portée V2.5

La V2.5 augmente la valeur commerciale sans devenir agressive:

- packs et paiements en plusieurs fois si supportés par la facturation existante;
- option complémentaire au checkout;
- proposition post-réservation ou post-achat;
- replay ou mini-conférence permanente;
- vitrine unique des offres à partager depuis les réseaux sociaux;
- communauté ou groupe associé à une formation;
- modèles de lancement et modèles permanents;
- calcul du revenu et du taux de transformation par offre.

### 27.6 Portée V3

La V3 couvre les besoins avancés qui justifient encore un outil externe:

- A/B testing ciblé;
- domaine personnalisé;
- programme ambassadeur;
- SMS consentis;
- webhooks et API;
- import de contacts et de séquences;
- bibliothèque de modèles partagés;
- assistant de rédaction encadré;
- recommandations d'optimisation fondées sur les données;
- gestion d'équipe et validation avant publication.

### 27.7 Hors périmètre permanent sauf changement stratégique

- catalogue de produits physiques et logistique;
- gestion de stock e-commerce généraliste;
- dropshipping;
- constructeur de site totalement libre;
- réseau publicitaire;
- revente en marque blanche à des agences;
- collecte de symptômes ou diagnostics dans les formulaires marketing;
- mécanismes de rareté mensongers;
- relances sans base légale ou consentement applicable.

## 28. Architecture isolée et protection de l'existant

### 28.1 Principe de bounded context

Le module doit être un contexte fonctionnel séparé nommé, par exemple, `OfferJourneys`. Il orchestre les fonctions existantes, mais ne devient pas propriétaire des rendez-vous, événements, formations, paiements ou dossiers clients.

Organisation proposée:

```text
app/
  Domain/
    OfferJourneys/
      Actions/
      Contracts/
      Data/
      Events/
      Jobs/
      Models/
      Policies/
      Services/
      Support/
  Http/
    Controllers/
      OfferJourneys/
resources/views/
  offer-journeys/
    practitioner/
    public/
routes/
  offer-journeys.php
tests/
  Feature/OfferJourneys/
  Unit/OfferJourneys/
```

Le nom exact peut suivre les conventions finales du dépôt, mais la séparation doit être conservée.

### 28.2 Règles d'isolation obligatoires

1. Toutes les nouvelles tables portent le préfixe `offer_journey_` ou `sales_funnel_`, choisi une fois puis appliqué partout.
2. Les migrations sont uniquement additives pendant la première phase.
3. Aucun champ n'est requis dans les tables métier existantes pour activer le module.
4. Les contrôleurs de réservation, événement, formation et checkout restent propriétaires de leur transaction.
5. Le module référence les ressources existantes par type et identifiant.
6. Les conversions sont reçues via événements applicatifs, adaptateurs ou paramètres d'attribution, pas par duplication des règles métier.
7. Une désactivation du feature flag retire les écrans et arrête les automatisations sans casser les pages ou données existantes.
8. Une suppression du module ne supprime jamais un rendez-vous, un client, une facture, un événement ou une formation.
9. Les routes publiques existantes restent inchangées.
10. Les tâches en file d'attente vérifient à nouveau l'état du parcours, du consentement et de la conversion au moment d'exécuter l'action.

### 28.3 Feature flags et kill switches

Flags proposés:

- `offer_journeys.enabled`: module global;
- `offer_journeys.public_pages_enabled`: rendu public;
- `offer_journeys.automation_enabled`: planification et exécution;
- `offer_journeys.email_enabled`: envoi de messages;
- `offer_journeys.tracking_enabled`: collecte analytique;
- `offer_journeys.beta_user_ids`: praticiens pilotes;
- `offer_journeys.max_active_per_user`: limite opérationnelle;
- `offer_journeys.pause_all_marketing_emails`: arrêt d'urgence délivrabilité.

Le contrôle de licence `sales_funnels` complète ces flags mais ne les remplace pas.

### 28.4 Adaptateurs vers les fonctions existantes

Contrats proposés:

```php
interface JourneyConversionAdapter
{
    public function supports(string $sourceType): bool;
    public function publicActionUrl(OfferJourney $journey, JourneyVisitor $visitor): string;
    public function resolveConversion(ConversionSignal $signal): ?JourneyConversion;
}
```

Adaptateurs initiaux:

- `AppointmentJourneyAdapter`;
- `EventJourneyAdapter`;
- `DigitalTrainingJourneyAdapter`;
- `GiftVoucherJourneyAdapter`;
- `ContactRequestJourneyAdapter`;
- `LeadMagnetJourneyAdapter`.

Chaque adaptateur traduit les concepts du module vers une fonction existante. Il n'implémente pas à nouveau le paiement, le planning ou l'inscription.

### 28.5 Événements d'intégration

Signaux métier que le module doit pouvoir consommer. Les noms ci-dessous expriment le contrat recherché; ils ne signifient pas que chaque événement existe déjà sous cette forme dans le dépôt:

- `AppointmentConfirmed` ou un événement de domaine distinct de la notification `AppointmentBooked` existante;
- `EventReservationCompleted`;
- `DigitalTrainingEnrolled`;
- `GiftVoucherPurchased`;
- `PaymentCompleted`;
- `PaymentFailed`;
- `ClientProfileCreated`;
- `NewsletterUnsubscribed`.

Créer les événements de domaine manquants dans les flux concernés ou un service d'adaptation transactionnel. Ne pas utiliser une classe de notification comme source de vérité et ne pas ajouter de logique de parcours directement dans les contrôleurs métier.

Événements émis par le module:

- `JourneyPublished`;
- `JourneyVisited`;
- `JourneyLeadCaptured`;
- `JourneyStepCompleted`;
- `JourneyConverted`;
- `JourneyEmailScheduled`;
- `JourneyEmailSent`;
- `JourneyContactUnsubscribed`.

### 28.6 Publication immuable

Le brouillon éditable et la version publique doivent être séparés.

Règle:

- le praticien modifie un brouillon;
- « Publier » crée un snapshot versionné;
- la page publique lit le dernier snapshot publié;
- une modification non publiée ne change jamais la page en ligne;
- un retour à la version précédente reste possible;
- les événements analytiques enregistrent l'identifiant de version.

Cette règle évite qu'une page en production soit partiellement modifiée ou incohérente pendant l'édition.

### 28.7 Stratégie de rollback

En cas d'incident:

1. désactiver les emails automatiques;
2. maintenir les pages en ligne si elles sont saines;
3. sinon mettre les parcours en pause avec un message neutre;
4. rediriger, si configuré, vers la ressource Olithea source;
5. conserver tous les contacts et journaux;
6. ne jamais annuler les conversions déjà réalisées;
7. reprendre les actions en attente uniquement après contrôle d'idempotence.

### 28.8 Points d'ancrage confirmés dans le dépôt

L'implémentation actuelle contient déjà:

- la route publique `therapist.show` sur `/pro/{slug}`;
- un groupe `pro/{therapist:slug}` pour les contenus publics du praticien;
- les routes de réservation, événement, formation, bon cadeau et questionnaire citées dans l'inventaire;
- `User::canUseFeature()` fondé sur `config/license_features.php`;
- `NewsletterRecipient`, `NewsletterOptOut` et un flux public de désinscription;
- une file d'attente utilisée par de nombreux mails et notifications.

Conséquences:

- enregistrer les routes d'offres dans le groupe public praticien ou dans un fichier dédié chargé de manière explicite, avec des noms `offer-journeys.*` uniques;
- ajouter `sales_funnels` ou le nom de permission retenu dans la carte de licences existante;
- construire une couche commune de suppression autour de l'existant newsletter au lieu d'avoir deux vérités contradictoires;
- utiliser les queues Laravel existantes, avec des files logiques dédiées au module;
- vérifier `route:cache` à chaque ajout car le fichier de routes principal contient déjà plusieurs surfaces publiques et authentifiées.

## 29. Carte complète des pages praticien

### 29.1 Navigation recommandée

Créer une entrée principale `Développer mon activité` avec les sous-entrées:

- `Parcours d'offre`;
- `Contacts intéressés`;
- `Campagnes et suivis` à partir de V2;
- `Résultats` à partir de V1.5.

Pour limiter l'impact visuel en V1, seule l'entrée `Parcours d'offre` peut être affichée. Les autres pages restent accessibles depuis le module.

### 29.2 Inventaire des pages

| Page | Route proposée | But | Action principale |
|---|---|---|---|
| Accueil module | `/dashboard-pro/parcours-offres` | Voir les parcours et résultats récents | Créer un parcours |
| Choix d'objectif | `/dashboard-pro/parcours-offres/create` | Choisir le résultat attendu | Choisir un modèle |
| Choix de source | `/dashboard-pro/parcours-offres/create/source` | Relier une offre Olithea | Continuer |
| Assistant initial | `/dashboard-pro/parcours-offres/create/setup` | Générer le premier brouillon | Créer le parcours |
| Vue d'ensemble | `/dashboard-pro/parcours-offres/{journey}` | Piloter un parcours | Modifier ou partager |
| Carte du parcours | `/dashboard-pro/parcours-offres/{journey}/steps` | Voir pages et transitions | Ajouter une étape |
| Éditeur de page | `/dashboard-pro/parcours-offres/{journey}/pages/{page}/edit` | Modifier le contenu | Enregistrer le brouillon |
| Formulaire | `/dashboard-pro/parcours-offres/{journey}/form` | Choisir champs et consentements | Enregistrer |
| Automatisations | `/dashboard-pro/parcours-offres/{journey}/automation` | Configurer les suivis | Activer la séquence |
| Contacts | `/dashboard-pro/parcours-offres/{journey}/contacts` | Voir et qualifier les leads | Ouvrir un contact |
| Fiche contact | `/dashboard-pro/contacts/{contact}` | Voir historique et statut | Contacter ou convertir |
| Statistiques | `/dashboard-pro/parcours-offres/{journey}/analytics` | Comprendre le résultat | Créer un lien de campagne |
| Publication | `/dashboard-pro/parcours-offres/{journey}/publish` | Contrôler puis publier | Publier |
| Partage | `/dashboard-pro/parcours-offres/{journey}/share` | Copier lien, QR et textes | Copier le lien |
| Paramètres | `/dashboard-pro/parcours-offres/{journey}/settings` | SEO, domaine, statut, suppression | Enregistrer |
| Bibliothèque | `/dashboard-pro/parcours-offres/templates` | Choisir un modèle métier | Utiliser ce modèle |
| Limites Premium | `/dashboard-pro/parcours-offres/usage` | Voir contacts, emails et stockage | Gérer l'abonnement |

### 29.3 Navigation interne d'un parcours

Onglets stables:

1. Vue d'ensemble.
2. Pages.
3. Formulaire.
4. Suivis automatiques.
5. Contacts.
6. Résultats.
7. Paramètres.

Sur mobile, utiliser une liste ou un menu compact, pas des onglets qui débordent horizontalement.

### 29.4 États transverses

Chaque page doit définir:

- chargement;
- liste vide;
- erreur récupérable;
- accès non Premium;
- ressource source supprimée ou indisponible;
- parcours brouillon;
- parcours publié avec modifications non publiées;
- parcours en pause;
- quota atteint;
- absence de permission;
- fonctionnement mobile.

## 30. Spécification détaillée des écrans praticien

### 30.1 Accueil « Parcours d'offre »

Objectif utilisateur: savoir ce qui est actif, ce qui fonctionne et quoi faire ensuite.

En-tête:

- titre `Parcours d'offre`;
- texte court: `Présentez une offre, recueillez les demandes et guidez chaque personne vers la prochaine étape.`;
- bouton principal `Créer un parcours`;
- menu secondaire: modèles, utilisation, aide.

Résumé compact:

- parcours publiés;
- nouveaux contacts sur 30 jours;
- conversions sur 30 jours;
- revenu attribué si disponible;
- alerte opérationnelle éventuelle.

Liste:

- nom et type;
- état;
- miniature discrète;
- objectif;
- 30 derniers jours: vues, contacts, conversions;
- date de dernière modification;
- actions rapides: partager, voir, modifier, menu.

Filtres:

- état;
- type;
- période;
- recherche par nom.

État vide:

- montrer les six objectifs sous forme de liste structurée;
- recommander un premier modèle selon les fonctions déjà utilisées par le praticien;
- bouton `Créer mon premier parcours`;
- ne pas afficher de faux chiffres ou de faux témoignages.

Wireframe indicatif:

```text
Parcours d'offre                         [Créer un parcours]
Présentez, recueillez, suivez.

3 publiés    42 contacts    8 conversions    640 EUR attribués

[Tous] [Publiés] [Brouillons]       [Rechercher................]

Guide sommeil        Contenu gratuit   Publié
184 vues   27 contacts   6 réservations       [Partager] [Ouvrir]

Atelier respiration  Atelier           Brouillon
Dernière modification aujourd'hui               [Continuer]
```

### 30.2 Choix d'objectif

Question affichée:

> Que voulez-vous obtenir avec ce parcours ?

Choix et résultat annoncé:

- `Obtenir des réservations`: présente une séance et ouvre l'agenda correspondant;
- `Remplir un atelier`: présente l'événement et gère les inscriptions;
- `Offrir une ressource`: capte un contact et délivre le contenu;
- `Vendre une formation`: présente, vend et donne accès au programme;
- `Vendre un bon cadeau`: explique l'offre et ouvre le checkout;
- `Recevoir des demandes qualifiées`: pose quelques questions puis propose un échange.

Chaque option doit afficher:

- icône;
- nom;
- résultat concret;
- prérequis éventuel;
- exemple métier;
- badge `Recommandé` si cohérent avec l'activité du compte.

Ne pas demander au praticien de choisir un type technique comme `funnel`, `landing page` ou `workflow`.

### 30.3 Choix de la ressource source

La page liste uniquement les ressources appartenant au praticien et compatibles avec l'objectif.

Pour chaque ressource:

- nom;
- statut;
- prix;
- durée/date;
- réservation ou paiement activé;
- avertissement si configuration incomplète.

Actions:

- `Utiliser cette prestation`;
- `Créer une nouvelle prestation` dans le flux existant puis revenir;
- `Continuer sans ressource` uniquement pour lead magnet ou contact qualifié.

Le retour depuis la création d'une ressource doit restaurer le brouillon de parcours.

### 30.4 Assistant de configuration initiale

Le formulaire doit produire une page exploitable en moins de dix minutes.

Questions minimales:

1. Quel est le nom public de l'offre?
2. À qui s'adresse-t-elle?
3. Quel problème ou besoin aide-t-elle à aborder?
4. Que va obtenir la personne?
5. Quelle action voulez-vous qu'elle réalise?
6. Quelle image souhaitez-vous utiliser?

Les champs préremplis depuis la ressource doivent être visibles et modifiables pour le parcours sans modifier la ressource source.

Sortie:

- création du brouillon;
- génération des blocs recommandés;
- création de la page de confirmation;
- proposition d'une séquence de suivi désactivée tant qu'elle n'a pas été contrôlée;
- arrivée dans la vue d'ensemble.

### 30.5 Vue d'ensemble d'un parcours

Cette page doit répondre à quatre questions:

1. Est-il en ligne?
2. Où mène-t-il?
3. Fonctionne-t-il?
4. Quelle est la prochaine action utile?

Contenu:

- état et URL publique;
- ressource source;
- aperçu du chemin;
- indicateurs 7 et 30 jours;
- statut des messages automatiques;
- derniers contacts;
- alertes de configuration;
- historique des publications.

Actions prioritaires selon état:

- brouillon: `Continuer la configuration`;
- prêt: `Vérifier et publier`;
- publié: `Partager`;
- publié sans trafic: `Créer un lien de campagne`;
- trafic sans conversion: `Améliorer la page`;
- source indisponible: `Réparer la destination`.

### 30.6 Éditeur guidé de page

Disposition ordinateur:

- panneau gauche: liste des sections;
- centre: aperçu fidèle de la page;
- panneau droit ou tiroir: réglages de la section sélectionnée;
- barre supérieure: statut de sauvegarde, aperçu mobile/ordinateur, annuler/rétablir, prévisualiser, publier.

Disposition mobile:

- liste des sections;
- édition d'une section dans une page dédiée ou un tiroir plein écran;
- aperçu dans un nouvel écran;
- aucune tentative de miniaturiser trois panneaux.

Actions sur un bloc:

- modifier;
- masquer;
- déplacer;
- dupliquer si autorisé;
- restaurer le contenu proposé;
- supprimer si non obligatoire.

Blocs obligatoires:

- identité du praticien;
- titre/promesse;
- action principale;
- informations légales minimales.

Garde-fous:

- un seul H1;
- contraste contrôlé;
- CTA toujours identifiable;
- aucun HTML libre en V1;
- texte purifié côté serveur;
- images redimensionnées et optimisées;
- avertissement pour promesses médicales à risque, sans prétendre faire une validation juridique automatique.

### 30.7 Configuration du formulaire

Champs disponibles V1:

- prénom;
- nom;
- email;
- téléphone;
- préférence de contact;
- ville ou code postal si justifié;
- réponse courte personnalisée;
- choix unique personnalisé;
- case de consentement marketing séparée.

Règles:

- email obligatoire pour délivrance ou séquence email;
- téléphone jamais obligatoire par défaut;
- maximum de trois questions personnalisées en V1;
- interdire ou avertir sur les questions de santé, symptômes, diagnostic ou antécédents;
- texte de confidentialité à proximité immédiate du bouton;
- consentement marketing non précoché;
- la finalité de chaque champ doit être expliquée au praticien.

### 30.8 Écran des suivis automatiques V1

Présenter une chronologie, pas un graphe complexe:

```text
Lorsqu'une personne demande le guide
  Immédiatement  [Envoyer le guide]                  Activé
  Après 2 jours  [Envoyer un conseil complémentaire] Activé
  Après 5 jours  [Proposer une séance]                Activé

Règle de sortie: arrêter si la personne réserve ou se désinscrit.
```

Pour chaque message:

- délai;
- objet;
- aperçu;
- état;
- condition de non-envoi;
- test vers l'adresse du praticien;
- modifier;
- désactiver.

Avant activation:

- confirmer l'identité d'expéditeur;
- vérifier le consentement nécessaire;
- vérifier que chaque lien est valide;
- envoyer un test recommandé;
- afficher le volume mensuel restant.

### 30.9 Contacts d'un parcours

Colonnes:

- identité;
- date d'entrée;
- source;
- étape atteinte;
- statut;
- dernière activité;
- conversion;
- consentement marketing;
- action.

Filtres:

- nouveau;
- à contacter;
- en réflexion;
- réservé;
- acheté;
- perdu/refusé;
- désinscrit;
- source et période.

Actions:

- ouvrir la fiche;
- marquer comme contacté;
- ajouter une note non médicale;
- proposer un lien de réservation;
- rattacher à un dossier client;
- exporter selon permissions;
- supprimer/anonymiser selon règles.

### 30.10 Fiche contact

Sections:

- identité et coordonnées;
- finalité et consentements;
- statut commercial;
- parcours d'origine;
- chronologie;
- messages envoyés;
- actions réalisées;
- liens avec rendez-vous, événements, formations ou achats;
- notes internes;
- demandes RGPD.

La fiche contact marketing ne doit pas afficher les informations cliniques du dossier client. Un lien contrôlé peut ouvrir le dossier si le praticien dispose de la permission et si le contact est rattaché.

### 30.11 Statistiques

En-tête:

- période;
- comparaison à la période précédente;
- filtre par source/campagne;
- export CSV.

Entonnoir principal:

- visiteurs uniques;
- action principale;
- contacts identifiés;
- début de réservation/checkout;
- conversions;
- revenu attribué.

Graphiques utiles:

- tendance quotidienne/hebdomadaire;
- conversion par canal;
- conversion par appareil;
- performance des liens de campagne;
- performance des messages;
- étapes où les visiteurs quittent le parcours à partir de V1.5.

Ne pas surcharger l'écran de métriques de vanité. Afficher une explication en français courant sous chaque taux.

### 30.12 Contrôle avant publication

Checklist bloquante:

- titre présent;
- CTA configuré;
- source accessible;
- page de confirmation configurée;
- formulaire conforme;
- politique de confidentialité accessible;
- email de livraison valide si contenu gratuit;
- consentement cohérent avec les suivis;
- lien de désinscription disponible;
- image sans erreur;
- rendu mobile vérifié.

Checklist non bloquante:

- ajouter une preuve;
- compléter la FAQ;
- définir le SEO;
- tester le parcours;
- créer un lien de campagne.

La publication affiche clairement ce qui sera visible et ce qui sera envoyé.

### 30.13 Partage

Sorties:

- URL canonique;
- bouton copier;
- QR code PNG/PDF;
- liens de campagne pour Instagram, Google Business, newsletter, Facebook, carte papier;
- texte de partage court;
- aperçu Open Graph;
- bouton ajouter au portail Olithea.

Chaque lien de campagne possède un libellé humain et des UTM générés. Le praticien ne doit pas saisir manuellement les paramètres techniques.

## 31. Expérience publique et pages du parcours

### 31.1 Types d'étapes publiques

Le modèle cible doit supporter:

- `landing`: présentation courte et CTA;
- `opt_in`: capture de contact;
- `content`: guide, vidéo, audio, article ou mini-cours;
- `sales`: présentation complète d'une offre;
- `qualification`: formulaire de demande;
- `booking`: entrée vers le flux de réservation;
- `event_registration`: entrée vers l'inscription événement;
- `checkout`: entrée vers le paiement existant;
- `training_access`: accès à une formation;
- `thank_you`: confirmation et prochaine action;
- `offer`: proposition complémentaire future.

La V1.0 peut utiliser seulement `sales/opt_in` puis `thank_you`, mais le modèle de données ne doit pas supposer qu'un parcours n'aura toujours qu'une page.

### 31.2 URL publique

URL canonique recommandée:

`https://olithea.fr/pro/{therapistSlug}/offres/{journeySlug}`

Étapes supplémentaires:

`https://olithea.fr/pro/{therapistSlug}/offres/{journeySlug}/{pageSlug}`

Contraintes:

- slug unique par praticien;
- redirection permanente depuis un ancien slug;
- URL stable après changement de titre;
- URL courte facultative plus tard;
- paramètre de prévisualisation signé pour les brouillons;
- aucune fuite d'identifiant interne séquentiel.

### 31.3 Page d'offre publique

Premier écran:

- marque ou identité du praticien clairement visible;
- nom précis de l'offre en H1;
- résultat attendu formulé avec prudence;
- modalités utiles: durée, lieu/visio et prix si applicable;
- CTA principal;
- preuve ou réassurance immédiate;
- aucun menu général qui détourne inutilement du parcours;
- lien discret vers le profil complet du praticien.

Ordre recommandé:

1. Offre et action.
2. Situation à laquelle elle répond.
3. Ce que la personne vivra ou recevra.
4. Déroulé concret.
5. À qui cela s'adresse et limites.
6. Présentation du praticien.
7. Avis ou témoignages réels.
8. Prix, durée et modalités.
9. FAQ.
10. CTA final.
11. Mentions et confidentialité.

### 31.4 Formulaire public

Comportement:

- validation en ligne et côté serveur;
- conservation des valeurs en cas d'erreur;
- message d'erreur précis;
- prévention du double envoi;
- CAPTCHA adaptatif ou honeypot;
- limitation de débit;
- réponse générique si l'email existe déjà afin d'éviter l'énumération;
- création d'un événement analytique sans enregistrer le contenu sensible dans les logs;
- envoi de la ressource même si le contact est déjà connu et autorisé;
- écran de succès accessible.

### 31.5 Flux de réservation

Le CTA doit transmettre un contexte d'attribution signé ou opaque au flux existant:

- identifiant du parcours;
- identifiant de version;
- identifiant visiteur/session;
- campagne;
- étape source.

Le flux de réservation reste inchangé fonctionnellement. À confirmation, le module reçoit un signal de conversion et:

- rattache la conversion au contact;
- arrête les relances incompatibles;
- déclenche le suivi post-réservation prévu;
- met à jour les statistiques.

### 31.6 Flux de paiement

Même principe:

- aucun numéro de carte n'est manipulé par le module;
- Stripe et les checkouts existants restent la source de vérité;
- le retour navigateur seul ne suffit pas pour confirmer un paiement;
- le webhook ou l'état métier confirmé produit la conversion;
- un paiement échoué ne marque pas le contact comme acheteur;
- une reprise de paiement conserve l'attribution si elle reste dans la fenêtre prévue.

### 31.7 Page de confirmation

La confirmation doit répondre immédiatement:

- l'action a-t-elle réussi?
- que va recevoir la personne?
- quand?
- depuis quelle adresse email?
- que faire si elle ne reçoit rien?

Actions secondaires possibles:

- ajouter au calendrier;
- ouvrir l'espace client;
- télécharger la ressource;
- consulter une prochaine étape;
- retourner au profil du praticien.

La vente complémentaire ne doit jamais masquer la confirmation principale.

### 31.8 États publics dégradés

- parcours en pause: message du praticien et retour au profil;
- parcours archivé: 404 ou redirection configurée;
- événement complet: liste d'attente ou autre offre;
- plus de créneau: conserver la page et proposer notification/contact;
- source supprimée: désactiver le CTA et alerter le praticien;
- quota email atteint: accepter l'action transactionnelle nécessaire et suspendre les suivis marketing;
- incident paiement: explication et nouvelle tentative;
- fichier indisponible: journaliser, prévenir et proposer un nouvel envoi.

## 32. Modèle de parcours multi-étapes

### 32.1 Entités principales révisées

Le modèle initial à cinq tables doit évoluer pour éviter de stocker toute la page dans `sales_funnels.content_json`.

Entités recommandées:

- `offer_journeys`: identité, objectif, propriétaire, source, état;
- `offer_journey_versions`: snapshot publié et version du schéma;
- `offer_journey_pages`: pages/étapes du brouillon;
- `offer_journey_page_versions`: contenu publié par page;
- `offer_journey_transitions`: liens entre étapes;
- `offer_journey_forms`: définition de formulaire;
- `offer_journey_form_fields`: champs ordonnés;
- `offer_journey_contacts`: contact unique par praticien;
- `offer_journey_entries`: entrée d'un contact dans un parcours;
- `offer_journey_events`: événements analytiques;
- `offer_journey_automations`: séquences;
- `offer_journey_automation_nodes`: déclencheurs, délais, conditions, actions;
- `offer_journey_automation_runs`: exécutions par contact;
- `offer_journey_message_deliveries`: envois et statut;
- `offer_journey_conversions`: conversions métier;
- `offer_journey_campaign_links`: liens de partage et attribution;
- `offer_journey_consents`: preuves de consentement;
- `offer_journey_suppressions`: désinscription, bounce et plainte;
- `offer_journey_assets`: fichiers et images;
- `offer_journey_slug_redirects`: anciennes URLs.

### 32.2 `offer_journeys`

Champs essentiels:

- `id` UUID/ULID ou identifiant non exposé;
- `user_id` propriétaire;
- `name` interne;
- `slug` public;
- `objective`;
- `status`: draft, published, paused, archived;
- `source_type`;
- `source_id`;
- `primary_conversion_type`;
- `published_version_id` nullable;
- `timezone` héritée du praticien;
- `show_on_profile`;
- `created_at`, `updated_at`, `published_at`, `paused_at`, `archived_at`.

Index et contraintes:

- unique `(user_id, slug)`;
- index `(user_id, status)`;
- index `(source_type, source_id)`;
- contrainte d'appartenance vérifiée en policy et service.

### 32.3 Pages et versions

`offer_journey_pages`:

- identifiant du parcours;
- nom interne;
- slug;
- type;
- position d'affichage dans l'éditeur;
- contenu de brouillon JSON versionné;
- thème local optionnel;
- SEO;
- état de validation;
- timestamps.

`offer_journey_page_versions`:

- page source;
- version du parcours;
- contenu JSON immuable;
- hash du contenu;
- auteur de publication;
- date de publication.

Le JSON est acceptable pour les blocs de présentation, mais pas pour les contacts, consentements, conversions ou exécutions qui nécessitent des colonnes et index explicites.

### 32.4 Transitions

Une transition contient:

- page source;
- événement déclencheur: CTA, formulaire valide, paiement confirmé, réservation confirmée;
- condition optionnelle;
- page cible ou action externe;
- priorité;
- fallback;
- état actif.

En V1.5, autoriser une seule transition principale et une transition de secours par page. Les graphes cycliques sont interdits avant V2.

### 32.5 Contact et entrée dans un parcours

Séparer:

- le contact, unique à l'échelle du praticien;
- son entrée dans un parcours donné;
- les événements et messages de cette entrée.

Cela permet à une même personne de télécharger un guide puis de s'inscrire à un atelier sans créer deux identités.

Clé de déduplication recommandée:

- email normalisé par praticien;
- téléphone normalisé comme signal secondaire;
- rattachement manuel possible;
- fusion journalisée et réversible par l'administration.

### 32.6 Conversions

`offer_journey_conversions` doit contenir:

- parcours et version;
- contact et entrée;
- type de conversion;
- `convertible_type` et `convertible_id` vers l'objet métier;
- montant et devise si applicable;
- état: pending, confirmed, cancelled, refunded;
- source d'attribution;
- date de conversion;
- clé d'idempotence.

Une annulation ou un remboursement met à jour l'état; il ne supprime pas l'historique.

### 32.7 Nommage technique

Le document utilise `offer_journey_*` pour refléter le vocabulaire produit. Si l'équipe conserve `sales_funnel_*`, elle doit documenter la correspondance et ne pas mélanger les deux préfixes. La décision doit être prise avant la première migration.

## 33. Système de blocs et contenu

### 33.1 Bibliothèque de blocs V1

- identité praticien;
- hero offre;
- texte riche purifié;
- image;
- vidéo externe autorisée;
- bénéfices ou résultats attendus;
- public concerné;
- déroulé en étapes;
- informations pratiques;
- prix;
- prestation liée;
- événement lié;
- formation liée;
- formulaire;
- avis existants;
- témoignage manuel avec attestation de véracité;
- FAQ;
- CTA;
- téléchargement;
- réassurance/confidentialité;
- pied de page légal.

### 33.2 Schéma de bloc

Chaque bloc possède:

- `type`;
- `schema_version`;
- `id` stable dans la page;
- `content`;
- `settings` bornés;
- `visibility`;
- `validation_errors` calculées, non persistées dans le snapshot.

Exemple:

```json
{
  "type": "offer_hero",
  "schema_version": 1,
  "id": "01J...",
  "content": {
    "eyebrow": "Séance découverte",
    "title": "Retrouver un rythme plus serein",
    "summary": "Un premier échange pour comprendre votre besoin et définir la suite adaptée.",
    "cta_label": "Choisir un créneau"
  },
  "settings": {
    "alignment": "left",
    "show_practical_details": true
  }
}
```

### 33.3 Thèmes

V1 propose trois thèmes maximum, tous compatibles avec l'identité Olithea:

- clair et sobre;
- éditorial;
- chaleureux et professionnel.

Le praticien choisit:

- couleur principale parmi une palette contrôlée ou sa couleur de profil si accessible;
- typographie parmi les familles déjà chargées;
- style de bouton;
- densité confortable ou compacte.

Pas de CSS personnalisé ni de palette totalement libre en V1.

### 33.4 Réutilisation de contenu

Blocs réutilisables futurs:

- présentation du praticien;
- avis sélectionnés;
- FAQ générale;
- informations de cabinet;
- mentions légales;
- signature email;
- preuve de diplôme ou certification déjà publique.

Une mise à jour globale ne doit pas modifier automatiquement une version publiée sans confirmation. L'éditeur signale qu'une version plus récente du bloc existe.

### 33.5 SEO et partage social

Par page:

- titre SEO;
- meta description;
- URL canonique;
- image Open Graph;
- indexation oui/non;
- données structurées compatibles avec le type de contenu, uniquement si les données sont fiables;
- aperçu du partage;
- redirection d'ancien slug.

Règles:

- pages de merci et étapes privées en `noindex`;
- pages de capture sans contenu suffisant peuvent être `noindex` par défaut;
- page d'offre principale indexable si publiée et complète;
- sitemap actualisé via le mécanisme existant;
- pas de texte SEO généré invisible.

## 34. Automatisation relationnelle

### 34.1 Modèle déclencheur-condition-action

Déclencheurs:

- contact capturé;
- formulaire soumis;
- tag ajouté;
- étape consultée;
- lien cliqué;
- réservation commencée;
- réservation confirmée;
- inscription événement confirmée;
- paiement confirmé;
- formation commencée;
- formation inactive depuis N jours;
- événement terminé;
- date ou délai atteint.

Conditions:

- a ou n'a pas un tag;
- a ou n'a pas réservé;
- a ou n'a pas acheté;
- est inscrit ou non à un événement;
- a consenti au marketing;
- a atteint une étape;
- source/campagne;
- date de dernière activité;
- statut du contact.

Actions:

- envoyer un email;
- ajouter/retirer un tag;
- changer le statut;
- créer une tâche praticien;
- notifier le praticien;
- donner/revoquer un accès formation selon droit métier;
- inscrire à une audience;
- arrêter la séquence;
- déplacer dans une étape de pipeline;
- appeler un webhook à partir de V3.

### 34.2 Automatisations V1

La V1 expose seulement des séquences préconstruites:

- livraison d'un contenu;
- rappel si aucune réservation;
- rappel événement;
- suivi post-événement;
- accueil formation;
- relance de formation non commencée;
- confirmation de demande qualifiée.

Le praticien peut modifier les délais et les messages dans les limites du modèle. Il ne peut pas créer de boucle ou branche arbitraire.

### 34.3 Exécution technique

Règles obligatoires:

- chaque exécution a une clé d'idempotence;
- un job recontrôle le consentement et l'état avant envoi;
- un job recontrôle que le parcours et le noeud sont actifs;
- les délais utilisent le fuseau horaire du praticien;
- les plages silencieuses évitent les envois marketing nocturnes;
- les erreurs temporaires sont réessayées avec backoff;
- les erreurs permanentes sont enregistrées et visibles;
- les actions réussies ne sont jamais répétées après retry;
- la publication d'une nouvelle version ne modifie pas une exécution déjà entrée dans l'ancienne séquence sans règle explicite;
- la pause globale empêche toute nouvelle action marketing.

### 34.4 Entrée, réentrée et sortie

Par automatisation, définir:

- entrée unique ou réentrée autorisée;
- délai minimum avant réentrée;
- événement de sortie;
- conversion qui arrête la séquence;
- comportement en cas de désinscription;
- comportement en cas d'archivage du parcours;
- comportement en cas de changement d'adresse email.

Défaut recommandé:

- une entrée par contact et par parcours;
- sortie à conversion, désinscription ou suppression;
- réentrée manuelle uniquement en V1;
- aucune relance après conversion sauf message explicitement post-conversion.

### 34.5 Éditeur visuel V2

Le canvas V2 propose uniquement cinq types de noeuds:

- déclencheur;
- délai;
- condition;
- action;
- fin.

Contraintes UX:

- un début unique;
- pas de connexion libre entre n'importe quels noeuds;
- validation des branches sans sortie;
- simulation sur un contact de test;
- résumé en français courant;
- publication séparée du brouillon;
- journal des changements;
- duplication depuis un modèle;
- bouton d'arrêt immédiat.

### 34.6 Exemple complet

Parcours « Guide sommeil »:

1. Déclencheur: guide demandé.
2. Action: envoyer le guide immédiatement.
3. Action: ajouter le tag `interet-sommeil`.
4. Délai: deux jours, en respectant les heures d'envoi.
5. Condition: la personne a-t-elle réservé la prestation liée?
6. Oui: arrêter la séquence d'acquisition.
7. Non: envoyer un conseil complémentaire.
8. Délai: trois jours.
9. Condition: consentement marketing encore valide et aucune conversion.
10. Oui: envoyer une invitation vers la séance découverte.
11. Fin: créer une tâche praticien si le contact a cliqué sans réserver, uniquement si ce suivi est activé.

## 35. Contacts, pipeline et relation client

### 35.1 Distinction des objets

Ne pas confondre:

- `visiteur`: navigateur anonyme avec identifiant technique limité;
- `contact`: personne identifiée auprès d'un praticien;
- `entrée de parcours`: participation du contact à un parcours précis;
- `prospect`: contact que le praticien souhaite suivre commercialement;
- `client`: personne rattachée à un `ClientProfile`;
- `abonné`: contact ayant consenti à des communications régulières;
- `participant`: personne inscrite à un événement ou une formation.

Une personne peut cumuler plusieurs rôles. Le statut commercial ne doit pas remplacer les statuts métier existants.

### 35.2 Pipeline V2

Étapes par défaut:

1. Nouveau contact.
2. À qualifier.
3. Échange en cours.
4. Rendez-vous proposé.
5. Réservé ou acheté.
6. Non retenu / pas maintenant.

Le praticien peut renommer les étapes à partir de V2.5, mais les états système de conversion restent séparés.

Fonctions:

- tableau Kanban et vue liste;
- déplacement manuel;
- déplacement automatique sur événement;
- date de prochaine action;
- responsable si cabinet partagé;
- filtre par offre, source et statut;
- notes;
- historique immuable des changements importants;
- export.

### 35.3 Tâches praticien

Exemples:

- rappeler une demande qualifiée;
- répondre à une question;
- proposer un créneau;
- vérifier un paiement;
- recontacter après atelier;
- demander un avis après accompagnement, via le mécanisme existant si possible.

Une tâche contient:

- contact;
- parcours;
- motif;
- échéance;
- priorité simple;
- statut;
- responsable;
- lien vers l'action pertinente.

Pas de relance automatique supplémentaire si la tâche est en retard. Le système peut notifier le praticien.

### 35.4 Déduplication et fusion

Normalisation:

- email en minuscules et espaces supprimés;
- téléphone au format international si possible;
- Unicode normalisé pour les noms;
- aucune fusion automatique sur le seul nom.

Ordre de résolution:

1. contact du même praticien avec email identique;
2. dossier client du même praticien avec email identique;
3. suggestion de correspondance téléphone;
4. création d'un nouveau contact.

La fusion:

- conserve toutes les entrées et conversions;
- conserve la preuve de chaque consentement;
- choisit une identité principale;
- est journalisée;
- ne fusionne jamais les dossiers de deux praticiens différents;
- ne propage pas automatiquement un consentement d'un praticien à un autre.

### 35.5 Import et export

V2.5 peut autoriser un import CSV:

- mappage des colonnes;
- prévisualisation;
- déduplication;
- origine de l'import;
- déclaration de la base légale par le praticien;
- aucun email automatique envoyé par défaut après import;
- rapport d'erreurs.

Export:

- contacts du praticien uniquement;
- filtres et période;
- consentement et statut inclus;
- données sensibles exclues par défaut;
- journalisation de l'export;
- lien temporaire et protégé.

### 35.6 Passage de prospect à client

Créer ou rattacher un `ClientProfile` seulement lors d'une action métier pertinente:

- réservation confirmée;
- achat nécessitant un espace client;
- inscription gérée comme client;
- action manuelle explicite du praticien.

Le simple téléchargement d'un guide ne doit pas créer un dossier client complet.

Lors du passage:

- conserver l'origine du contact;
- rattacher la conversion;
- éviter la duplication d'identité;
- ne pas copier les notes marketing dans les données de suivi thérapeutique;
- arrêter les séquences d'acquisition incompatibles.

## 36. Email, consentement et délivrabilité

### 36.1 Catégories de messages

Transactionnel nécessaire:

- livraison du contenu demandé;
- confirmation de demande;
- confirmation de réservation/inscription/achat;
- accès formation;
- rappel directement lié à l'action, selon le cadre applicable.

Marketing ou prospection:

- conseils non nécessaires à la demande initiale;
- invitation vers une autre offre;
- newsletter;
- relance après une période sans action;
- lancement ou promotion.

Chaque modèle doit avoir une catégorie persistée. Le praticien ne peut pas transformer un message marketing en transactionnel par simple sélection.

### 36.2 Registre de consentement

Pour chaque consentement:

- contact;
- praticien responsable;
- finalité;
- texte exact ou version du texte;
- source et parcours;
- date/heure;
- adresse IP tronquée ou hashée selon décision juridique;
- user-agent réduit si nécessaire;
- statut: granted, withdrawn, expired;
- date de retrait;
- preuve d'origine.

Finalités séparées:

- recevoir la ressource demandée;
- recevoir la séquence liée à cette ressource;
- recevoir les actualités régulières du praticien;
- être recontacté par téléphone;
- partage éventuel avec un tiers, non prévu par défaut.

La formulation finale doit être validée avec le conseil RGPD. Le produit doit rendre techniquement possible cette séparation.

### 36.3 Désinscription

Exigences:

- lien signé et non devinable;
- désinscription en un clic ou confirmation très courte;
- prise en compte immédiate;
- page de confirmation sans connexion;
- possibilité de choisir la finalité si plusieurs consentements existent;
- suppression de tous les envois marketing concernés;
- messages transactionnels nécessaires toujours possibles;
- synchronisation avec `NewsletterOptOut` ou couche de suppression commune;
- journalisation de la source du retrait.

### 36.4 Identité d'expéditeur

V1 recommandée:

- adresse technique validée du domaine Olithea;
- nom d'expéditeur: `{Prénom Nom} via Olithea` ou nom professionnel validé;
- `Reply-To` vers l'adresse professionnelle du praticien;
- signature claire;
- adresse et mentions requises selon le contexte;
- aucune usurpation d'un domaine praticien non authentifié.

V3:

- domaine d'envoi personnalisé;
- vérification DNS guidée;
- SPF, DKIM et DMARC contrôlés;
- état de santé du domaine;
- fallback vers l'expéditeur Olithea en cas d'échec, seulement avec information claire.

### 36.5 Suppression globale et réputation

Une suppression commune doit couvrir:

- désinscription volontaire;
- bounce permanent;
- plaintes;
- adresse invalide;
- blocage administratif;
- demande RGPD;
- risque de sécurité.

Avant tout envoi marketing, vérifier:

1. feature flag;
2. licence et quota;
3. parcours et automatisation actifs;
4. consentement applicable;
5. absence de suppression;
6. absence de conversion qui déclenche la sortie;
7. fréquence maximale;
8. validité des liens et du contenu publié.

### 36.6 Quotas et fréquence

Limites initiales à tester:

- maximum trois messages automatiques par parcours en V1;
- maximum un message marketing par contact sur 24 heures au niveau du praticien;
- plafond mensuel selon abonnement;
- seuil progressif pour les nouveaux comptes;
- blocage automatique en cas de taux de bounce ou plainte anormal;
- transactionnel prioritaire sur marketing.

Le quota est vérifié à la programmation et à l'envoi. Si le quota est atteint, l'action passe à `skipped_quota`, reste visible et n'est pas envoyée en retard le mois suivant.

### 36.7 Suivi d'ouverture et de clic

Les ouvertures d'email sont imprécises et peuvent être affectées par les protections de confidentialité. Présentation recommandée:

- clics comme signal principal;
- ouvertures marquées `estimation`;
- conversion comme métrique prioritaire;
- option de désactiver le pixel d'ouverture;
- ne jamais présenter une ouverture comme preuve qu'une personne a réellement lu le message.

### 36.8 Éditeur de message

V1:

- objet;
- pré-en-tête;
- paragraphes;
- bouton principal;
- image optionnelle;
- signature;
- variables autorisées;
- aperçu mobile;
- email de test;
- vérification des liens;
- texte brut généré automatiquement.

Variables sûres:

- prénom contact;
- nom praticien;
- nom de l'offre;
- lien de parcours;
- lien de réservation;
- date/lieu événement;
- lien de désinscription.

Les variables inconnues doivent bloquer l'activation ou disposer d'un fallback explicite.

## 37. Mesure, attribution et définitions des métriques

### 37.1 Événements analytiques normalisés

Événements publics:

- `page_viewed`;
- `primary_cta_clicked`;
- `form_started`;
- `form_submitted`;
- `step_completed`;
- `booking_started`;
- `checkout_started`;
- `content_accessed`.

Événements confirmés côté serveur:

- `lead_captured`;
- `appointment_booked`;
- `event_registered`;
- `payment_confirmed`;
- `training_enrolled`;
- `gift_voucher_purchased`;
- `conversion_cancelled`;
- `payment_refunded`.

Événements messages:

- `message_queued`;
- `message_sent`;
- `message_delivered` si fournisseur;
- `message_bounced`;
- `message_opened_estimated`;
- `message_clicked`;
- `message_unsubscribed`.

### 37.2 Définitions officielles

- Vue: chargement réussi d'une page publique hors prévisualisation et trafic connu comme robot.
- Visiteur unique: identifiant pseudonyme distinct dans la fenêtre de mesure.
- Contact: identité enregistrée avec email ou canal exploitable et finalité connue.
- Conversion: événement métier confirmé correspondant à l'objectif principal.
- Taux de capture: contacts / visiteurs uniques.
- Taux de clic CTA: visiteurs ayant cliqué / visiteurs uniques.
- Taux de conversion: conversions confirmées / visiteurs uniques.
- Taux contact vers conversion: conversions / contacts.
- Revenu attribué: montants confirmés moins remboursements attribués au parcours.
- Coût par contact: non calculable sans saisie/import du coût de campagne; ne pas afficher sinon.

### 37.3 Attribution

V1:

- dernier lien de parcours connu avant conversion;
- fenêtre recommandée de 30 jours, à valider avec confidentialité;
- attribution directe si conversion dans la même session;
- UTM conservés;
- première source également enregistrée à titre informatif;
- priorité à l'identifiant signé transmis aux flux Olithea;
- pas de fingerprinting.

V2:

- premier contact;
- dernier contact;
- assistance d'un email;
- comparaison par campagne;
- attribution configurable 7/30/90 jours si validée;
- conversions multi-parcours visibles mais revenu attribué une seule fois selon modèle choisi.

### 37.4 Liens de campagne

Un lien contient:

- nom humain;
- canal;
- campagne;
- contenu/variante optionnel;
- URL générée;
- QR associé;
- date de début/fin facultative;
- état actif.

Modèles de canal:

- Instagram bio;
- Instagram story;
- Google Business;
- newsletter;
- email individuel;
- Facebook;
- fiche papier/QR;
- partenaire;
- direct.

### 37.5 Qualité des données

Exclure ou marquer:

- prévisualisations praticien;
- tests internes;
- robots connus;
- rafraîchissements répétés dans une courte fenêtre;
- webhooks dupliqués;
- conversions annulées;
- événements reçus hors ordre.

Le tableau de bord indique quand une métrique est estimée, partielle ou non disponible.

### 37.6 Rétention analytique

Proposition à valider:

- événements bruts identifiants: 13 mois maximum ou durée plus courte décidée;
- agrégats anonymisés: conservation plus longue pour comparaison;
- IP jamais conservée en clair dans l'analytique;
- suppression/anonymisation liée à une demande du contact;
- journaux techniques séparés des données marketing.

## 38. Paiements et optimisation de la conversion

### 38.1 Source de vérité

Le module ne crée pas une nouvelle plateforme de paiement.

- rendez-vous: flux de paiement rendez-vous existant;
- événement: réservation/paiement événement existant;
- formation: checkout formation existant ou à compléter dans son domaine;
- bon cadeau: checkout bon cadeau existant;
- facture: flux facture existant si applicable.

Le parcours fournit la présentation, l'attribution et les actions de suivi.

### 38.2 Prix et affichage

Le prix affiché sur la page doit venir de la ressource source ou être explicitement synchronisé.

Règles:

- avertir si le prix de page diffère de la source;
- le checkout affiche toujours le montant final autoritatif;
- afficher devise et taxes selon les règles applicables;
- afficher modalités et conditions avant CTA;
- aucune promotion expirée ne reste visible;
- les places restantes doivent provenir de la capacité réelle de l'événement.

### 38.3 Vente additionnelle V2.5

Cas acceptables:

- ajouter un support numérique à une formation;
- proposer une séance de suivi après un atelier;
- proposer un pack de séances lorsque la prestation le permet;
- ajouter un contenu complémentaire pertinent.

Garde-fous:

- option non cochée par défaut;
- prix total mis à jour immédiatement;
- bénéfice concret expliqué;
- aucun compte à rebours artificiel;
- compatibilité avec facture, remboursement et droit de rétractation;
- une seule option complémentaire en première version.

### 38.4 Proposition après conversion

La page de merci peut proposer une action secondaire, mais:

- la confirmation reste prioritaire;
- l'action principale accomplie n'est jamais ambiguë;
- une offre complémentaire refusée n'altère pas l'achat initial;
- aucune facturation en un clic sans consentement explicite et support sécurisé;
- l'offre est désactivable par le praticien.

### 38.5 Abandon de réservation ou checkout

Définition:

- démarrage identifié;
- absence de confirmation dans un délai défini;
- contact connu et communication autorisée;
- aucun événement de paiement confirmé reçu.

Relance:

- une relance maximum en première version;
- lien de reprise sécurisé;
- aucune mention anxiogène;
- arrêt immédiat à conversion;
- pas de relance si le visiteur n'a pas fourni de canal avec base applicable.

### 38.6 Remboursement et annulation

Lorsqu'une conversion est annulée ou remboursée:

- mettre à jour la conversion;
- ajuster le revenu attribué;
- arrêter les séquences de bienvenue incompatibles;
- ne pas réinscrire automatiquement dans une séquence de vente;
- laisser le flux métier gérer facture, avoir et accès;
- conserver l'audit.

## 39. RGPD, sécurité et conformité métier

### 39.1 Rôles et responsabilités

À valider juridiquement, mais le produit doit pouvoir représenter:

- le praticien comme responsable de traitement pour sa prospection et sa relation;
- Olithea comme sous-traitant pour les fonctions opérées pour son compte;
- Olithea comme responsable distinct pour certaines données de plateforme si applicable;
- les fournisseurs email, stockage, paiement et mesure comme sous-traitants ou destinataires documentés.

Les CGU, DPA, registre de sous-traitants et politique de confidentialité devront être mis à jour avant ouverture générale.

### 39.2 Minimisation

Interdictions V1 dans les formulaires marketing:

- diagnostic médical;
- pathologies détaillées;
- traitements;
- documents médicaux;
- numéro de sécurité sociale;
- informations de mutuelle;
- texte libre invitant à raconter des données de santé.

Pour une qualification d'accompagnement, privilégier:

- objectif général;
- format souhaité;
- disponibilités;
- préférence de contact;
- question non médicale clairement bornée.

Si un questionnaire clinique est nécessaire, rediriger vers le mécanisme sécurisé existant après création de la relation appropriée.

### 39.3 Droits des personnes

Le module doit permettre:

- accès aux données;
- rectification;
- retrait du consentement;
- opposition;
- portabilité lorsque applicable;
- suppression/anonymisation;
- restriction opérationnelle pendant traitement de la demande.

Une demande depuis la page publique peut être transmise au praticien et visible par l'administration, avec suivi d'état et échéance.

### 39.4 Sécurité applicative

Obligatoire:

- policies sur toutes les ressources praticien;
- scoping systématique par propriétaire;
- liens signés pour aperçu et actions sensibles;
- protection CSRF pour actions navigateur;
- rate limiting public;
- validation serveur;
- purification du contenu riche;
- CSP compatible avec les médias autorisés;
- contrôle MIME et taille des fichiers;
- stockage privé des lead magnets avec liens temporaires ou délivrance contrôlée;
- scan antivirus si des fichiers arbitraires sont autorisés;
- chiffrement des secrets et jetons;
- vérification de signature des webhooks;
- idempotence;
- journal d'audit;
- tests d'accès inter-utilisateur.

### 39.5 Menaces principales

| Menace | Exemple | Mesure principale |
|---|---|---|
| Accès croisé | Un praticien ouvre le parcours d'un autre | Policies et requêtes scopées |
| Spam de formulaire | Bot créant des milliers de contacts | Rate limit, honeypot, CAPTCHA adaptatif |
| XSS | Script dans un bloc texte | Allowlist HTML et purification serveur |
| Fuite de fichier | URL PDF devinable | Stockage privé et URL temporaire |
| Double action | Job rejoué après timeout | Clé d'idempotence |
| Faux paiement | Retour navigateur manipulé | Confirmation serveur/webhook |
| Email abusif | Import puis envoi massif | Quotas, validation, seuils et blocage |
| Énumération | Tester si un email existe | Réponse publique uniforme |
| Promesse trompeuse | Allégation de guérison | Modèles, avertissements, signalement/modération |
| Exposition analytique | Donnée sensible dans metadata | Schéma d'événement strict et filtrage |

### 39.6 Modération et signalement

Fonctions:

- signaler une page publique;
- masquer d'urgence une page;
- voir l'auteur et la version publiée;
- motif de modération;
- notification au praticien;
- restauration après correction;
- liste de termes à risque servant d'avertissement, pas de décision médicale automatique;
- conservation de l'audit.

### 39.7 Mentions commerciales

Avant publication, rappeler au praticien:

- ne pas promettre de guérison ou résultat garanti;
- ne pas fabriquer de témoignages;
- afficher les prix et conditions de façon loyale;
- indiquer l'identité professionnelle requise;
- respecter le droit de rétractation des contenus et services vendus à distance;
- ne pas créer de rareté artificielle;
- disposer des droits sur les images et fichiers.

Les textes juridiques définitifs nécessitent une validation professionnelle externe.

## 40. Design, mobile et accessibilité

### 40.1 Principes visuels

Le module praticien doit rester un outil de travail:

- interface calme et dense;
- hiérarchie claire;
- peu de décoration;
- cartes seulement pour des éléments répétés ou réellement encadrés;
- indicateurs compréhensibles;
- commandes stables;
- vocabulaire français;
- aucune mise en scène de type page marketing dans le tableau de bord.

Les pages publiques peuvent être plus éditoriales, tout en restant centrées sur l'offre réelle et l'identité du praticien.

### 40.2 Responsive

Largeurs à vérifier au minimum:

- 360 px;
- 390 px;
- 768 px;
- 1024 px;
- 1440 px.

Contraintes:

- CTA public visible sans recouvrir le contenu;
- barre d'édition mobile sans chevauchement;
- titres longs contenus;
- tableaux transformés en listes exploitables sur mobile;
- formulaires avec labels persistants;
- aucune dépendance au hover;
- images avec ratio stable;
- aperçu mobile représentant réellement le rendu.

### 40.3 Accessibilité

Objectif: WCAG 2.2 niveau AA sur les parcours publics et fonctions principales.

Exigences:

- structure de titres logique;
- navigation clavier;
- focus visible;
- labels et erreurs associés;
- contraste suffisant;
- alternatives textuelles;
- boutons nommés par leur action;
- statut annoncé aux technologies d'assistance;
- pas d'information par couleur seule;
- réduction des animations si préférence système;
- zone tactile adaptée;
- langue de page correcte;
- email HTML lisible sans image.

### 40.4 Performance publique

Budgets initiaux:

- HTML utile rendu côté serveur;
- JavaScript initial limité;
- image hero optimisée et responsive;
- aucune dépendance lourde pour une page statique;
- LCP cible inférieur à 2,5 s au 75e percentile mobile;
- CLS cible inférieur à 0,1;
- INP cible inférieur à 200 ms;
- page fonctionnelle même si le tracking analytique échoue;
- CTA de réservation ou formulaire non bloqué par les scripts marketing.

### 40.5 Prévisualisation

Modes:

- ordinateur;
- mobile;
- lien de test signé;
- test du formulaire avec contact de démonstration;
- test de l'email sans entrer dans les statistiques;
- test complet marqué `is_test`.

Le bandeau de prévisualisation doit empêcher toute confusion avec une page en ligne et proposer `Retour à l'éditeur`.

## 41. Exploitation, observabilité et support

### 41.1 Tableaux de bord internes

L'équipe Olithea doit suivre:

- parcours créés, publiés et actifs;
- praticiens actifs sur 7/30 jours;
- contacts capturés;
- conversions;
- volume email par catégorie;
- bounces, plaintes et désinscriptions;
- jobs échoués;
- temps de traitement des automatisations;
- erreurs publiques par type;
- stockage consommé;
- taux de publication après création;
- quotas atteints;
- pages signalées.

### 41.2 Logs structurés

Inclure:

- identifiant parcours;
- identifiant version;
- identifiant d'exécution;
- action;
- résultat;
- code d'erreur;
- durée;
- identifiant métier associé si nécessaire;
- clé d'idempotence.

Exclure:

- contenu de formulaire libre;
- donnée de santé;
- corps complet des emails;
- jetons;
- adresse IP en clair si non nécessaire;
- secrets fournisseur.

### 41.3 Alertes

Alertes critiques:

- taux d'échec email au-dessus du seuil;
- file d'automatisation bloquée;
- délai d'exécution excessif;
- hausse brutale des formulaires/spam;
- erreurs de paiement ou attribution généralisées;
- fichier lead magnet inaccessible;
- pages publiques en erreur;
- webhook non vérifié ou échecs répétés;
- taux de plainte anormal.

### 41.4 Objectifs de service initiaux

- pages publiques disponibles à 99,9 % mensuel hors maintenance planifiée;
- soumission formulaire enregistrée sans perte après réponse de succès;
- 95 % des messages planifiés pris en charge dans les cinq minutes suivant leur échéance;
- conversion métier reflétée dans les statistiques en moins de quinze minutes;
- pause d'urgence effective en moins d'une minute pour les nouveaux envois;
- aucune action dupliquée après retry.

### 41.5 Files d'attente

Séparer logiquement ou physiquement:

- `offer-journey-events`;
- `offer-journey-automation`;
- `offer-journey-mail`;
- `offer-journey-analytics`;
- `offer-journey-files`.

Les confirmations transactionnelles ne doivent pas attendre derrière un gros volume d'agrégation analytique.

### 41.6 Planification

Utiliser:

- dispatch différé pour les délais simples;
- commande de rattrapage périodique pour les exécutions manquées;
- scheduler Laravel pour agrégats, nettoyage et contrôle;
- verrou `withoutOverlapping` sur les opérations globales;
- traitement par lots bornés;
- curseur ou pagination stable.

### 41.7 Support praticien

Dans le produit:

- aide contextuelle courte;
- checklist de publication;
- exemples adaptés au type de parcours;
- diagnostic de configuration;
- bouton contacter le support avec identifiant du parcours;
- export du diagnostic sans données personnelles;
- historique visible des messages échoués avec action de correction.

Guide support interne:

- vérifier état et version;
- vérifier ressource source;
- simuler le parcours;
- voir l'exécution d'automatisation;
- voir la suppression/consentement;
- relancer seulement une action explicitement sûre;
- ne jamais modifier directement les données de conversion sans audit.

## 42. Stratégie de tests et qualité

### 42.1 Tests unitaires

- validation du graphe de parcours;
- transitions;
- publication et snapshot;
- normalisation contact;
- déduplication;
- résolution de consentement;
- décision d'envoi;
- règles d'entrée/sortie;
- attribution;
- calcul des métriques;
- quotas;
- idempotence;
- changements d'état conversion;
- redirections de slug.

### 42.2 Tests de fonctionnalité Laravel

- CRUD avec licence Premium;
- refus d'accès inter-utilisateur;
- publication d'un brouillon valide/invalide;
- rendu public de la version publiée uniquement;
- capture avec et sans consentement;
- rate limiting;
- rattachement à un contact existant;
- redirection vers réservation;
- réception d'un signal de conversion;
- arrêt d'une séquence après conversion;
- désinscription;
- export et suppression;
- quotas;
- pause globale;
- ressource source supprimée;
- URL historique.

### 42.3 Tests de contrat avec les domaines existants

Pour chaque adaptateur:

- URL générée valide;
- paramètres d'attribution acceptés;
- conversion reçue à partir d'un événement réel;
- annulation/remboursement répercuté;
- propriété et permissions respectées;
- aucune modification de la règle métier existante;
- comportement si la ressource est désactivée.

### 42.4 Tests de jobs

- envoi au bon délai;
- fuseau horaire;
- heures silencieuses;
- retry temporaire;
- échec permanent;
- job dupliqué;
- job après pause;
- job après désinscription;
- job après conversion;
- job lié à une ancienne version;
- quota atteint;
- suppression du contact avant exécution.

### 42.5 Tests navigateur de bout en bout

Scénarios minimum:

1. créer et publier un parcours séance;
2. visiter, cliquer, réserver et voir la conversion;
3. créer un lead magnet, demander le contenu et recevoir l'email;
4. se désinscrire puis vérifier qu'aucune relance n'est envoyée;
5. inscrire à un événement payant;
6. acheter une formation ou un bon selon les flux disponibles;
7. modifier un brouillon sans changer la version publique;
8. publier une nouvelle version;
9. utiliser le module sur mobile;
10. vérifier qu'un compte non Premium ne publie pas.

### 42.6 Régression de l'existant

À chaque phase:

- réservation publique sans parcours;
- paiement rendez-vous;
- inscription événement;
- formation digitale;
- bon cadeau;
- newsletter et désinscription;
- dossier client;
- profil public;
- navigation desktop et mobile;
- `php artisan route:cache` puis `route:clear`;
- compilation et cache des vues;
- tests actuels des domaines touchés.

### 42.7 Sécurité

- IDOR entre praticiens;
- accès au brouillon sans signature;
- XSS dans chaque bloc;
- injection dans paramètres de tracking;
- CSRF;
- spam et rate limit;
- fichier malveillant;
- webhook falsifié;
- replay de webhook;
- énumération d'email;
- lien de désinscription d'un autre contact;
- export non autorisé;
- fuite dans logs.

### 42.8 Performance et charge

Tests:

- page publique avec images et blocs maximum;
- pic de trafic vers un lead magnet;
- soumissions simultanées;
- planification de milliers d'actions;
- envoi par lots;
- agrégation statistique;
- requêtes de la liste de contacts;
- nettoyage/rétention;
- webhook dupliqué;
- reprise après indisponibilité du worker.

### 42.9 Validation produit

Avant chaque ouverture:

- cinq praticiens accomplissent le scénario sans assistance directe;
- temps médian de création mesuré;
- points de confusion enregistrés;
- pages testées par de vrais visiteurs;
- emails relus sur mobile et principaux clients;
- conformité relue;
- support formé;
- tableau de bord opérationnel disponible.

## 43. Déploiement progressif sans perturber l'application

### 43.1 Phase 0 - Prototype isolé

- routes derrière flag et utilisateurs internes;
- tables additives;
- aucune entrée de navigation générale;
- fausses données uniquement;
- page publique non indexée;
- emails vers adresses de test;
- adaptateur de réservation en environnement local/staging.

Sortie de phase:

- création/publication fonctionne;
- version publiée immuable;
- aucun impact sur les flux existants;
- tests d'appartenance passent.

### 43.2 Phase 1 - Alpha interne

- équipe Olithea et comptes explicitement autorisés;
- un seul type: séance ou lead magnet;
- domaine Olithea;
- quotas bas;
- suivi opérationnel quotidien;
- formulaire de retour intégré.

Sortie:

- aucune perte de contact;
- aucun double envoi;
- attribution réservation fiable;
- support capable de diagnostiquer.

### 43.3 Phase 2 - Pilote praticiens

- 10 à 20 praticiens volontaires;
- métiers variés;
- parcours réel;
- accompagnement de création;
- comparaison avec leur processus actuel;
- activation progressive des modèles.

Critères:

- au moins 70 % publient un parcours;
- création médiane inférieure à 20 minutes avec contenu prêt;
- au moins 50 % obtiennent une action réelle pendant le pilote, sous réserve de trafic;
- taux d'échec email inférieur au seuil fournisseur;
- aucun incident de confidentialité sévère;
- aucun impact mesurable sur réservation/paiement hors parcours.

### 43.4 Phase 3 - Bêta Premium

- feature visible aux Premium;
- inscription volontaire;
- limites documentées;
- modèles V1 complets;
- page d'utilisation;
- support et FAQ;
- suivi hebdomadaire.

### 43.5 Phase 4 - Disponibilité générale

Prérequis:

- conformité validée;
- délivrabilité stabilisée;
- alertes et kill switches testés;
- documentation;
- quotas et coûts décidés;
- sauvegarde/restauration testée;
- parcours de suppression;
- métriques produit disponibles;
- offre commerciale et communication prêtes.

### 43.6 Migration et compatibilité

Il n'existe pas de migration destructive de l'existant.

Possibilités:

- transformer une prestation existante en source d'un nouveau parcours;
- transformer un événement, une formation ou un bon en source;
- ajouter le parcours au profil;
- conserver l'URL existante de la ressource;
- désactiver le parcours sans désactiver la ressource;
- supprimer le parcours sans supprimer sa source.

### 43.7 Import depuis Systeme.io V3

Objectif raisonnable:

- import CSV contacts;
- import d'une séquence email à partir d'un format documenté;
- import manuel assisté du contenu de page;
- redirection d'anciennes URLs si domaine contrôlé;
- rapport de ce qui n'a pas été importé.

Ne pas promettre une copie parfaite d'un builder tiers.

## 44. Roadmap de livraison et dépendances

Cette roadmap approfondie est la référence de livraison. Elle précise et remplace le découpage résumé de la section 20 lorsqu'un niveau de phase diffère.

### 44.1 V0 - Fondations

Épics:

- configuration/feature flags;
- modèles, policies et migrations;
- versionnage/publication;
- rendu public minimal;
- adaptateur de source;
- événements analytiques;
- tests d'isolation.

Dépendances:

- décision de nommage;
- convention d'événements métier;
- choix de stockage des assets;
- licence Premium.

### 44.2 V1.0 - Offre vendable

Épics:

- accueil module;
- assistant six objectifs;
- éditeur guidé;
- page publique;
- formulaire et contact;
- page de merci;
- trois suivis automatiques;
- désinscription;
- statistiques essentielles;
- partage/QR;
- adaptateurs séance, événement, formation, bon, lead magnet et contact;
- publication et pause;
- support interne.

### 44.3 V1.5 - Véritable parcours

Épics:

- pages multiples;
- transitions;
- carte du parcours;
- liens de campagne;
- snapshots par page;
- modèles multi-étapes;
- analytics par étape;
- bibliothèque de blocs;
- meilleur SEO/social.

### 44.4 V2 - Automatisation et pipeline

Épics:

- moteur déclencheur-condition-action;
- éditeur visuel borné;
- tags/segments;
- pipeline;
- tâches praticien;
- règles de réentrée/sortie;
- campagnes réutilisables;
- rapports avancés.

### 44.5 V2.5 - Monétisation avancée

Épics:

- packs et échéanciers compatibles;
- option complémentaire;
- offre post-conversion;
- replay/webinaire permanent;
- vitrine d'offres;
- intégration communauté;
- suivi de revenu net.

### 44.6 V3 - Besoins avancés

Épics:

- A/B testing;
- domaines personnalisés;
- affiliation/ambassadeurs;
- SMS;
- webhooks/API;
- import;
- équipe et validation;
- assistant de rédaction;
- recommandations.

### 44.7 Dépendances critiques

Ordre obligatoire:

1. identité contact et consentement avant automatisation;
2. publication versionnée avant éditeur riche;
3. événements métier fiables avant statistiques de conversion;
4. suppression globale avant volume email;
5. observabilité avant bêta large;
6. facturation fiable avant ventes complémentaires;
7. transitions multi-pages avant A/B testing;
8. limites et coûts avant promesse commerciale illimitée.

## 45. Découpage en épics implémentables

### Epic A - Socle isolé

Livrables:

- fichier de routes dédié;
- configuration et flags;
- permission `sales_funnels`;
- modèles et policies;
- service de publication;
- contrôleurs namespacés;
- tests d'isolation.

Terminé quand le module peut être activé pour un utilisateur pilote et totalement masqué pour les autres.

### Epic B - Pages et publication

Livrables:

- assistant initial;
- blocs V1;
- éditeur;
- prévisualisation;
- snapshots;
- rendu public;
- statut pause/archive;
- redirections de slug.

### Epic C - Sources et conversions

Livrables:

- contrat d'adaptateur;
- adaptateurs métier;
- transmission attribution;
- consommation des conversions;
- idempotence;
- annulation/remboursement;
- tests de contrat.

### Epic D - Contacts et consentement

Livrables:

- formulaire;
- contact et entrée;
- déduplication;
- registre de consentement;
- suppression/désinscription;
- liste et fiche contact;
- export/suppression.

### Epic E - Messages V1

Livrables:

- modèles;
- chronologie de trois messages;
- planification;
- décision d'envoi;
- tests;
- suivi de statut;
- quotas;
- bounces et suppressions.

### Epic F - Mesure

Livrables:

- événement public et serveur;
- liens de campagne;
- agrégats;
- entonnoir;
- conversions/revenu;
- exclusion tests/robots;
- export.

### Epic G - Expérience et partage

Livrables:

- accueil module;
- vue d'ensemble;
- checklist publication;
- partage/QR;
- responsive;
- accessibilité;
- états vides/erreurs;
- aide.

### Epic H - Exploitation

Livrables:

- dashboard interne;
- logs structurés;
- alertes;
- kill switches;
- modération;
- outils support;
- procédures incident.

### Estimation relative

| Epic | Taille relative | Risque principal |
|---|---|---|
| A - Socle | M | isolation et propriété |
| B - Pages | XL | éditeur, versions et rendu |
| C - Sources | L | contrats avec flux existants |
| D - Contacts | L | déduplication et RGPD |
| E - Messages | XL | consentement et délivrabilité |
| F - Mesure | L | attribution fiable |
| G - Expérience | L | simplicité malgré l'étendue |
| H - Exploitation | M | diagnostic et sécurité |

Une estimation calendrier nécessite la taille de l'équipe, les disponibilités et une revue technique. Cette spécification évite de fabriquer une date sans ces données.

## 46. Critères d'acceptation approfondis

### 46.1 Isolation

- Le module désactivé n'ajoute aucune route publique exploitable et aucune navigation visible.
- Les rendez-vous, événements, formations, bons et paiements continuent de fonctionner sans parcours.
- Supprimer un parcours ne supprime aucune ressource métier.
- Un utilisateur ne peut ni voir ni modifier le parcours, contact ou statistique d'un autre praticien.
- Les migrations V1 sont additives.
- Une panne du tracking n'empêche pas l'action publique principale.

### 46.2 Création

- Un praticien Premium peut créer chaque type V1 depuis une ressource compatible.
- L'assistant conserve l'état lorsqu'il passe par la création d'une ressource.
- Les données préremplies peuvent être adaptées sans modifier la source.
- Un parcours incomplet reste brouillon.
- Le système explique chaque prérequis manquant.

### 46.3 Édition et publication

- Les modifications sont sauvegardées comme brouillon.
- La version publique reste inchangée tant que le praticien ne republie pas.
- La checklist bloque une publication incohérente.
- Un aperçu signé permet de tester sans indexation.
- Une version précédente peut être restaurée.
- Changer le slug crée une redirection depuis l'ancien.

### 46.4 Public

- La page charge et reste exploitable sans script analytique.
- Le contenu est adapté mobile.
- Le CTA mène à la ressource autorisée.
- Le formulaire résiste au double envoi.
- Les erreurs ne révèlent pas l'existence d'un contact.
- Une page en pause ne collecte plus de contact.
- Une source indisponible n'entraîne pas une erreur serveur brute.

### 46.5 Contact et consentement

- Un email déjà connu du même praticien ne crée pas de doublon.
- Aucun contact n'est fusionné entre praticiens.
- Le texte et la date du consentement sont conservés.
- Le marketing est indépendant de la livraison demandée.
- Le retrait est immédiat.
- Une suppression est propagée aux actions futures.
- Un téléchargement simple ne crée pas automatiquement un dossier client complet.

### 46.6 Automatisation

- Chaque action est exécutée au maximum une fois.
- Un retry ne duplique pas un email, un tag ou un accès.
- Une conversion arrête la séquence prévue.
- Une désinscription empêche les messages marketing.
- Une pause empêche les nouvelles actions.
- Les délais respectent le fuseau et les heures silencieuses.
- Une erreur permanente est visible et diagnostiquable.

### 46.7 Conversion

- Une réservation confirmée est attribuée quand le contexte est valide.
- Un retour navigateur sans confirmation serveur ne suffit pas pour un paiement.
- Une annulation ou un remboursement ajuste la statistique.
- La même conversion n'est pas comptée deux fois.
- Le praticien peut ouvrir l'objet métier correspondant.
- L'attribution manquante n'empêche jamais la transaction métier.

### 46.8 Statistiques

- Les prévisualisations et tests sont exclus.
- Les définitions sont visibles.
- Les filtres de période et campagne sont cohérents.
- Le revenu ne compte que les conversions confirmées, ajustées des remboursements.
- Les chiffres de la liste et du détail correspondent.
- Une donnée partielle est signalée.

### 46.9 Email

- L'expéditeur et le reply-to sont corrects.
- Le message contient une version texte.
- Les variables sont résolues ou bloquées avant activation.
- Le lien de désinscription fonctionne sans connexion.
- Bounce et plainte alimentent la suppression.
- Le quota n'empêche pas les confirmations transactionnelles indispensables.
- Les tests n'entrent pas dans les statistiques commerciales.

### 46.10 Accessibilité et qualité

- Les scénarios principaux sont réalisables au clavier.
- Les erreurs de formulaire sont annoncées et reliées aux champs.
- Le contraste respecte l'objectif AA.
- Le rendu est vérifié aux largeurs définies.
- Les budgets de performance sont contrôlés.
- Aucun texte ne déborde d'un bouton ou d'une carte.

## 47. Indicateurs produit et économiques

### 47.1 Activation

- pourcentage de Premium qui ouvrent le module;
- pourcentage qui créent un brouillon;
- pourcentage qui publient;
- temps création vers publication;
- nombre d'étapes où les praticiens abandonnent;
- modèle choisi.

### 47.2 Usage

- parcours actifs par praticien;
- part des parcours ayant du trafic;
- liens de campagne créés;
- contacts capturés;
- séquences activées;
- connexions hebdomadaires au tableau de bord;
- modifications après publication.

### 47.3 Valeur praticien

- réservations attribuées;
- ventes attribuées;
- revenu attribué;
- taux contact vers conversion;
- temps économisé déclaré;
- outils externes abandonnés;
- intention de renouvellement Premium;
- satisfaction du module.

### 47.4 Santé plateforme

- bounces;
- plaintes;
- désinscriptions;
- spam formulaire;
- coût email par compte;
- stockage par compte;
- jobs échoués;
- délai moyen d'automatisation;
- tickets support par parcours publié.

### 47.5 North star

Métrique principale proposée:

> Nombre mensuel de parcours actifs ayant généré au moins une action utile confirmée: contact qualifié, réservation, inscription ou vente.

Elle évite d'optimiser seulement la création de pages ou les vues.

### 47.6 Garde-fous

- taux de plainte email;
- taux de désinscription;
- incidents de confidentialité;
- promesses signalées;
- taux de remboursement;
- impact sur performance publique;
- impact sur taux d'erreur des flux existants.

## 48. Offre commerciale Premium

### 48.1 Packaging proposé

Starter:

- découverte de la fonctionnalité;
- aperçu des modèles;
- pas de publication.

Pro:

- un parcours simple publié;
- pas de séquence marketing ou un seul message de livraison;
- statistiques 30 jours;
- branding Olithea standard.

Premium:

- jusqu'à 10 parcours actifs au lancement;
- contacts et formulaires;
- trois messages par parcours en V1;
- 2 000 emails marketing par mois comme hypothèse à valider;
- statistiques 12 mois;
- QR et liens de campagne;
- modèles Premium;
- support prioritaire raisonnable.

Options futures:

- volume de contacts/emails supplémentaire;
- domaine personnalisé;
- SMS;
- équipe/cabinet;
- programme ambassadeur.

### 48.2 Message de vente

> Vous avez déjà vos prestations, votre agenda et vos clients dans Olithea. Avec les Parcours d'offre, vous pouvez aussi créer la page qui présente chaque accompagnement, recueillir les personnes intéressées et automatiser un suivi simple jusqu'à la réservation ou l'inscription.

Preuve à construire avant lancement commercial:

- exemples réels de parcours;
- temps moyen de mise en ligne;
- nombre de contacts/réservations pilotes;
- témoignages vérifiés;
- démonstration complète en vidéo;
- comparaison honnête avec l'usage d'outils séparés.

### 48.3 Positionnement face à Systeme.io

Ne pas dire:

- `Olithea fait tout ce que fait Systeme.io`;
- `automatisation illimitée`;
- `multipliez vos ventes` sans preuve;
- `aucune connaissance nécessaire` si l'offre doit quand même être rédigée.

Dire:

- `Conçu pour les praticiens et leurs offres`;
- `Relié à votre agenda, vos prestations, ateliers et formations`;
- `Un parcours guidé, sans assemblage d'outils`;
- `Des suivis simples et mesurables`;
- `Vos pages et vos contacts dans le même espace que votre activité`.

## 49. Décisions à prendre avant développement

### Bloquantes

1. Nom final utilisateur: `Parcours d'offre` recommandé.
2. Préfixe technique: `offer_journey_*` ou `sales_funnel_*`.
3. Plan(s) autorisés et limites exactes.
4. Premier type pilote: lead magnet recommandé pour tester capture/email, ou séance pour tester avantage métier.
5. Politique de consentement validée.
6. Fournisseur et configuration d'envoi marketing.
7. Modèle d'attribution et fenêtre.
8. Événements métier disponibles pour les conversions.
9. Stockage et protection des fichiers.
10. Position de navigation desktop/mobile.

### Non bloquantes pour prototype

1. Domaine personnalisé.
2. A/B testing.
3. SMS.
4. Affiliation.
5. import Systeme.io.
6. builder visuel complet.
7. options complémentaires.
8. personnalisation avancée du thème.

### Données business à fournir

- prix et structure actuelle des plans;
- coût cible par praticien actif;
- volume d'emails acceptable;
- capacité support;
- métiers prioritaires;
- 10 praticiens pilotes;
- exemples d'offres réelles;
- témoignages utilisables;
- politique de modération;
- validation juridique.

## 50. Définition de terminé du produit V1.0

La V1.0 est terminée lorsque:

1. un praticien Premium pilote peut choisir un objectif et une ressource;
2. il peut produire une page d'offre crédible avec un assistant guidé;
3. il peut contrôler le formulaire, le consentement et trois suivis;
4. il peut tester puis publier une version immuable;
5. il peut partager une URL stable ou un QR;
6. un visiteur mobile peut comprendre l'offre et agir sans compte;
7. un contact peut être capturé, dédupliqué et désinscrit;
8. une réservation, inscription ou vente confirmée peut être attribuée;
9. une conversion arrête les relances prévues;
10. le praticien voit contacts, conversions et sources;
11. l'équipe peut diagnostiquer, pauser et modérer;
12. le module peut être désactivé sans casser l'existant;
13. les tests de sécurité, accessibilité, performance et régression passent;
14. la conformité et les textes sont validés;
15. le pilote démontre que le parcours principal peut être opéré sans outil externe.

Cette définition est volontairement plus exigeante qu'un CRUD de landing pages. Elle décrit une fonctionnalité Premium commercialisable, exploitable et extensible.

## 51. Recommandation de lancement

Construire le produit en deux mouvements:

1. V1.0 guidée: une page, une action, une confirmation, trois suivis et une mesure fiable.
2. V1.5 multi-étapes: plusieurs pages, transitions, campagnes et analyse des abandons.

Le modèle de données, la publication, l'identité contact et les événements doivent être conçus pour le deuxième mouvement dès le premier. L'interface V1 peut rester simple.

Premier modèle pilote recommandé:

> Ressource gratuite vers séance découverte.

Il éprouve presque tout le socle:

- page;
- formulaire;
- consentement;
- fichier ou contenu;
- email;
- délai;
- réservation;
- conversion;
- attribution;
- arrêt de séquence;
- statistiques.

Deuxième modèle:

> Atelier vers séance ou formation.

Troisième modèle:

> Page directe de prestation vers réservation.

La réussite ne sera pas d'avoir « un mini-Systeme.io ». La réussite sera qu'un praticien dise:

> J'ai présenté mon offre, partagé mon lien, reçu des demandes, suivi les personnes et obtenu des réservations sans quitter Olithea ni apprendre un outil marketing supplémentaire.
