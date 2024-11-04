@component('mail::message')
# Rapport Quotidien des KPIs

Bonjour les Lapiz,

{{-- Afficher un message festif si un nouveau record est atteint --}}
@if($kpis['isNewHigh'])


	 ![Bravo](https://aromamade.com/images/congratz.webp)
     🎉 Nouveau Record Atteint ! 🎉
        Hier, vous avez atteint un nouveau record de **{{ $kpis['sessionsYesterday'] }}** sessions, dépassant l'ancien record de **{{ $kpis['lastHighCount'] }}** sessions (Le {{ \Carbon\Carbon::parse($kpis['lastHighDate'])->format('d/m/Y') }}).

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

{{-- New Facebook Metrics Section --}}
## Statistiques Facebook 📊

@component('mail::table')
| **Facebook KPI**                   | **Dernières Valeurs**                                  | **Il y a 24h**                                      | **Croissance (%)**                       |
| ---------------------------------- | ------------------------------------------------------ | --------------------------------------------------- | ---------------------------------------- |
| **Nombre de Likes (fan_count)**    | {{ $kpis['facebookMetrics']['latest']['fan_count'] ?? 'N/A' }}       | {{ $kpis['facebookMetrics']['24h_ago']['fan_count'] ?? 'N/A' }}       | {{ isset($kpis['facebookGrowth']['fan_count']) ? number_format($kpis['facebookGrowth']['fan_count'], 2) . '%' : 'N/A' }} |
| **Nombre de Followers**            | {{ $kpis['facebookMetrics']['latest']['followers_count'] ?? 'N/A' }} | {{ $kpis['facebookMetrics']['24h_ago']['followers_count'] ?? 'N/A' }} | {{ isset($kpis['facebookGrowth']['followers_count']) ? number_format($kpis['facebookGrowth']['followers_count'], 2) . '%' : 'N/A' }} |
@endcomponent



@component('mail::button', ['url' => route('admin.index')])
    Voir le Tableau de Bord
@endcomponent

Cordialement,

Le CLO (Chief Lapin Officier),<br>
{{ config('app.name') }}
@endcomponent
