@component('mail::message')
# Rapport Quotidien des KPIs

Bonjour les Lapiz,

{{-- Afficher un message festif si un nouveau record est atteint --}}
@if($kpis['isNewHigh'])
    <div style="text-align: center; margin-bottom: 20px;">
        {{-- GIF festif pour célébrer le nouveau record --}}
        <img src="https://media.giphy.com/media/xT8qBepJQzUjXpeWU8/giphy.gif" alt="Félicitations!" width="300" />
        <p><a href="https://giphy.com/gifs/olympics-shaun-the-sheep-aardman-xT8qBepJQzUjXpeWU8">via GIPHY</a></p>
        <p style="font-size: 18px; color: #28a745; font-weight: bold;">🎉 Nouveau Record Atteint ! 🎉</p>
        <p>Hier, vous avez atteint un nouveau record de **{{ $kpis['sessionsYesterday'] }}** sessions, dépassant l'ancien record de **{{ $kpis['lastHighCount'] }}** sessions (Le {{ \Carbon\Carbon::parse($kpis['lastHighDate'])->format('d/m/Y') }}).</p>
    </div>
@endif

Voici les KPIs pour le **{{ \Carbon\Carbon::now()->format('d/m/Y') }}** :

@component('mail::table')
| **KPI**                         | **Valeur**                                                           |
| ------------------------------- | -------------------------------------------------------------------- |
| **Sessions Aujourd'hui**        | {{ $kpis['sessionsToday'] }}                                         |
| **Sessions Hier**               | {{ $kpis['sessionsYesterday'] }}                                     |
| **Sessions Cette Semaine**      | {{ $kpis['sessionsThisWeek'] }}                                      |
| **Sessions Semaine Dernière**   | {{ $kpis['sessionsLastWeek'] }}                                      |
| **Sessions Ce Mois**            | {{ $kpis['sessionsThisMonth'] }}                                     |
| **Sessions Mois Dernier**       | {{ $kpis['sessionsLastMonth'] }}                                     |
| **Total des Sessions**          | {{ $kpis['sessionsTotal'] }}                                         |
| **Record de Sessions**          | {{ $kpis['highestSessionCount'] }} (Le {{ \Carbon\Carbon::parse($kpis['highestSessionDate'])->format('d/m/Y') }}) |
@endcomponent

@component('mail::button', ['url' => route('admin.dashboard')])
    Voir le Tableau de Bord
@endcomponent

Cordialement,

Le CLO (Chief Lapin Officier),<br>
{{ config('app.name') }}
@endcomponent
