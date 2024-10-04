@component('mail::message')
# Bienvenue chez {{ config('app.name') }} !

Bonjour {{ $user->name }},

Nous sommes ravis de vous accueillir parmi nos professionnels sur **{{ config('app.name') }}**.

**Bonne nouvelle !** Vous bénéficiez dès maintenant d'une **période d'essai gratuite de 15 jours** pour découvrir toutes les fonctionnalités de notre plateforme. Vous pouvez à tout moment passer à la version Pro depuis votre profil.

## Voici ce que vous pouvez faire dès aujourd'hui :

- **Gérer vos rendez-vous** : planifiez et suivez vos consultations facilement.
- **Accéder à des ressources exclusives** : profitez de contenus réservés aux professionnels.
- **Créer votre profil public** : augmentez votre visibilité auprès de nouveaux clients.
- **Communiquer avec vos clients** : utilisez notre messagerie intégrée pour rester en contact.
- **Analyser vos performances** : accédez à des statistiques détaillées pour améliorer votre activité.
- **Et bien plus encore !**

## Pour bien démarrer :

1. **Complétez votre profil** en ajoutant vos informations professionnelles.
2. **Configurez vos disponibilités** pour que les clients puissent réserver des rendez-vous.
3. **Explorez les ressources** disponibles dans votre espace.
4. **Personnalisez vos paramètres** pour adapter la plateforme à vos besoins.

@component('mail::button', ['url' => route('dashboard-pro')])
Accéder à mon espace pro
@endcomponent

Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter ou à consulter notre [Centre d'aide]({{ route('dashboard-pro') }}).

Nous vous souhaitons une excellente expérience sur **{{ config('app.name') }}** !

Cordialement,

L'équipe {{ config('app.name') }}
@endcomponent
