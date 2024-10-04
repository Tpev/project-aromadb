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
        // Define common bot user agents
        $botUserAgents = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'Googlebot',
            'Bingbot', 'Baiduspider', 'DuckDuckBot', 'YandexBot', 'Sogou',
            'Exabot', 'facebot', 'ia_archiver', 'MJ12bot', 'AsyncHttp', 'python'
        ];

        // Base query excluding null and empty user agents, and bot user agents
        $pageViewsQuery = PageViewLog::whereNotNull('user_agent')
            ->where('user_agent', '!=', '')
            ->where(function ($query) use ($botUserAgents) {
                foreach ($botUserAgents as $bot) {
                    $query->where('user_agent', 'NOT LIKE', "%$bot%");
                }
            });

        // Get current timestamps
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Perform the date filtering and count unique sessions
        $sessionsToday = (clone $pageViewsQuery)->whereDate('viewed_at', '=', $today)->distinct('session_id')->count('session_id');
        $sessionsYesterday = (clone $pageViewsQuery)->whereDate('viewed_at', '=', $yesterday)->distinct('session_id')->count('session_id');
        $sessionsThisWeek = (clone $pageViewsQuery)->where('viewed_at', '>=', $startOfWeek)->distinct('session_id')->count('session_id');
        $sessionsLastWeek = (clone $pageViewsQuery)->whereBetween('viewed_at', [$startOfLastWeek, $startOfLastWeek->copy()->endOfWeek()])->distinct('session_id')->count('session_id');
        $sessionsThisMonth = (clone $pageViewsQuery)->where('viewed_at', '>=', $startOfMonth)->distinct('session_id')->count('session_id');
        $sessionsLastMonth = (clone $pageViewsQuery)->whereBetween('viewed_at', [$startOfLastMonth, $startOfLastMonth->copy()->endOfMonth()])->distinct('session_id')->count('session_id');
        $sessionsTotal = (clone $pageViewsQuery)->distinct('session_id')->count('session_id');

        // Prepare KPI data
        $kpis = [
            'sessionsToday' => $sessionsToday,
            'sessionsYesterday' => $sessionsYesterday,
            'sessionsThisWeek' => $sessionsThisWeek,
            'sessionsLastWeek' => $sessionsLastWeek,
            'sessionsThisMonth' => $sessionsThisMonth,
            'sessionsLastMonth' => $sessionsLastMonth,
            'sessionsTotal' => $sessionsTotal,
        ];

        // Get admin email(s)
        $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

        // Send the email to all admins
        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new DailyKpiEmail($kpis));
            $this->info('Daily KPI email sent successfully.');
        } else {
            $this->warn('No admin emails found to send the KPI email.');
        }
    }
}
