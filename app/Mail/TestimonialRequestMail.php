<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TestimonialRequest;

class TestimonialRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $testimonialRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(TestimonialRequest $testimonialRequest)
    {
        $this->testimonialRequest = $testimonialRequest;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Demande de Témoignage')
                    ->markdown('emails.testimonial_request');
    }
}
