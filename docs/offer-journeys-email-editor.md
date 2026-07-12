# Éditeur visuel des campagnes email

## Architecture

L’éditeur enrichit `OfferJourneyMessageCampaign`; il ne crée ni un second moteur de campagne ni une seconde audience.

- `body` reste le contenu historique et le fallback texte.
- `content_json` contient les blocs validés.
- `style_json` contient uniquement des choix visuels bornés.
- `OfferJourneyEmailRenderer` produit l’aperçu, l’email test, l’email final et la version texte.
- `OfferJourneyCampaignSender` conserve les contrôles de consentement, suppression, fréquence, réputation et idempotence.
- Les anciennes campagnes ne sont jamais converties automatiquement.

## Blocs disponibles

Titre, paragraphe, image, bouton, encadré, séparateur, espacement, informations pratiques et signature.

Le HTML libre, les scripts, SVG, iframes, formulaires, URL non HTTP(S) et variables inconnues sont refusés côté serveur. Les images sont converties en WebP, redimensionnées, isolées par campagne et praticien, puis publiées avec un nom non devinable.

## Activation pilote

Conserver la pause marketing pendant la préparation:

```env
OFFER_JOURNEYS_ALLOW_ALL=false
OFFER_JOURNEYS_BETA_USER_IDS=123
OFFER_JOURNEYS_CAMPAIGNS_ENABLED=true
OFFER_JOURNEYS_EMAIL_EDITOR_ENABLED=true
OFFER_JOURNEYS_EMAIL_ENABLED=false
OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS=true
```

Puis exécuter:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan queue:restart
```

Créer un brouillon, appliquer un modèle, modifier les blocs, ajouter une image et un bouton, contrôler les aperçus ordinateur/téléphone puis envoyer un test uniquement au praticien. Activer les emails réels seulement après validation du rendu et de la délivrabilité.

## Retour arrière

Mettre `OFFER_JOURNEYS_EMAIL_EDITOR_ENABLED=false`, reconstruire le cache de configuration et redémarrer les workers. Ne pas retirer la migration: les campagnes visuelles programmées restent enregistrées et sont suspendues avec `email_editor_disabled`; les campagnes texte historiques restent inchangées.

## Validation métier et juridique

- Valider les modèles et textes de consentement avant ouverture large.
- Ne pas utiliser les campagnes pour des données médicales ou des notes cliniques.
- Conserver une preuve de consentement indépendante des étiquettes et segments.
- Vérifier le domaine SES, SPF, DKIM, DMARC, les bounces et plaintes avant de lever la pause marketing.

## Limites V1 et pistes V2

- Bibliothèque d’images réutilisable entre campagnes.
- Sections personnelles réutilisables.
- Comparaison A/B des objets et contenus, après volumes suffisants.
- Matrice automatisée de captures dans davantage de clients email.
- Statistiques détaillées des clics par bouton.
- Duplication complète d’une campagne et calendrier éditorial enrichi.
- Collaboration et validation par un second membre du cabinet.
