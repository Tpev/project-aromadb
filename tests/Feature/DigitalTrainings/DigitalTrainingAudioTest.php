<?php

use App\Models\DigitalTraining;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeDigitalTrainingForAudio(User $user, array $overrides = []): DigitalTraining
{
    return DigitalTraining::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Formation audio test',
        'slug' => 'formation-audio-' . uniqid(),
        'status' => 'draft',
        'access_type' => 'public',
    ], $overrides));
}

test('owner can upload an audio block in digital training builder', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $training = makeDigitalTrainingForAudio($user);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module audio',
        'display_order' => 1,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('digital-trainings.blocks.store', [$training, $module]), [
            '_token' => 'csrf-token',
            'type' => 'audio',
            'title' => 'Relaxation guidée',
            'content' => null,
            'file' => UploadedFile::fake()->create('relaxation.mp3', 2048, 'audio/mpeg'),
        ]);

    $response->assertRedirect();

    $block = TrainingBlock::where('training_module_id', $module->id)->first();

    expect($block)->not->toBeNull()
        ->and($block->type)->toBe('audio')
        ->and($block->title)->toBe('Relaxation guidée')
        ->and($block->file_path)->not->toBeNull();

    Storage::disk('public')->assertExists($block->file_path);
});

test('owner can replace uploaded audio file on existing block', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $training = makeDigitalTrainingForAudio($user);
    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module audio',
        'display_order' => 1,
    ]);

    $initialPath = UploadedFile::fake()->create('initial.mp3', 512, 'audio/mpeg')
        ->store('digital-trainings/audios', 'public');

    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'audio',
        'title' => 'Audio initial',
        'content' => null,
        'file_path' => $initialPath,
        'display_order' => 1,
    ]);

    $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->put(route('digital-trainings.blocks.update', [$training, $module, $block]), [
            '_token' => 'csrf-token',
            'title' => 'Audio remplacé',
            'file' => UploadedFile::fake()->create('updated.m4a', 1024, 'audio/mp4'),
        ])
        ->assertRedirect();

    $block->refresh();

    expect($block->title)->toBe('Audio remplacé')
        ->and($block->file_path)->not->toBe($initialPath);

    Storage::disk('public')->assertMissing($initialPath);
    Storage::disk('public')->assertExists($block->file_path);
});

test('public digital training page shows audio badge when a module contains audio content', function () {
    $user = User::factory()->create();
    $training = makeDigitalTrainingForAudio($user, [
        'status' => 'published',
        'description' => 'Description formation audio',
    ]);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module podcast',
        'display_order' => 1,
    ]);

    TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'audio',
        'title' => 'Episode 1',
        'content' => 'https://cdn.example.test/audio.mp3',
        'display_order' => 1,
    ]);

    $this->get(route('digital-trainings.public.show', $training))
        ->assertOk()
        ->assertSee('Audios');
});
