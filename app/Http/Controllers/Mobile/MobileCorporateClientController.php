<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\CorporateClient;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MobileCorporateClientController extends Controller
{
    public function index()
    {
        $companies = CorporateClient::query()
            ->withCount('clientProfiles')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('mobile.corporate-clients.index', compact('companies'));
    }

    public function create()
    {
        return view('mobile.corporate-clients.form', [
            'title' => 'Nouvelle entreprise',
            'company' => new CorporateClient([
                'billing_country' => 'France',
            ]),
            'action' => route('mobile.corporate-clients.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);
        $payload['user_id'] = Auth::id();

        $company = CorporateClient::create($payload);

        return redirect()
            ->route('mobile.corporate-clients.show', $company)
            ->with('success', 'Entreprise creee.');
    }

    public function show(CorporateClient $corporateClient)
    {
        $this->ensureOwnsCompany($corporateClient);

        $company = $corporateClient->load([
            'clientProfiles' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
            'clientProfiles.invoices.clientProfile',
        ]);
        $company->loadCount('clientProfiles');

        $invoices = $this->companyInvoices($company);

        return view('mobile.corporate-clients.show', compact('company', 'invoices'));
    }

    public function edit(CorporateClient $corporateClient)
    {
        $this->ensureOwnsCompany($corporateClient);

        return view('mobile.corporate-clients.form', [
            'title' => 'Modifier l entreprise',
            'company' => $corporateClient,
            'action' => route('mobile.corporate-clients.update', $corporateClient),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, CorporateClient $corporateClient)
    {
        $this->ensureOwnsCompany($corporateClient);

        $corporateClient->update($this->validatedPayload($request));

        return redirect()
            ->route('mobile.corporate-clients.show', $corporateClient)
            ->with('success', 'Entreprise mise a jour.');
    }

    public function destroy(CorporateClient $corporateClient)
    {
        $this->ensureOwnsCompany($corporateClient);

        ClientProfile::where('company_id', $corporateClient->id)->update(['company_id' => null]);
        $corporateClient->delete();

        return redirect()
            ->route('mobile.corporate-clients.index')
            ->with('success', 'Entreprise supprimee.');
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_country' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'billing_phone' => ['nullable', 'string', 'max:50'],
            'main_contact_first_name' => ['nullable', 'string', 'max:255'],
            'main_contact_last_name' => ['nullable', 'string', 'max:255'],
            'main_contact_email' => ['nullable', 'email', 'max:255'],
            'main_contact_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function companyInvoices(CorporateClient $company): Collection
    {
        $linkedInvoices = $company->clientProfiles
            ->flatMap(fn (ClientProfile $client) => $client->invoices)
            ->filter(fn (Invoice $invoice) => (int) $invoice->user_id === (int) Auth::id());

        $directInvoices = Invoice::query()
            ->with('clientProfile')
            ->where('user_id', Auth::id())
            ->where('corporate_client_id', $company->id)
            ->orderByDesc('id')
            ->get();

        return $linkedInvoices
            ->merge($directInvoices)
            ->unique('id')
            ->sortByDesc('id')
            ->values();
    }

    private function ensureOwnsCompany(CorporateClient $company): void
    {
        if ((int) $company->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
