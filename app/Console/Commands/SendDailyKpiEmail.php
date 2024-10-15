<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\DailyKpiEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\PageViewLog;
use App\Models\User;

class SendDailyKpiEmail extends Command
{
    protected $signature = 'email:daily-kpi';

    protected $description = 'Send daily KPI email to admin at 6 AM';

    public function handle()
    {
        // Définir les agents utilisateurs des bots courants
        $botUserAgents = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'Googlebot',
            'Bingbot', 'Baiduspider', 'DuckDuckBot', 'YandexBot', 'Sogou',
            'Exabot', 'facebot', 'ia_archiver', 'MJ12bot', 'AsyncHttp', 'python'
        ];

        // Base query excluant les agents utilisateurs null, vides et des bots
        $pageViewsQuery = PageViewLog::whereNotNull('user_agent')
            ->where('user_agent', '!=', '')
            ->where(function ($query) use ($botUserAgents) {
                foreach ($botUserAgents as $bot) {
                    $query->where('user_agent', 'NOT LIKE', "%$bot%");
                }
            });

        // Récupérer les dates pertinentes
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Compter les sessions distinctes pour différentes périodes
        $sessionsToday = (clone $pageViewsQuery)
            ->whereDate('viewed_at', '=', $today)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsYesterday = (clone $pageViewsQuery)
            ->whereDate('viewed_at', '=', $yesterday)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsThisWeek = (clone $pageViewsQuery)
            ->where('viewed_at', '>=', $startOfWeek)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsLastWeek = (clone $pageViewsQuery)
            ->whereBetween('viewed_at', [$startOfLastWeek, $startOfLastWeek->copy()->endOfWeek()])
            ->distinct('session_id')
            ->count('session_id');

        $sessionsThisMonth = (clone $pageViewsQuery)
            ->where('viewed_at', '>=', $startOfMonth)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsLastMonth = (clone $pageViewsQuery)
            ->whereBetween('viewed_at', [$startOfLastMonth, $startOfLastMonth->copy()->endOfMonth()])
            ->distinct('session_id')
            ->count('session_id');

        $sessionsTotal = (clone $pageViewsQuery)
            ->distinct('session_id')
            ->count('session_id');

        // Calculer le nombre maximal de sessions quotidiennes jamais enregistrées
        $maxDailySessionsRecord = PageViewLog::selectRaw('DATE(viewed_at) as date, COUNT(DISTINCT session_id) as session_count')
            ->whereNotNull('user_agent')
            ->where('user_agent', '!=', '')
            ->where(function ($query) use ($botUserAgents) {
                foreach ($botUserAgents as $bot) {
                    $query->where('user_agent', 'NOT LIKE', "%$bot%");
                }
            })
            ->groupBy('date')
            ->orderByDesc('session_count')
            ->first();

        $highestSessionCount = $maxDailySessionsRecord ? $maxDailySessionsRecord->session_count : 0;
        $highestSessionDate = $maxDailySessionsRecord ? $maxDailySessionsRecord->date : null;

        // Trouver le record précédent (le second plus élevé)
        $previousHighRecord = PageViewLog::selectRaw('DATE(viewed_at) as date, COUNT(DISTINCT session_id) as session_count')
            ->whereNotNull('user_agent')
            ->where('user_agent', '!=', '')
            ->where(function ($query) use ($botUserAgents) {
                foreach ($botUserAgents as $bot) {
                    $query->where('user_agent', 'NOT LIKE', "%$bot%");
                }
            })
            ->groupBy('date')
            ->orderByDesc('session_count')
            ->skip(1) // Sauter le premier record (le plus élevé)
            ->first();

        $previousHighCount = $previousHighRecord ? $previousHighRecord->session_count : 0;
        $previousHighDate = $previousHighRecord ? $previousHighRecord->date : null;

        // Vérifier si hier a dépassé le record actuel
        $isNewHigh = false;
        $lastHighCount = $highestSessionCount;
        $lastHighDate = $highestSessionDate;

        if ($sessionsYesterday > $highestSessionCount) {
            $isNewHigh = true;
            $lastHighCount = $highestSessionCount;
            $lastHighDate = $highestSessionDate;

            // (Optionnel) Vous pouvez mettre à jour une table pour stocker ce record si nécessaire
        }

        // Préparer les données KPI
        $kpis = [
            'sessionsToday' => $sessionsToday,
            'sessionsYesterday' => $sessionsYesterday,
            'sessionsThisWeek' => $sessionsThisWeek,
            'sessionsLastWeek' => $sessionsLastWeek,
            'sessionsThisMonth' => $sessionsThisMonth,
            'sessionsLastMonth' => $sessionsLastMonth,
            'sessionsTotal' => $sessionsTotal,
            'highestSessionCount' => $highestSessionCount,
            'highestSessionDate' => $highestSessionDate,
            'isNewHigh' => $isNewHigh,
            'lastHighCount' => $lastHighCount,
            'lastHighDate' => $lastHighDate,
        ];

        // Récupérer les emails des administrateurs
        $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

        // Envoyer l'email aux administrateurs
        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new DailyKpiEmail($kpis));
            $this->info('Daily KPI email sent successfully.');
        } else {
            $this->warn('No admin emails found to send the KPI email.');
        }
    }
}
