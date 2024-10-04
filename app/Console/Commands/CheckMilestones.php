<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\MilestoneReachedEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\PageViewLog;
use App\Models\Milestone;
use App\Models\User;

class CheckMilestones extends Command
{
    protected $signature = 'milestone:check';

    protected $description = 'Check if milestones have been reached and send emails';

    // Define the milestones you want to track
    protected $milestones = [500, 1000, 2000, 5000, 10000];

    public function handle()
    {
        $sessionsTotal = $this->getTotalSessions();

        $milestone = Milestone::firstOrCreate(
            ['type' => 'sessions'],
            ['last_milestone' => 0]
        );

        $nextMilestone = null;
        foreach ($this->milestones as $m) {
            if ($sessionsTotal >= $m && $m > $milestone->last_milestone) {
                $nextMilestone = $m;
            }
        }

        if ($nextMilestone) {
            // Update the last milestone
            $milestone->last_milestone = $nextMilestone;
            $milestone->save();

            // Send the email
            $this->sendMilestoneEmail($sessionsTotal, $nextMilestone);

            $this->info("Milestone of {$nextMilestone} sessions reached. Email sent.");
        } else {
            $this->info("No new milestone reached. Current total sessions: {$sessionsTotal}");
        }
    }

    private function getTotalSessions()
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

        // Total sessions
        $sessionsTotal = $pageViewsQuery
            ->distinct('session_id')
            ->count('session_id');

        return $sessionsTotal;
    }

    private function sendMilestoneEmail($sessionsTotal, $milestone)
    {
        // Get admin emails
        $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

        // Send the email
        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new MilestoneReachedEmail($sessionsTotal, $milestone));
        }
    }
}
