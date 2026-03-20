<?php

use App\Models\DigitalTraining;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Models\User;

function makeTrainingFor(User $user, string $title = 'Formation test'): DigitalTraining
{
    return DigitalTraining::create([
        'user_id' => $user->id,
        'title' => $title,
        'slug' => 'formation-' . uniqid(),
        'status' => 'draft',
        'access_type' => 'public',
    ]);
}

test('owner can move module up and down in digital training builder', function () {
    $user = User::factory()->create();
    $training = makeTrainingFor($user);

    $moduleA = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module A',
        'display_order' => 1,
    ]);
    $moduleB = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module B',
        'display_order' => 2,
    ]);

    $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.modules.move', [$training, $moduleB]), [
            '_token' => 'csrf-token',
            'direction' => 'up',
        ])
        ->assertRedirect();

    $moduleA->refresh();
    $moduleB->refresh();

    expect($moduleB->display_order)->toBe(1)
        ->and($moduleA->display_order)->toBe(2);

    $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.modules.move', [$training, $moduleB]), [
            '_token' => 'csrf-token',
            'direction' => 'down',
        ])
        ->assertRedirect();

    $moduleA->refresh();
    $moduleB->refresh();

    expect($moduleA->display_order)->toBe(1)
        ->and($moduleB->display_order)->toBe(2);
});

test('owner can move block order inside module', function () {
    $user = User::factory()->create();
    $training = makeTrainingFor($user);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module',
        'display_order' => 1,
    ]);

    $block1 = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 1',
        'content' => 'A',
        'display_order' => 1,
    ]);
    $block2 = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 2',
        'content' => 'B',
        'display_order' => 2,
    ]);
    $block3 = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 3',
        'content' => 'C',
        'display_order' => 3,
    ]);

    $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.blocks.move', [$training, $module, $block3]), [
            '_token' => 'csrf-token',
            'direction' => 'up',
        ])
        ->assertRedirect();

    $block1->refresh();
    $block2->refresh();
    $block3->refresh();

    expect($block1->display_order)->toBe(1)
        ->and($block3->display_order)->toBe(2)
        ->and($block2->display_order)->toBe(3);
});

test('user cannot move module that does not belong to their training', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $ownerTraining = makeTrainingFor($owner, 'Owner training');
    $otherTraining = makeTrainingFor($other, 'Other training');

    $otherModule = TrainingModule::create([
        'digital_training_id' => $otherTraining->id,
        'title' => 'Other module',
        'display_order' => 1,
    ]);

    $this->actingAs($owner)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.modules.move', [$ownerTraining, $otherModule]), [
            '_token' => 'csrf-token',
            'direction' => 'up',
        ])
        ->assertForbidden();
});
