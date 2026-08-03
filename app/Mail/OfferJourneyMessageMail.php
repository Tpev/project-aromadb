<?php

namespace App\Mail;

use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailRenderer;
use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class OfferJourneyMessageMail extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public function __construct(
        public readonly User $therapist,
        public readonly string $messageSubject,
        public readonly string $messageBody,
        public readonly string $unsubscribeUrl,
        public readonly string $category,
        public readonly ?int $deliveryId = null,
        public readonly ?OfferJourneyMessageCampaign $campaign = null,
        public readonly array $renderVariables = []
    ) {
    }

    public function build(): self
    {
        $therapistName = $this->therapist->company_name
            ?: trim(($this->therapist->first_name ?? '').' '.($this->therapist->last_name ?? ''))
            ?: $this->therapist->name
            ?: 'Votre praticien';
        $mail = $this->applyPractitionerReplyTo($this->therapist)
            ->subject($this->messageSubject)
            ->from(config('mail.from.address'), config('mail.from.name'));

        if ($this->campaign?->content_json) {
            $rendered = app(OfferJourneyEmailRenderer::class)->render(
                $this->campaign,
                $this->renderVariables,
                $this->unsubscribeUrl,
                $this->category
            );
            $mail->html($rendered['html'])
                ->text('emails.offer-journeys.rendered-text')
                ->with(['renderedText' => $rendered['text']]);
        } else {
            $mail->view('emails.offer-journeys.message-html')
                ->text('emails.offer-journeys.message-text')
                ->with([
                    'therapistName' => $therapistName,
                    'body' => $this->messageBody,
                    'unsubscribeUrl' => $this->unsubscribeUrl,
                    'category' => $this->category,
                ]);
        }

        $configurationSet = config('offer_journeys.deliverability.configuration_set');
        if ($configurationSet || $this->deliveryId) {
            $mail->withSymfonyMessage(function (Email $message) use ($configurationSet): void {
                if ($configurationSet) {
                    $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', (string) $configurationSet);
                }
                if ($this->deliveryId) {
                    $message->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'offer_delivery_id='.$this->deliveryId);
                }
            });
        }

        return $mail;
    }
}
