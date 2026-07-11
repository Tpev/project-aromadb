# Parcours d’offre - cadre juridique et conservation à valider

Statut: **brouillon produit, validation DPO/conseil juridique obligatoire avant ouverture large**.

## Répartition des rôles proposée

- Le praticien détermine l’objectif de son parcours, les données demandées, les destinataires, les textes, les campagnes et les durées utiles à sa relation professionnelle.
- Olithea fournit l’hébergement, les formulaires, les journaux de consentement, les suppressions, les automatisations et les outils de conservation selon les instructions configurées.
- Les responsabilités exactes de responsable de traitement, sous-traitant ou responsables conjoints doivent être confirmées dans les conditions contractuelles et le registre de traitement.
- Les notes cliniques et données de santé ne doivent pas être collectées par les formulaires marketing du module.

## Textes à faire valider

### Réponse à une demande

> Les informations saisies sont utilisées par le praticien pour répondre à cette demande. Les champs obligatoires sont nécessaires au traitement de votre demande.

### Relances facultatives

> J’accepte de recevoir par email des informations liées à cette offre. Je peux retirer mon consentement à tout moment grâce au lien de désinscription présent dans chaque message.

Ces textes sont des propositions fonctionnelles. La version validée doit être renseignée dans les variables `OFFER_JOURNEYS_REQUEST_PRIVACY_TEXT`, `OFFER_JOURNEYS_MARKETING_CONSENT_TEXT` et identifiée par `OFFER_JOURNEYS_CONSENT_TEXT_VERSION`.

## Politique de conservation proposée

| Donnée | Valeur technique par défaut | Décision à valider |
|---|---:|---|
| Contact marketing sans activité | 1 095 jours | durée commerciale appropriée et mécanisme de renouvellement |
| Événement analytique | 395 jours | durée nécessaire aux comparaisons saisonnières |
| Journal de livraison email | 395 jours | durée support et preuve opérationnelle |
| Preuve de consentement | 1 825 jours | prescription applicable et preuve du retrait |

Les contacts liés à un dossier client existant ne sont jamais anonymisés automatiquement par cette politique marketing. Les rendez-vous, factures, achats et dossiers clients sont hors du périmètre de cette purge.

## Procédure de validation

1. confirmer les rôles contractuels;
2. valider les deux textes et leur version;
3. valider chaque durée et le point de départ;
4. exécuter `php artisan offer-journeys:apply-retention --dry-run` en production;
5. faire relire le rapport par l’équipe Olithea;
6. activer la conservation sur un faible volume;
7. documenter les demandes d’accès, de retrait, d’opposition et d’effacement.
