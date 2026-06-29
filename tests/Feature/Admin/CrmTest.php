<?php

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin can view the CRM board', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.crm.index'));

    $response->assertOk();
    $response->assertSee('CRM leads');
    $response->assertSee('Nouveau lead');
    $response->assertSee('ARR pipeline');
});

test('non admin users cannot access the CRM', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.crm.index'))
        ->assertForbidden();
});

test('admin can create a lead, add a touchpoint, and move the lead', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('admin.crm.leads.store'), [
            'full_name' => 'Alice Martin',
            'company' => 'Aroma Studio',
            'email' => 'alice@example.com',
            'phone' => '+33123456789',
            'source' => 'Website',
            'referral_source' => 'Partner Julie',
            'stage' => 'new',
            'expected_license_type' => 'new_pro_annuelle',
            'probability' => '15',
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'tags' => 'hot, demo',
            'notes' => 'Asked for a guided onboarding.',
        ])
        ->assertRedirect();

    $lead = CrmLead::firstOrFail();

    expect($lead->full_name)->toBe('Alice Martin');
    expect($lead->tag_list)->toBe(['hot', 'demo']);
    expect($lead->referral_source)->toBe('Partner Julie');
    expect((float) $lead->estimated_value)->toBe(328.90);

    $this->actingAs($admin)
        ->get(route('admin.crm.leads.show', $lead))
        ->assertOk()
        ->assertSee('Fiche lead')
        ->assertSee('Licence visee')
        ->assertSee('Partner Julie');

    $this->actingAs($admin)
        ->post(route('admin.crm.activities.store', $lead), [
            'activity_type' => 'call',
            'activity_direction' => 'outbound',
            'activity_subject' => 'Discovery call',
            'activity_body' => 'Confirmed timing and decision criteria.',
            'activity_occurred_at' => now()->format('Y-m-d H:i:s'),
            'activity_due_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'activity_outcome' => 'Start onboarding',
        ])
        ->assertRedirect(route('admin.crm.leads.show', $lead));

    expect(CrmLeadActivity::count())->toBe(1);
    expect($lead->fresh()->last_touch_at)->not->toBeNull();

    $this->actingAs($admin)
        ->get(route('admin.crm.index'))
        ->assertOk()
        ->assertSee('Alice Martin')
        ->assertSee('329')
        ->assertSee('Discovery call');

    $this->actingAs($admin)
        ->patchJson(route('admin.crm.leads.stage', $lead), [
            'stage' => 'onboarding_ok',
        ])
        ->assertOk()
        ->assertJsonPath('stage', 'onboarding_ok');

    expect($lead->fresh()->stage)->toBe('onboarding_ok');
});

test('admin can import and export leads as csv', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $csv = implode("\n", [
        'full_name,email,stage,source,referral_source,expected_license_type,actual_license_type,tags,notes',
        'Marie Dupont,marie@example.com,presentation_ok,Referral,Partner Sophie,new_premium_annuelle,,"priority, warm",Imported from partner list',
    ]);

    $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

    $this->actingAs($admin)
        ->post(route('admin.crm.import'), [
            'csv_file' => $file,
        ])
        ->assertRedirect(route('admin.crm.index'));

    $this->assertDatabaseHas('crm_leads', [
        'full_name' => 'Marie Dupont',
        'email' => 'marie@example.com',
        'stage' => 'presentation_ok',
        'referral_source' => 'Partner Sophie',
        'expected_license_type' => 'new_premium_annuelle',
        'estimated_value' => 548.90,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.crm.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('Marie Dupont');
});
