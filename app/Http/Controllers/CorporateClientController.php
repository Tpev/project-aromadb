<?php

namespace App\Http\Controllers;

use App\Models\CorporateClient;
use App\Models\ClientProfile;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporateClientController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
    }

    protected function ownedCompaniesQuery()
    {
        return CorporateClient::where('user_id', Auth::id());
    }

    public function index()
    {
        $companies = $this->ownedCompaniesQuery()
            ->orderBy('name')
            ->paginate(15);

        return view('corporate_clients.index', compact('companies'));
    }

    public function create()
    {
        return view('corporate_clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:255'],
            'trade_name'               => ['nullable', 'string', 'max:255'],
            'siret'                    => ['nullable', 'string', 'max:255'],
            'vat_number'               => ['nullable', 'string', 'max:255'],
            'billing_address'          => ['nullable', 'string', 'max:255'],
            'billing_zip'              => ['nullable', 'string', 'max:20'],
            'billing_city'             => ['nullable', 'string', 'max:255'],
            'billing_country'          => ['nullable', 'string', 'max:255'],
            'billing_email'            => ['nullable', 'email', 'max:255'],
            'billing_phone'            => ['nullable', 'string', 'max:50'],
            'main_contact_first_name'  => ['nullable', 'string', 'max:255'],
            'main_contact_last_name'   => ['nullable', 'string', 'max:255'],
            'main_contact_email'       => ['nullable', 'email', 'max:255'],
            'main_contact_phone'       => ['nullable', 'string', 'max:50'],
            'notes'                    => ['nullable', 'string'],
        ]);

        $data['user_id'] = Auth::id();

        $company = CorporateClient::create($data);

        return redirect()
            ->route('corporate-clients.show', $company)
            ->with('success', 'Entreprise créée avec succès.');
    }

    public function show(CorporateClient $corporateClient)
    {
        $company = $this->ownedCompaniesQuery()
            // also load invoices for linked individuals (so the view doesn't trigger N+1)
            ->with(['clientProfiles', 'clientProfiles.invoices'])
            ->findOrFail($corporateClient->id);

        // Invoices/quotes billed directly to the corporate entity (Option A)
        $directInvoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->where('corporate_client_id', $company->id)
            ->orderByDesc('id')
            ->get();

        return view('corporate_clients.show', compact('company', 'directInvoices'));
    }

    public function edit(CorporateClient $corporateClient)
    {
        $company = $this->ownedCompaniesQuery()
            ->findOrFail($corporateClient->id);

        return view('corporate_clients.edit', compact('company'));
    }

    public function update(Request $request, CorporateClient $corporateClient)
    {
        $company = $this->ownedCompaniesQuery()
            ->findOrFail($corporateClient->id);

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:255'],
            'trade_name'               => ['nullable', 'string', 'max:255'],
            'siret'                    => ['nullable', 'string', 'max:255'],
            'vat_number'               => ['nullable', 'string', 'max:255'],
            'billing_address'          => ['nullable', 'string', 'max:255'],
            'billing_zip'              => ['nullable', 'string', 'max:20'],
            'billing_city'             => ['nullable', 'string', 'max:255'],
            'billing_country'          => ['nullable', 'string', 'max:255'],
            'billing_email'            => ['nullable', 'email', 'max:255'],
            'billing_phone'            => ['nullable', 'string', 'max:50'],
            'main_contact_first_name'  => ['nullable', 'string', 'max:255'],
            'main_contact_last_name'   => ['nullable', 'string', 'max:255'],
            'main_contact_email'       => ['nullable', 'email', 'max:255'],
            'main_contact_phone'       => ['nullable', 'string', 'max:50'],
            'notes'                    => ['nullable', 'string'],
        ]);

        $company->update($data);

        return redirect()
            ->route('corporate-clients.show', $company)
            ->with('success', 'Entreprise mise à jour avec succès.');
    }

    public function destroy(CorporateClient $corporateClient)
    {
        $company = $this->ownedCompaniesQuery()
            ->findOrFail($corporateClient->id);

        // Option : détacher les ClientProfile avant
        ClientProfile::where('company_id', $company->id)->update(['company_id' => null]);

        $company->delete();

        return redirect()
            ->route('corporate-clients.index')
            ->with('success', 'Entreprise supprimée avec succès.');
    }
}
