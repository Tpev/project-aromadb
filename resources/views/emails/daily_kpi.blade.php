@component('mail::message')
# Daily KPIs Report

Hello Admin,

Here are the KPIs for {{ \Carbon\Carbon::now()->format('d/m/Y') }}:

- **Sessions Today:** {{ $kpis['sessionsToday'] }}
- **Sessions Yesterday:** {{ $kpis['sessionsYesterday'] }}
- **Sessions This Week:** {{ $kpis['sessionsThisWeek'] }}
- **Sessions Last Week:** {{ $kpis['sessionsLastWeek'] }}
- **Sessions This Month:** {{ $kpis['sessionsThisMonth'] }}
- **Sessions Last Month:** {{ $kpis['sessionsLastMonth'] }}
- **Total Sessions:** {{ $kpis['sessionsTotal'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
