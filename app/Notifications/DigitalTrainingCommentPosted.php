<?php

namespace App\Notifications;

use App\Models\DigitalTrainingBlockComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DigitalTrainingCommentPosted extends Notification
{
    use Queueable;

    public function __construct(protected DigitalTrainingBlockComment $comment)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $training = $this->comment->training;
        $block = $this->comment->block;
        $participant = $this->comment->participant_name_snapshot ?: $this->comment->participant_email_snapshot ?: 'Un participant';

        return [
            'digital_training_id' => $training?->id,
            'training_block_id' => $block?->id,
            'comment_id' => $this->comment->id,
            'participant_name' => $participant,
            'message' => 'Nouveau commentaire sur la formation "' . ($training?->title ?? 'Formation') . '" par ' . $participant,
            'url' => $training ? route('digital-trainings.comments.index', $training) : route('notifications.index'),
        ];
    }
}
