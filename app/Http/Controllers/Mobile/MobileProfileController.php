<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('mobile.profile.index', [
            'user' => $user,
            'services' => $this->servicesArray($user->services),
        ]);
    }

    public function edit()
    {
        $user = Auth::user();

        abort_unless($user?->isTherapist(), 403);

        return view('mobile.profile.form', [
            'user' => $user,
            'services' => $this->servicesArray($user->services),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        abort_unless($user?->isTherapist(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'about' => ['nullable', 'string'],
            'profile_description' => ['nullable', 'string', 'max:1000'],
            'services_text' => ['nullable', 'string', 'max:1000'],
            'minimum_notice_hours' => ['nullable', 'integer', 'min:0'],
            'buffer_time_between_appointments' => ['nullable', 'integer', 'min:0'],
            'global_daily_booking_limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'cancellation_notice_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
        ]);

        $originalCompanyName = $user->company_name;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'company_phone' => $validated['company_phone'] ?? null,
            'about' => $validated['about'] ?? null,
            'profile_description' => $validated['profile_description'] ?? null,
            'minimum_notice_hours' => $validated['minimum_notice_hours'] ?? null,
            'buffer_time_between_appointments' => $validated['buffer_time_between_appointments'] ?? null,
            'global_daily_booking_limit' => $validated['global_daily_booking_limit'] ?? null,
        ]);

        $user->cancellation_notice_hours = (int) ($validated['cancellation_notice_hours'] ?? 0);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->share_address_publicly = $request->boolean('share_address_publicly');
        $user->share_email_publicly = $request->boolean('share_email_publicly');
        $user->share_phone_publicly = $request->boolean('share_phone_publicly');
        $user->accept_online_appointments = $request->boolean('accept_online_appointments');

        $user->services = json_encode($this->servicesFromText($validated['services_text'] ?? ''), JSON_UNESCAPED_UNICODE);

        if (
            filled($validated['company_name'] ?? null)
            && $originalCompanyName !== ($validated['company_name'] ?? null)
        ) {
            $user->slug = User::createUniqueSlug($validated['company_name'], $user->id);
        }

        $user->save();

        return redirect()
            ->route('mobile.profile.index')
            ->with('success', 'Profil mis a jour.');
    }

    private function servicesArray(mixed $services): array
    {
        if (is_array($services)) {
            return array_values(array_filter($services));
        }

        $decoded = json_decode((string) $services, true);

        return is_array($decoded) ? array_values(array_filter($decoded)) : [];
    }

    private function servicesFromText(?string $servicesText): array
    {
        return collect(preg_split('/[\r\n,;]+/', (string) $servicesText))
            ->map(fn (string $service) => trim($service))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
