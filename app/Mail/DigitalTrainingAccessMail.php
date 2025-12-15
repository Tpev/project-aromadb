<?php

namespace App\Mail;

use App\Models\DigitalTrainingEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DigitalTrainingAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public DigitalTrainingEnrollment $enrollment;

    public function __construct(DigitalTrainingEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function build()
    {
        $training = $this->enrollment->training;
        $practitioner = optional($training->user);

        $accessUrl = url('/training-access/' . $this->enrollment->access_token);

        $subject = $practitioner->name
            ? $practitioner->name . ' vous a donné accès à une formation'
            : 'Accès à votre formation';

        return $this
            ->subject($subject)
            ->view('emails.digital-trainings.access')
            ->with([
                'training'     => $training,
                'enrollment'   => $this->enrollment,
                'accessUrl'    => $accessUrl,
                'practitioner' => $practitioner,
            ]);
    }
}
