<?php

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function makeTrainingForVideoDownload(User $user): DigitalTraining
{
    return DigitalTraining::create([
        'user_id' => $user->id,
        'title' => 'Formation video',
        'slug' => 'formation-video-' . uniqid(),
        'status' => 'published',
        'access_type' => 'public',
    ]);
}

test('player shows explicit download fallback for uploaded training video', function () {
    Storage::fake('public');

    $therapist = User::factory()->create();
    $training = makeTrainingForVideoDownload($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module video',
        'display_order' => 1,
    ]);

    $storedPath = 'digital-trainings/videos/video-test.mp4';
    Storage::disk('public')->put($storedPath, 'fake-video-content');

    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'video_url',
        'title' => 'Video uploadee',
        'file_path' => $storedPath,
        'display_order' => 1,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'video-token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $this->get(route('digital-trainings.access.show', [$enrollment->access_token]))
        ->assertOk()
        ->assertSee('Télécharger la vidéo')
        ->assertSee(json_encode(route('digital-trainings.access.blocks.download', ['token' => $enrollment->access_token, 'block' => '__BLOCK__'])), false);
});

test('authorized enrollment can download uploaded training video through fallback route', function () {
    Storage::fake('public');

    $therapist = User::factory()->create();
    $training = makeTrainingForVideoDownload($therapist);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module video',
        'display_order' => 1,
    ]);

    $storedPath = 'digital-trainings/videos/video-download.mp4';
    Storage::disk('public')->put($storedPath, 'fake-video-content');

    $block = TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'video_url',
        'title' => 'Ma video',
        'file_path' => $storedPath,
        'display_order' => 1,
    ]);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Client Test',
        'participant_email' => 'client@example.test',
        'access_token' => 'video-token-' . uniqid(),
        'progress_percent' => 0,
    ]);

    $response = $this->get(route('digital-trainings.access.blocks.download', [$enrollment->access_token, $block]));

    $response->assertOk();
    $response->assertHeader('content-disposition');
});


