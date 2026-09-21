# Réservation, messages d’événement et liens privés de packs

## Utilisation

- **Prise de rendez-vous** : un encadré visible invite le client à sélectionner le mode de consultation avant d’afficher les créneaux. Il disparaît après sélection et revient lorsque le choix est réinitialisé. Les liens partenaires utilisent le terme « format de consultation ».
- **Téléphone obligatoire** : option dans les paramètres du praticien, également disponible sur mobile. Désactivée par défaut, elle s’applique aux réservations publiques, mobiles et par lien partenaire. Les rendez-vous saisis par le praticien, les événements et les achats de packs conservent leurs règles propres.
- **Annulation client** : motif obligatoire, de 1 à 500 caractères après suppression des espaces superflus. Les boutons du portail dirigent vers le formulaire d’annulation. Le motif est enregistré, affiché dans le rendez-vous du praticien et transmis dans son email. Les délais d’annulation restent applicables.
- **Messages d’événement** : deux champs facultatifs, un pour la confirmation et un pour les rappels à 24 h et à 1 h. Chaque champ accepte 2 000 caractères, des retours à la ligne et des liens HTTP(S). Un aperçu est disponible dans les formulaires de création, modification et duplication, sur ordinateur et mobile. Les modifications concernent les futurs emails, sans renvoi des confirmations passées.
- **Pack privé** : activer « Activer un lien privé d’achat pour ce pack » dans le formulaire du pack, enregistrer puis copier le lien. Toute personne possédant le lien peut acheter ; le lien n’est pas lié à un client précis. La visibilité sur le portail est indépendante. Pour proposer un pack uniquement par lien, désactiver sa visibilité sur le portail. Désactiver le lien ou le pack empêche les nouveaux achats. Réactiver le lien conserve la même URL.

La page privée présente le descriptif, les prestations et formations incluses, ainsi que le prix. Elle propose le paiement en une fois et les échéanciers mensuels déjà configurés sur le pack. Un compte Stripe connecté et prêt à encaisser est obligatoire. La page est exclue de l’indexation et ne propose aucun autre produit.

## Fiabilité des paiements et des emails

- Les paiements d’événements sont confirmés par un webhook Stripe signé ou par le retour de paiement vérifié. Le compte, la devise, le montant et les identifiants Stripe doivent correspondre à la réservation.
- Un traitement en file d’attente suit séparément l’envoi de la confirmation client et de la notification au praticien. Les callbacks répétés ne renvoient pas les messages déjà transmis.
- Les réservations déjà payées avant le déploiement ne déclenchent pas un nouvel envoi sur réception d’un ancien callback. Les nouveaux paiements disposent d’un marqueur distinct pour permettre les reprises après une panne d’email.
- Les rappels d’événement excluent les réservations en attente de paiement, annulées ou échouées. L’état de la réservation et la date de l’événement sont revérifiés au moment de l’envoi.
- Les achats de packs utilisent le même traitement pour le retour Stripe et le webhook. Les confirmations répétées conservent les crédits consommés et ne dupliquent ni facture, ni encaissement, ni accès aux formations.
- Une facture d’échéance peut arriver avant la confirmation Checkout. La première échéance reste comptabilisée. Après la dernière échéance, l’arrêt des prélèvements conserve le pack et les accès acquis. Si Stripe est temporairement indisponible, l’arrêt est retenté lors de la nouvelle livraison du webhook.
- Les emails d’accès aux formations sont envoyés après la réussite de la transaction comptable. Les échecs comptables peuvent être rejoués sans compter deux fois une échéance.

## Déploiement

Exécuter la migration additive `2026_09_17_180000_add_booking_and_event_communication_settings.php` avant d’activer les nouvelles pages, puis redémarrer les workers après activation du nouveau code :

```sh
php artisan migrate --force
php artisan queue:restart
```

Déployer les ressources de `public/build` avec le code. Le scheduler et les workers de queue existants doivent rester actifs. Les endpoints Stripe existants traitent les confirmations d’événement ; aucune nouvelle clé Stripe n’est nécessaire. Les endpoints doivent recevoir `checkout.session.completed`, `checkout.session.async_payment_succeeded` et `payment_intent.succeeded`. Les événements de facturation des abonnements déjà utilisés restent nécessaires aux paiements en plusieurs fois.

Les tests automatisés couvrent les réservations sur les trois canaux, les paramètres desktop/mobile, les motifs d’annulation, les messages et rappels d’événement, la confirmation sans retour navigateur, les liens privés, les choix de paiement et la répétition des callbacks. Les appels Stripe sont simulés ; aucun paiement réel n’est effectué pendant les tests.

La répétition et l’ordre des callbacks suivent les recommandations publiques de [Stripe sur les webhooks](https://docs.stripe.com/webhooks). La vérification locale utilise SQLite ; elle ne constitue pas un essai sur la base de production ni sur un compte Stripe réel. Avant mise en production, vérifier le déploiement de la migration sur un environnement de staging utilisant le même moteur de base de données, ainsi que la configuration des événements reçus par les endpoints Stripe.

## Vérification du 18 septembre 2026

- Suite complète : **735 tests réussis, 4 448 assertions, 1 test ignoré**. Le test ignoré concerne une limitation préexistante du schéma SQLite pour les achats de formation sans pack.
- Migration : aller-retour testé sur une base de test contenant des données, avec conservation des réservations et des valeurs par défaut attendues.
- Compilation de production Vite, compilation des vues Blade et contrôle de syntaxe des 38 fichiers PHP modifiés ou ajoutés : réussis.
- Vérifications navigateur des vues concernées sur ordinateur et mobile : aucune erreur JavaScript ni débordement horizontal sur les huit pages contrôlées.
- Les simulations couvrent notamment les callbacks répétés ou désordonnés, les pannes temporaires de facturation et d’envoi d’email, les paiements reçus après un retour d’annulation, et la conservation des accès après la dernière échéance.

Aucune connexion à un compte Stripe ni modification de la base de production n’a été effectuée pendant cette vérification.
