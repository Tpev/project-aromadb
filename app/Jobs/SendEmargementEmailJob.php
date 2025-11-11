<?php

namespace App\Jobs;

use App\Mail\EmargementRequestMail;
use App\Models\Emargement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SendEmargementEmailJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public Emargement $em) {}

    public function handle(): void
    {
        if (empty($this->em->client_email)) return;
        Mail::to($this->em->client_email)->send(new EmargementRequestMail($this->em));
    }
}
