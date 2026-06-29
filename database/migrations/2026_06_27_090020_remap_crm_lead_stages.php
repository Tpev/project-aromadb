<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_leads')) {
            return;
        }

        $this->remap([
            'qualified' => 'presentation_ok',
            'proposal' => 'onboarding_ok',
            'negotiation' => 'free_trial',
            'converted' => 'won',
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_leads')) {
            return;
        }

        $this->remap([
            'presentation_ok' => 'qualified',
            'onboarding_ok' => 'proposal',
            'free_trial' => 'negotiation',
            'referencement_gratuit' => 'negotiation',
            'won' => 'converted',
            'need_contact_info' => 'new',
        ]);
    }

    private function remap(array $stageMap): void
    {
        foreach ($stageMap as $from => $to) {
            DB::table('crm_leads')
                ->where('stage', $from)
                ->update(['stage' => $to]);
        }
    }
};
