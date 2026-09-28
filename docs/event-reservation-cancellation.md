# Annulation des réservations d’événement

Sur ordinateur et mobile, chaque participant actif dispose d’un bouton **Annuler la réservation**. Une confirmation affiche son nom et précise que sa place sera libérée. Aucun motif ni formulaire supplémentaire n’est demandé.

La réservation reste conservée dans la section repliée **Réservations annulées ou non abouties**. Les paiements et leurs références sont conservés. L’annulation n’effectue aucun remboursement et n’envoie pas d’email supplémentaire ; les confirmations et rappels encore en attente sont bloqués.

Les réservations annulées ne comptent plus dans les places occupées, ne bloquent plus une nouvelle inscription du même client et ne sont pas recopiées lors d’une duplication d’événement. Une nouvelle inscription crée une nouvelle réservation afin de conserver l’historique.

L’annulation est indépendante du statut de paiement : un paiement reçu ensuite est enregistré sans réactiver la participation. Les sessions de paiement encore ouvertes sont expirées par un job avec reprises en cas d’erreur Stripe. Un paiement déjà engagé peut encore aboutir ; son montant apparaît dans l’historique pour gérer le remboursement séparément.

## Déploiement

1. Exécuter `php artisan migrate --force` pour ajouter `cancelled_at` et `cancelled_by` aux réservations.
2. Déployer les fichiers applicatifs et les assets produits par `npm run build`.
3. Redémarrer les workers avec `php artisan queue:restart` ; le worker existant traite aussi l’expiration des sessions Checkout.
4. Ajouter `checkout.session.expired` et `checkout.session.async_payment_failed` aux événements reçus par le webhook Stripe existant, pour libérer aussi les places des paiements abandonnés ou échoués. Conserver les événements de paiement réussi déjà configurés.

Le retour public d’annulation de paiement utilise désormais une URL signée. Les anciennes URL non signées restent consultables mais ne modifient plus les réservations.

La migration est additive et préserve les réservations existantes. Un retour arrière du schéma supprime l’historique des nouvelles annulations : éviter de revenir à l’ancien code après utilisation de cette fonction.

## Vérification

`EventReservationCancellationTest` couvre les droits, l’annulation répétée, les écrans ordinateur/mobile, les capacités, les réinscriptions, la duplication, les emails déjà en attente, les callbacks de paiement tardifs, l’expiration Stripe simulée et la conservation des conversions marketing. Tous les appels Stripe de ces tests sont simulés.

Sur l’environnement Windows où Pest ne charge pas automatiquement son fichier d’amorçage, utiliser `--bootstrap=tests/Pest.php`. Les tests doivent utiliser une base dédiée ; les vérifications locales utilisent `TEST_DB_CONNECTION=sqlite` et `TEST_DB_DATABASE=:memory:`.
