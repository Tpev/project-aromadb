@component('mail::message')
# Rapport Quotidien des KPIs

Bonjour les Lapiz,

{{-- Afficher un message festif si un nouveau record est atteint --}}
@if($kpis['isNewHigh'])
	@component('mail::message1')

	 ![Bravo](https://aromamade.com/images/congratz.webp)
     🎉 Nouveau Record Atteint ! 🎉
        Hier, vous avez atteint un nouveau record de **{{ $kpis['sessionsYesterday'] }}** sessions, dépassant l'ancien record de **{{ $kpis['lastHighCount'] }}** sessions (Le {{ \Carbon\Carbon::parse($kpis['lastHighDate'])->format('d/m/Y') }}).
@endcomponent
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

@component('mail::button', ['url' => route('admin.index')])
    Voir le Tableau de Bord
@endcomponent

Cordialement,

Le CLO (Chief Lapin Officier),<br>
{{ config('app.name') }}
@endcomponent
