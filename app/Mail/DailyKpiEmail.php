<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyKpiEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $kpis;

    /**
     * Create a new message instance.
     *
     * @param array $kpis
     * @return void
     */
    public function __construct(array $kpis)
    {
        $this->kpis = $kpis;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->subject('Daily KPIs Report')
                     ->markdown('emails.daily_kpi');
    }
}
