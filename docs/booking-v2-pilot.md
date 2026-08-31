# Déploiement Booking V2

## Portée

Lorsque le switch global est actif, Booking V2 ajoute pour tous les praticiens :

- une grille de départ fixe (15, 30, 45 ou 60 minutes) ou optimisée selon la prestation ;
- un temps de préparation et un battement après chaque prestation ;
- des messages propres à la prestation dans la confirmation et le rappel client ;
- la désactivation des demandes d'information du portail ;
- la sélection automatique du seul cabinet compatible avec la prestation choisie ;
- un accès direct aux créneaux depuis chaque prestation du portail.

Les rendez-vous existants ne sont ni modifiés ni backfillés. Les champs nullable gardent le comportement historique. La durée visible, Google Calendar, Stripe, les packs, les factures et les liens visio ne comprennent jamais les buffers.

## Décisions de compatibilité

- Tous les écrans et règles V2 passent par un gate serveur central. Lorsque le switch est désactivé, tous les comptes conservent les contrôleurs, formulaires et créneaux historiques.
- Les nouveaux rendez-vous V2 mémorisent les buffers et messages effectivement utilisés. Une modification ultérieure de la prestation ne réécrit pas ces rendez-vous.
- Un ancien rendez-vous replanifié avec Booking V2 actif applique les règles courantes uniquement après validation du nouveau créneau.
- Le mode réellement choisi est conservé pour les prestations proposant plusieurs formats, sans modifier le helper historique utilisé lorsque le switch est désactivé.
- Le dernier contrôle de création et de déplacement est transactionnel. Les deux parcours utilisent les mêmes verrous par praticien et, au cabinet, par lieu afin d’éviter les réservations concurrentes, y compris dans les cabinets partagés.
- Une prestation publique supprimée, déplacée vers un autre compte ou rendue non réservable est refusée lors d'une soumission obsolète. Un lien partenaire conserve sa propre liste de prestations autorisées.
- Les cabinets proposés sont accessibles au praticien et associés à une disponibilité de la prestation. Zéro cabinet affiche une explication, un cabinet est sélectionné automatiquement, plusieurs cabinets conservent le choix.

## Configuration

```dotenv
BOOKING_V2_ENABLED=false
```

`BOOKING_V2_ENABLED` est un switch global : `true` active Booking V2 pour tous les praticiens et `false` le désactive pour tous.

Activation globale :

```dotenv
BOOKING_V2_ENABLED=true
```

## Déploiement

1. Déployer le code avec `BOOKING_V2_ENABLED=false`.
2. Exécuter `php artisan migrate --force`.
3. Exécuter `php artisan optimize:clear` puis `php artisan config:cache`.
4. Redémarrer les workers avec `php artisan queue:restart`.
5. Tant que le switch est désactivé, vérifier les créneaux d'un praticien : ils doivent rester identiques.
6. Avant l'activation globale, contrôler les disponibilités hebdomadaires et ponctuelles de tous les praticiens :

```bash
php artisan app:backfill-availability-locations --dry-run
```

7. Si la sortie est correcte, affecter uniquement les disponibilités sans cabinet au cabinet principal de chaque praticien :

```bash
php artisan app:backfill-availability-locations
php artisan app:backfill-availability-locations --dry-run
```

Le dernier dry-run doit annoncer zéro modification. La commande ne modifie ni les rendez-vous, ni les disponibilités qui possèdent déjà un cabinet. Si un praticien n'a pas de cabinet principal, elle ne change rien et affiche un avertissement : il faut d'abord choisir explicitement le bon cabinet dans Olithea.

8. Mettre `BOOKING_V2_ENABLED=true`, puis reconstruire le cache de configuration et redémarrer les workers.
9. Effectuer la checklist navigateur ci-dessous avec des prestations de test gratuites sur plusieurs comptes représentatifs.
10. Surveiller les files, les échecs et les logs après l'activation.

Le changement ne nécessite ni nouveau paquet Composer, ni nouvelle dépendance JavaScript, ni compilation Vite.

## Kill switch

Mettre `BOOKING_V2_ENABLED=false`, puis exécuter :

```bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```

Aucun rollback de migration n'est nécessaire. Les rendez-vous créés sous V2 conservent leurs snapshots de buffers, qui restent honorés par les contrôles bas niveau même lorsque l'interface V2 est coupée.

## Checklist navigateur en production

1. Switch désactivé : vérifier que réglages et formulaires n'affichent aucun champ V2 et que la grille reste à 15 minutes.
2. Switch activé : tester les intervalles 15, 30, 45 et 60 minutes sur une disponibilité commençant à une heure non ronde.
3. Tester le mode optimisé avec deux prestations de 45 et 90 minutes sur la même disponibilité.
4. Ajouter préparation et battement, puis vérifier qu'ils bloquent les horaires sans changer la durée affichée ni l'événement Google.
5. Réserver depuis le portail, le lien public normal, le lien partenaire et un téléphone.
6. Sans cabinet compatible, vérifier le message bloquant. Avec un cabinet unique, vérifier la sélection automatique, le nom et l'adresse. Avec deux cabinets, vérifier que le choix reste visible et obligatoire.
7. Cliquer sur chaque variante d'une prestation groupée et vérifier prix, durée et mode sélectionnés.
8. Vérifier les emails de confirmation et de rappel, leur contenu personnalisé et le Reply-To du praticien.
9. Déplacer le rendez-vous depuis le lien client puis depuis l'interface praticien. Vérifier même ID, même token, paiement inchangé, visio inchangée et Google mis à jour sans doublon.
10. Annuler côté client puis praticien. Vérifier capacité libérée, disparition des rendez-vous à venir, suppression Google et absence de rappel.
11. Activer « Me prévenir si un rendez-vous plus tôt se libère », libérer un créneau compatible et vérifier l'offre. Faire réclamer le même créneau par deux clients et vérifier le message clair du perdant.
12. Désactiver les demandes d'information : aucun bouton ni modal ne doit rester, et un ancien POST doit être refusé sans stockage ni email.
13. Contrôler `php artisan queue:monitor database:100`, `php artisan queue:failed` et les logs après les essais.

## Règle de conflit

Pour deux rendez-vous A puis B, l'écart exigé est le maximum entre le battement après A et la préparation avant B. Les valeurs historiques null utilisent le buffer général existant. Cette règle évite de compter deux fois le même temps.

## Limites connues

- Booking V2 n'ajoute aucune personnalisation des numéros de facture.
- Les buffers sont des contraintes Olithea et ne créent pas de faux rendez-vous dans Google.
- L'envoi d'un email de test depuis la fiche prestation n'est pas inclus ; l'aperçu local est disponible avant enregistrement.
