@component('mail::message')
# Bonjour les Lapiz,


Un nouvel utilisateur vient de s'inscrire sur AromaMade PRO.

**Détails de l'utilisateur :**

- **Nom :** {{ $user->name }}
- **Email :** {{ $user->email }}



Cordialement,

Le CLO (Chief Lapin Officier),<br>
{{ config('app.name') }}
@endcomponent
