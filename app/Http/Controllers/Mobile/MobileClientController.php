<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\CorporateClient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MobileClientController extends Controller
{
    use AuthorizesRequests;

    public function create()
    {
        return view('mobile.clients.form', [
            'title' => 'Nouvelle fiche client',
            'clientProfile' => new ClientProfile(),
            'companies' => $this->ownedCompanies(),
            'selectedCompanyId' => request('company_id'),
            'action' => route('mobile.clients.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $clientProfile = ClientProfile::create($this->validatedClientPayload($request));

        return redirect()
            ->route('mobile.clients.show', $clientProfile)
            ->with('success', 'Fiche client creee.');
    }

    public function edit(ClientProfile $clientProfile)
    {
        $this->authorize('update', $clientProfile);

        return view('mobile.clients.form', [
            'title' => 'Modifier la fiche client',
            'clientProfile' => $clientProfile,
            'companies' => $this->ownedCompanies(),
            'selectedCompanyId' => $clientProfile->company_id,
            'action' => route('mobile.clients.update', $clientProfile),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('update', $clientProfile);

        $clientProfile->update($this->validatedClientPayload($request, includeOwner: false));

        return redirect()
            ->route('mobile.clients.show', $clientProfile)
            ->with('success', 'Fiche client mise a jour.');
    }

    private function validatedClientPayload(Request $request, bool $includeOwner = true): array
    {
        $userId = Auth::id();

        $payload = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
            'birthdate' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'first_name_billing' => ['nullable', 'string', 'max:255'],
            'last_name_billing' => ['nullable', 'string', 'max:255'],
            'company_id' => [
                'nullable',
                Rule::exists('corporate_clients', 'id')->where('user_id', $userId),
            ],
        ]);

        if ($includeOwner) {
            $payload['user_id'] = $userId;
        }

        return $payload;
    }

    private function ownedCompanies()
    {
        return CorporateClient::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }
}
