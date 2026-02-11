@component('mail::message')
# Bonjour les Lapiz 🐰


Un nouvel utilisateur vient de s'inscrire sur **AromaMade PRO**.

**Détails de l'utilisateur :**

- **Nom :** {{ $user->name }}
- **Email :** {{ $user->email }}
- **Mode d’onboarding :**
  @if($user->onboarding_mode === 'assisted')
      🤝 Accompagné — souhaite un appel / une visio pour une mise en place guidée
  @elseif($user->onboarding_mode === 'self')
      🧭 Autonome — découvre la plateforme par lui-même
  @else
      ❓ Non précisé
  @endif


@component('mail::panel')
@if($user->onboarding_mode === 'assisted')
👉 **Action recommandée :**  
Prévoir un contact rapide (appel ou visio) pour accompagner la configuration et maximiser l’activation.
@else
ℹ️ Aucun accompagnement demandé pour le moment.
@endif
@endcomponent


Cordialement,

Le CLO (Chief Lapin Officier),  
{{ config('app.name') }}
@endcomponent
