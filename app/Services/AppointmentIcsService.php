<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentIcsService
{
    public function build(Appointment $appointment): string
    {
        $eventData = $this->buildEventData($appointment);
        $nowUtc = Carbon::now('UTC');
        $uid = sprintf('appointment-%s-%s@aromamade.com', $appointment->id ?: 'draft', $appointment->token ?: uniqid());

        $lines = array_filter([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//AromaMade//Appointments//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $this->escapeText($uid),
            'DTSTAMP:' . $nowUtc->format('Ymd\THis\Z'),
            'DTSTART:' . $eventData['start_utc']->format('Ymd\THis\Z'),
            'DTEND:' . $eventData['end_utc']->format('Ymd\THis\Z'),
            'SUMMARY:' . $this->escapeText($eventData['summary']),
            'DESCRIPTION:' . $this->escapeText($eventData['description']),
            $eventData['location'] !== null ? 'LOCATION:' . $this->escapeText($eventData['location']) : null,
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'URL:' . $this->escapeText($eventData['event_url']),
            $appointment->user?->email
                ? 'ORGANIZER;CN=' . $this->escapeParam($eventData['practitioner_name']) . ':MAILTO:' . $this->escapeText($appointment->user->email)
                : null,
            $appointment->clientProfile?->email
                ? 'ATTENDEE;CN=' . $this->escapeParam($eventData['client_name'] ?: 'Client') . ';ROLE=REQ-PARTICIPANT:MAILTO:' . $this->escapeText($appointment->clientProfile->email)
                : null,
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return collect($lines)
            ->map(fn (string $line) => $this->foldLine($line))
            ->implode("\r\n") . "\r\n";
    }

    public function fileName(Appointment $appointment): string
    {
        return 'rendez-vous-' . ($appointment->id ?: 'aromamade') . '.ics';
    }

    public function googleCalendarUrl(Appointment $appointment): string
    {
        $eventData = $this->buildEventData($appointment);

        $query = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $eventData['summary'],
            'dates' => $eventData['start_utc']->format('Ymd\THis\Z') . '/' . $eventData['end_utc']->format('Ymd\THis\Z'),
            'details' => $eventData['description'],
            'location' => $eventData['location'] ?? '',
        ], '', '&', PHP_QUERY_RFC3986);

        return 'https://calendar.google.com/calendar/render?' . $query;
    }

    private function buildEventData(Appointment $appointment): array
    {
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
            'meeting',
        ]);

        $startUtc = $appointment->appointment_date->copy()->utc();
        $endUtc = $appointment->appointment_date->copy()
            ->addMinutes((int) ($appointment->duration ?? 60))
            ->utc();

        $practitionerName = $appointment->user?->company_name ?: $appointment->user?->name ?: 'Praticien';
        $clientName = trim((string) (($appointment->clientProfile?->first_name ?? '') . ' ' . ($appointment->clientProfile?->last_name ?? '')));
        $productName = $appointment->product?->name ?: 'Rendez-vous';
        $mode = method_exists($appointment, 'getResolvedMode') ? $appointment->getResolvedMode() : 'cabinet';
        $modeLabel = method_exists($appointment, 'getResolvedModeLabel') ? $appointment->getResolvedModeLabel() : 'Cabinet';
        $confirmationUrl = route('appointments.showPatient', ['token' => $appointment->token]);
        $visioUrl = $this->resolveVisioUrl($appointment);
        $location = $this->resolveLocation($appointment, $mode, $visioUrl);
        $description = $this->buildDescription(
            $appointment,
            $practitionerName,
            $clientName,
            $productName,
            $modeLabel,
            $confirmationUrl,
            $visioUrl
        );

        return [
            'start_utc' => $startUtc,
            'end_utc' => $endUtc,
            'practitioner_name' => $practitionerName,
            'client_name' => $clientName,
            'product_name' => $productName,
            'summary' => trim("{$productName} avec {$practitionerName}"),
            'description' => $description,
            'location' => $location,
            'confirmation_url' => $confirmationUrl,
            'visio_url' => $visioUrl,
            'event_url' => $visioUrl ?: $confirmationUrl,
        ];
    }

    private function buildDescription(
        Appointment $appointment,
        string $practitionerName,
        string $clientName,
        string $productName,
        string $modeLabel,
        string $confirmationUrl,
        ?string $visioUrl
    ): string {
        $lines = [
            'Rendez-vous AromaMade',
            'Prestation : ' . $productName,
            'Praticien : ' . $practitionerName,
            'Mode : ' . $modeLabel,
        ];

        if ($clientName !== '') {
            $lines[] = 'Client : ' . $clientName;
        }

        if ($appointment->appointment_date) {
            $lines[] = 'Date : ' . $appointment->appointment_date->copy()->timezone(config('app.timezone', 'Europe/Paris'))->format('d/m/Y à H:i');
        }

        if ((int) ($appointment->duration ?? 0) > 0) {
            $lines[] = 'Durée : ' . (int) $appointment->duration . ' minutes';
        }

        $mode = method_exists($appointment, 'getResolvedMode') ? $appointment->getResolvedMode() : 'cabinet';
        if ($mode === 'cabinet' && $appointment->practiceLocation?->full_address) {
            $lines[] = 'Adresse du cabinet : ' . $appointment->practiceLocation->full_address;
        } elseif (in_array($mode, ['domicile', 'entreprise'], true)) {
            $address = trim((string) ($appointment->address ?: $appointment->clientProfile?->address ?: ''));
            if ($address !== '') {
                $lines[] = ($mode === 'entreprise' ? 'Adresse de l’entreprise : ' : 'Adresse du domicile : ') . $address;
            }
        }

        if ($visioUrl) {
            $lines[] = 'Lien de visioconférence : ' . $visioUrl;
        }

        if (!empty($appointment->notes)) {
            $lines[] = 'Notes : ' . trim((string) $appointment->notes);
        }

        $lines[] = 'Voir le rendez-vous : ' . $confirmationUrl;

        return implode("\n", $lines);
    }

    private function resolveLocation(Appointment $appointment, string $mode, ?string $visioUrl): ?string
    {
        if ($mode === 'visio') {
            return $visioUrl ? 'En visio - lien dans la description' : 'En visio';
        }

        if ($mode === 'domicile') {
            $address = trim((string) ($appointment->address ?: $appointment->clientProfile?->address ?: ''));
            return $address !== '' ? 'À domicile - ' . $address : 'À domicile';
        }

        if ($mode === 'entreprise') {
            $address = trim((string) ($appointment->address ?: $appointment->clientProfile?->address ?: ''));
            return $address !== '' ? 'En entreprise - ' . $address : 'En entreprise';
        }

        $location = method_exists($appointment, 'getResolvedLocationString')
            ? $appointment->getResolvedLocationString()
            : null;

        return $location ? (string) $location : null;
    }

    private function resolveVisioUrl(Appointment $appointment): ?string
    {
        $isVisio = false;

        if ($appointment->product) {
            $isVisio = (bool) ($appointment->product->visio ?? false);
        }

        if (!$isVisio && in_array(($appointment->type ?? null), ['visio', 'video', 'teleconsultation'], true)) {
            $isVisio = true;
        }

        if (!$isVisio || !$appointment->meeting || empty($appointment->meeting->room_token)) {
            return null;
        }

        $room = (string) $appointment->meeting->room_token;
        $jitsi = app(JitsiJwtService::class);
        $jwt = $jitsi->makeJwtForClient([
            'room' => $room,
            'appointment' => $appointment,
        ]);

        $base = rtrim(config('services.jitsi.base_url', 'https://visio.aromamade.com'), '/');

        return "{$base}/{$room}?jwt={$jwt}";
    }

    private function escapeText(string $value): string
    {
        return str_replace(
            ["\\", ";", ",", "\r\n", "\r", "\n"],
            ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"],
            $value
        );
    }

    private function escapeParam(string $value): string
    {
        return str_replace(
            ["\\", ";", ",", "\r\n", "\r", "\n", '"'],
            ["\\\\", "\\;", "\\,", '', '', '', "'"],
            $value
        );
    }

    private function foldLine(string $line): string
    {
        $result = '';
        $remaining = $line;

        while (strlen($remaining) > 75) {
            $chunk = mb_strcut($remaining, 0, 73, 'UTF-8');
            $result .= $chunk . "\r\n ";
            $remaining = substr($remaining, strlen($chunk));
        }

        return $result . $remaining;
    }
}
