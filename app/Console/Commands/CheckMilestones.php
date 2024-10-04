<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\MilestoneReachedEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\PageViewLog;
use App\Models\Milestone;
use App\Models\User;

class CheckMilestones extends Command
{
    protected $signature = 'milestone:check';

    protected $description = 'Check if milestones have been reached and send emails';

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
            $milestone->last_milestone = $nextMilestone;
            $milestone->save();

            $this->sendMilestoneEmail($sessionsTotal, $nextMilestone);

            $this->info("Milestone of {$nextMilestone} sessions reached. Email sent.");
        } else {
            $this->info("No new milestone reached. Current total sessions: {$sessionsTotal}");
        }
    }

    private function getTotalSessions()
    {
        // Exclude bots as before
        // ...

        $sessionsTotal = $pageViewsQuery
            ->distinct('session_id')
            ->count('session_id');

        return $sessionsTotal;
    }

    private function sendMilestoneEmail($sessionsTotal, $milestone)
    {
        $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new MilestoneReachedEmail($sessionsTotal, $milestone));
        }
    }
}
