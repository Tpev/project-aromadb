@component('mail::message')
# Rapport Quotidien des KPIs

Bonjour les Lapiz,

Voici les KPIs pour le **{{ \Carbon\Carbon::now()->format('d/m/Y') }}** :

@component('mail::table')
| KPI                        | Valeur                       |
| -------------------------- | ---------------------------- |
| **Sessions Aujourd'hui**   | {{ $kpis['sessionsToday'] }} |
| **Sessions Hier**          | {{ $kpis['sessionsYesterday'] }} |
| **Sessions Cette Semaine** | {{ $kpis['sessionsThisWeek'] }} |
| **Sessions Semaine Dernière** | {{ $kpis['sessionsLastWeek'] }} |
| **Sessions Ce Mois**       | {{ $kpis['sessionsThisMonth'] }} |
| **Sessions Mois Dernier**  | {{ $kpis['sessionsLastMonth'] }} |
| **Total des Sessions**     | {{ $kpis['sessionsTotal'] }} |
@endcomponent

Cordialement,

Le CLO (Chief Lapin Officier),<br>
{{ config('app.name') }}
@endcomponent
