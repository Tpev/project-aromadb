<?php

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingBlockComment;
use App\Models\DigitalTrainingEnrollment;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Models\User;

function makeTrainingForComments(User $user, array $overrides = []): DigitalTraining
{
    return DigitalTraining::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Formation commentaires',
        'slug' => 'formation-commentaires-' . uniqid(),
        'status' => 'published',
        'access_type' => 'public',
    ], $overrides));
}

test('therapist can enable comments when creating a training block', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForComments($therapist, ['status' => 'draft']);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);

    $this->actingAs($therapist)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.blocks.store', [$training, $module]), [
            '_token' => 'csrf-token',
            'type' => 'text',
            'title' => 'Bloc commentable',
            'content' => '<p>Hello</p>',
            'comments_enabled' => '1',
        ])
        ->assertRedirect();

    $block = TrainingBlock::where('training_module_id', $module->id)->first();

    expect($block)->not->toBeNull()
        ->and(data_get($block->meta, 'comments_enabled'))->toBeTrue()
        ->and($block->commentsEnabled())->toBeTrue();
});

test('participant can post comment on enabled block and therapist gets in-app notification', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForComments($therapist);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);
    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 1',
        'content' => '<p>Contenu</p>',
        'meta' => ['comments_enabled' => true],
        'display_order' => 1,
    ]);
    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Alice Martin',
        'participant_email' => 'alice@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->withSession(['_token' => 'csrf-token'])
        ->postJson(route('digital-trainings.access.comments.store', [$enrollment->access_token, $block]), [
            'comment' => 'J’ai une question sur cette partie.',
        ])
        ->assertOk()
        ->assertJsonPath('comment.participant_name', 'Alice');

    $comment = DigitalTrainingBlockComment::first();

    expect($comment)->not->toBeNull()
        ->and($comment->training_block_id)->toBe($block->id)
        ->and($comment->participant_name_snapshot)->toBe('Alice Martin');

    $therapist->refresh();

    expect($therapist->notifications()->count())->toBe(1)
        ->and($therapist->notifications()->first()->data['comment_id'])->toBe($comment->id);
});

test('participant cannot post comment on block when comments are disabled', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForComments($therapist);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);
    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc privé',
        'content' => '<p>Contenu</p>',
        'meta' => ['comments_enabled' => false],
        'display_order' => 1,
    ]);
    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Bob',
        'participant_email' => 'bob@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->withSession(['_token' => 'csrf-token'])
        ->postJson(route('digital-trainings.access.comments.store', [$enrollment->access_token, $block]), [
            'comment' => 'Test refusé',
        ])
        ->assertStatus(403);

    expect(DigitalTrainingBlockComment::count())->toBe(0);
});

test('participant can see other participants comments with first name only', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForComments($therapist);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);
    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc partagé',
        'content' => '<p>Contenu</p>',
        'meta' => ['comments_enabled' => true],
        'display_order' => 1,
    ]);

    $aliceEnrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Alice Martin',
        'participant_email' => 'alice@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $bobEnrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Bob Dupont',
        'participant_email' => 'bob@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    DigitalTrainingBlockComment::create([
        'digital_training_id' => $training->id,
        'training_module_id' => $module->id,
        'training_block_id' => $block->id,
        'digital_training_enrollment_id' => $aliceEnrollment->id,
        'participant_name_snapshot' => 'Alice Martin',
        'participant_email_snapshot' => 'alice@example.test',
        'comment' => 'Super conseil, merci.',
        'created_by_role' => 'participant',
        'is_visible' => true,
    ]);

    $this->get(route('digital-trainings.access.show', ['token' => $bobEnrollment->access_token, 'block' => $block->id]))
        ->assertOk()
        ->assertSee('Super conseil, merci.')
        ->assertSee('Alice')
        ->assertDontSee('Alice Martin');
});

test('therapist can view training comments page', function () {
    $therapist = User::factory()->create();
    $training = makeTrainingForComments($therapist);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);
    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 1',
        'content' => '<p>Contenu</p>',
        'meta' => ['comments_enabled' => true],
        'display_order' => 1,
    ]);
    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Claire',
        'participant_email' => 'claire@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);
    DigitalTrainingBlockComment::create([
        'digital_training_id' => $training->id,
        'training_module_id' => $module->id,
        'training_block_id' => $block->id,
        'digital_training_enrollment_id' => $enrollment->id,
        'participant_name_snapshot' => 'Claire',
        'participant_email_snapshot' => 'claire@example.test',
        'comment' => 'Merci pour cette explication.',
        'created_by_role' => 'participant',
        'is_visible' => true,
    ]);

    $this->actingAs($therapist)
        ->get(route('digital-trainings.comments.index', $training))
        ->assertOk()
        ->assertSee('Merci pour cette explication.')
        ->assertSee('Claire');
});

test('therapist can reply from comments inbox and participant sees the reply', function () {
    $therapist = User::factory()->create(['name' => 'Julie Martin']);
    $training = makeTrainingForComments($therapist);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'display_order' => 1,
    ]);
    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bloc 1',
        'content' => '<p>Contenu</p>',
        'meta' => ['comments_enabled' => true],
        'display_order' => 1,
    ]);
    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Claire Dupont',
        'participant_email' => 'claire@example.test',
        'access_token' => 'token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $comment = DigitalTrainingBlockComment::create([
        'digital_training_id' => $training->id,
        'training_module_id' => $module->id,
        'training_block_id' => $block->id,
        'digital_training_enrollment_id' => $enrollment->id,
        'participant_name_snapshot' => 'Claire Dupont',
        'participant_email_snapshot' => 'claire@example.test',
        'comment' => 'Est-ce que vous pouvez préciser ce point ?',
        'created_by_role' => 'participant',
        'is_visible' => true,
    ]);

    $this->actingAs($therapist)
        ->post(route('digital-trainings.comments.reply.store', [$training, $comment]), [
            'comment' => 'Bien sûr, voici une précision complémentaire.',
        ])
        ->assertRedirect(route('digital-trainings.comments.index', $training));

    $reply = DigitalTrainingBlockComment::where('parent_comment_id', $comment->id)->first();

    expect($reply)->not->toBeNull()
        ->and($reply->created_by_role)->toBe('therapist')
        ->and($reply->participant_name_snapshot)->toBe('Julie Martin');

    $response = $this->get(route('digital-trainings.access.show', ['token' => $enrollment->access_token, 'block' => $block->id]))
        ->assertOk();

    $commentsByBlock = $response->viewData('commentsByBlock');
    $blockComments = collect($commentsByBlock[$block->id] ?? []);
    $replyInView = optional($blockComments->first())->replies?->first();

    expect($replyInView)->not->toBeNull()
        ->and($replyInView->comment)->toBe('Bien sûr, voici une précision complémentaire.')
        ->and($replyInView->participant_first_name)->toBe('Julie')
        ->and($replyInView->author_role_label)->toBe('Thérapeute');
});
