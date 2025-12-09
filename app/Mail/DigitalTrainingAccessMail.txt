<?php

// app/Mail/DigitalTrainingAccessMail.php

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
        $accessUrl = url('/training-access/' . $this->enrollment->access_token); // future route

        return $this->subject('Accès à votre formation : ' . $training->title)
            ->view('emails.digital-trainings.access')
            ->with([
                'training'  => $training,
                'enrollment'=> $this->enrollment,
                'accessUrl' => $accessUrl,
            ]);
    }
}
