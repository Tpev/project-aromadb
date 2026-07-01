<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmargementEmailJob;
use App\Models\Appointment;
use App\Models\Emargement;
use App\Services\EmargementService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobileEmargementController extends Controller
{
    public function __construct(private EmargementService $service) {}

    public function index()
    {
        $appointments = Appointment::query()
            ->with(['clientProfile:id,first_name,last_name,email,phone,user_id', 'product:id,name,requires_emargement'])
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('requires_emargement', true)
                    ->orWhereHas('product', fn ($product) => $product->where('requires_emargement', true))
                    ->orWhereIn('id', Emargement::query()->select('appointment_id'));
            })
            ->orderByDesc('appointment_date')
            ->limit(80)
            ->get();

        $emargements = Emargement::query()
            ->where('therapist_id', Auth::id())
            ->whereIn('appointment_id', $appointments->pluck('id'))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('appointment_id');

        $rows = $appointments->map(function (Appointment $appointment) use ($emargements) {
            $latest = $emargements->get($appointment->id)?->first();

            return [
                'appointment' => $appointment,
                'emargement' => $latest,
                'requires' => $this->appointmentRequiresEmargement($appointment),
                'clientName' => $this->clientName($appointment),
                'clientEmail' => $appointment->clientProfile?->email,
                'canSend' => $this->appointmentRequiresEmargement($appointment)
                    && ! $latest
                    && filled($appointment->clientProfile?->email),
                'canResend' => $latest
                    && $latest->status !== 'signed'
                    && ! $latest->isExpired()
                    && filled($latest->client_email),
                'canDownload' => $latest
                    && filled($latest->pdf_path)
                    && Storage::disk('public')->exists($latest->pdf_path),
            ];
        });

        return view('mobile.emargements.index', [
            'rows' => $rows,
            'totalRequired' => $rows->where('requires', true)->count(),
            'pendingTotal' => $rows->filter(fn ($row) => in_array($row['emargement']?->status, ['pending', 'expired'], true) || $row['canSend'])->count(),
            'signedTotal' => $rows->filter(fn ($row) => $row['emargement']?->status === 'signed')->count(),
        ]);
    }

    public function send(Appointment $appointment)
    {
        $this->ensureOwnsAppointment($appointment);
        $appointment->loadMissing(['clientProfile', 'product', 'user']);

        if (! $this->appointmentRequiresEmargement($appointment)) {
            return redirect()
                ->route('mobile.emargements.index')
                ->with('error', 'Cette prestation ne requiert pas d emargement.');
        }

        if (! $appointment->clientProfile?->email) {
            return redirect()
                ->route('mobile.emargements.index')
                ->with('error', 'Email client manquant.');
        }

        $emargement = $this->service->createForAppointment($appointment);
        $appointment->update(['emargement_sent' => true]);

        Bus::dispatch(new SendEmargementEmailJob($emargement));

        return redirect()
            ->route('mobile.emargements.index')
            ->with('success', 'Feuille d emargement envoyee.');
    }

    public function resend(Emargement $emargement)
    {
        $this->ensureOwnsEmargement($emargement);

        $emargement = $this->service->rotateToken($emargement);
        Bus::dispatch(new SendEmargementEmailJob($emargement));

        return redirect()
            ->route('mobile.emargements.index')
            ->with('success', 'Lien de signature renvoye.');
    }

    public function download(Emargement $emargement)
    {
        $this->ensureOwnsEmargement($emargement);

        abort_if(! $emargement->pdf_path || ! Storage::disk('public')->exists($emargement->pdf_path), 404);

        return Storage::disk('public')->download($emargement->pdf_path, 'Emargement-' . $emargement->id . '.pdf');
    }

    private function appointmentRequiresEmargement(Appointment $appointment): bool
    {
        return (bool) ($appointment->requires_emargement || $appointment->product?->requires_emargement);
    }

    private function clientName(Appointment $appointment): string
    {
        $client = $appointment->clientProfile;
        $name = trim(($client?->first_name ?? '') . ' ' . ($client?->last_name ?? ''));

        return $name !== '' ? $name : 'Client sans nom';
    }

    private function ensureOwnsAppointment(Appointment $appointment): void
    {
        abort_unless((int) $appointment->user_id === (int) Auth::id(), 403);
    }

    private function ensureOwnsEmargement(Emargement $emargement): void
    {
        $emargement->loadMissing('appointment');
        abort_unless((int) $emargement->therapist_id === (int) Auth::id(), 403);
        abort_unless($emargement->appointment && (int) $emargement->appointment->user_id === (int) Auth::id(), 403);
    }
}
