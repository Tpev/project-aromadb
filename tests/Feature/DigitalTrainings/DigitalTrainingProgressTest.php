<?php

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Models\User;

function makeTrainingForProgress(User $user): DigitalTraining
{
    return DigitalTraining::create([
        'user_id' => $user->id,
        'title' => 'Formation progression',
        'slug' => 'formation-progression-' . uniqid(),
        'status' => 'published',
        'access_type' => 'public',
    ]);
}

test('viewing blocks updates training progress percentage', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForProgress($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);

    $blockA = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc A',
        'content' => '<p>A</p>',
        'display_order' => 1,
    ]);

    $blockB = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc B',
        'content' => '<p>B</p>',
        'display_order' => 2,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->postJson(route('digital-trainings.access.blocks.viewed', [$enrollment->access_token, $blockA]))
        ->assertOk()
        ->assertJsonPath('progress_percent', 50)
        ->assertJsonPath('completed', false);

    $enrollment->refresh();

    expect($enrollment->progress_percent)->toBe(50)
        ->and($enrollment->viewed_block_ids)->toBe([$blockA->id])
        ->and($enrollment->completed_at)->toBeNull();

    $this->postJson(route('digital-trainings.access.blocks.viewed', [$enrollment->access_token, $blockB]))
        ->assertOk()
        ->assertJsonPath('progress_percent', 100)
        ->assertJsonPath('completed', true);

    $enrollment->refresh();

    expect($enrollment->progress_percent)->toBe(100)
        ->and($enrollment->viewed_block_ids)->toBe([$blockA->id, $blockB->id])
        ->and($enrollment->completed_at)->not->toBeNull();
});

test('viewing same block twice does not inflate progress', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForProgress($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);

    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc unique',
        'content' => '<p>A</p>',
        'display_order' => 1,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->postJson(route('digital-trainings.access.blocks.viewed', [$enrollment->access_token, $block]))
        ->assertOk()
        ->assertJsonPath('progress_percent', 100);

    $this->postJson(route('digital-trainings.access.blocks.viewed', [$enrollment->access_token, $block]))
        ->assertOk()
        ->assertJsonPath('progress_percent', 100);

    $enrollment->refresh();

    expect($enrollment->viewed_block_ids)->toBe([$block->id]);
});

test('marking a block as completed stores a dedicated completed state', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForProgress($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);

    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc suivi',
        'content' => '<p>Suivi</p>',
        'display_order' => 1,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->postJson(route('digital-trainings.access.blocks.complete', [$enrollment->access_token, $block]))
        ->assertOk()
        ->assertJsonPath('progress_percent', 100)
        ->assertJsonPath('viewed_block_ids.0', $block->id)
        ->assertJsonPath('completed_block_ids.0', $block->id);

    $enrollment->refresh();

    expect($enrollment->viewed_block_ids)->toBe([$block->id])
        ->and($enrollment->completed_block_ids)->toBe([$block->id])
        ->and($enrollment->completed_at)->not->toBeNull();
});

test('player shows completed and viewed indicators for blocks already tracked', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForProgress($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);

    $completedBlock = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc terminé',
        'content' => '<p>Terminé</p>',
        'display_order' => 1,
    ]);

    $viewedBlock = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc vu',
        'content' => '<p>Vu</p>',
        'display_order' => 2,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 50,
        'viewed_block_ids' => [$completedBlock->id, $viewedBlock->id],
        'completed_block_ids' => [$completedBlock->id],
    ]);

    $this->get(route('digital-trainings.access.show', $enrollment->access_token))
        ->assertOk()
        ->assertSee('data-block-id="' . $completedBlock->id . '"', false)
        ->assertSee('is-completed', false)
        ->assertSee('data-block-id="' . $viewedBlock->id . '"', false)
        ->assertSee('currentBlockStateBadge', false)
        ->assertSee('Marquer cette partie comme terminée');
});
