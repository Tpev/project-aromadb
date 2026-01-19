{{-- resources/views/corporate_clients/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de l\'entreprise cliente') }} - {{ $company->name }}
        </h2>
    </x-slot>

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <!-- Titre principal -->
            <h1 class="details-title">
                {{ $company->name }}
            </h1>

            @if($company->trade_name)
                <p class="text-center text-muted mb-2">
                    Nom commercial : <strong>{{ $company->trade_name }}</strong>
                </p>
            @endif

            <hr class="my-6">

            {{-- ==========================
                 SECTION: Infos entreprise (cartes)
                 ========================== --}}
            <div class="profile-info-boxes">
                <div class="profile-box">
                    <i class="fas fa-building icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('Raison sociale') }}</p>
                        <p class="profile-value">{{ $company->name }}</p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-id-badge icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('Nom commercial') }}</p>
                        <p class="profile-value">{{ $company->trade_name ?? 'Non spécifié' }}</p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-id-card icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('SIRET') }}</p>
                        <p class="profile-value">{{ $company->siret ?? 'Non spécifié' }}</p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-file-invoice icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('TVA intracommunautaire') }}</p>
                        <p class="profile-value">{{ $company->vat_number ?? 'Non spécifié' }}</p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('Adresse de facturation') }}</p>
                        <p class="profile-value">
                            @if($company->billing_address || $company->billing_city || $company->billing_zip)
                                {{ $company->billing_address }}<br>
                                {{ $company->billing_zip }} {{ $company->billing_city }}<br>
                                {{ $company->billing_country }}
                            @else
                                {{ 'Non spécifiée' }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-envelope icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('Email facturation') }}</p>
                        <p class="profile-value">{{ $company->billing_email ?? 'Non spécifié' }}</p>
                    </div>
                </div>

                <div class="profile-box">
                    <i class="fas fa-phone icon"></i>
                    <div class="profile-details">
                        <p class="profile-label">{{ __('Téléphone facturation') }}</p>
                        <p class="profile-value">{{ $company->billing_phone ?? 'Non spécifié' }}</p>
                    </div>
                </div>
            </div>

            {{-- ==========================
                 SECTION: Contact principal
                 ========================== --}}
            <section class="bg-white p-6 rounded-2xl shadow mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">👤 Contact principal</h2>

                @if($company->main_contact_first_name || $company->main_contact_last_name || $company->main_contact_email || $company->main_contact_phone)
                    <div class="profile-info-boxes">
                        <div class="profile-box">
                            <i class="fas fa-user icon"></i>
                            <div class="profile-details">
                                <p class="profile-label">{{ __('Nom du contact') }}</p>
                                <p class="profile-value">
                                    {{ trim($company->main_contact_first_name.' '.$company->main_contact_last_name) ?: 'Non spécifié' }}
                                </p>
                            </div>
                        </div>

                        <div class="profile-box">
                            <i class="fas fa-envelope icon"></i>
                            <div class="profile-details">
                                <p class="profile-label">{{ __('Email du contact') }}</p>
                                <p class="profile-value">{{ $company->main_contact_email ?? 'Non spécifié' }}</p>
                            </div>
                        </div>

                        <div class="profile-box">
                            <i class="fas fa-phone icon"></i>
                            <div class="profile-details">
                                <p class="profile-label">{{ __('Téléphone du contact') }}</p>
                                <p class="profile-value">{{ $company->main_contact_phone ?? 'Non spécifié' }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">
                        Aucun contact principal n’est encore défini pour cette entreprise.
                    </p>
                @endif
            </section>

            {{-- ==========================
                 SECTION: Notes internes
                 ========================== --}}
            <section class="bg-white p-6 rounded-2xl shadow mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📝 Notes internes</h2>

                @if($company->notes)
                    <p class="text-sm text-gray-800 whitespace-pre-line">
                        {{ $company->notes }}
                    </p>
                @else
                    <p class="text-muted text-sm">
                        Aucune note interne pour cette entreprise. Vous pouvez en ajouter via le formulaire de modification.
                    </p>
                @endif
            </section>

            {{-- ==========================
                 SECTION: Employés / Clients individuels liés
                 ========================== --}}
            <section class="bg-white p-6 rounded-2xl shadow mt-6">
                <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
                    <h2 class="text-xl font-semibold text-gray-800">
                        👥 {{ __('Clients individuels rattachés (employés)') }}
                    </h2>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('client_profiles.create') }}?company_id={{ $company->id }}"
                           class="btn-primary">
                            + Créer un client lié
                        </a>
                    </div>
                </div>

                @if($company->clientProfiles->isEmpty())
                    <p class="text-muted text-sm">
                        Aucun client individuel n’est encore rattaché à cette entreprise.
                    </p>
                @else
                    <div class="table-responsive mx-auto">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Dernière mise à jour</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->clientProfiles as $client)
                                    <tr>
                                        <td>
                                            {{ $client->first_name }} {{ $client->last_name }}
                                        </td>
                                        <td>{{ $client->email ?? '—' }}</td>
                                        <td>{{ $client->phone ?? '—' }}</td>
                                        <td>
                                            {{ optional($client->updated_at)->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('client_profiles.show', $client->id) }}"
                                               class="btn-primary">
                                                Ouvrir le dossier
                                            </a>

                                            <a href="{{ route('client_profiles.edit', $client->id) }}"
                                               class="btn-secondary">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- ==========================
                 SECTION: Factures liées à cette entreprise
                 ========================== --}}
            @php
                // Factures via clients individuels rattachés
                $linkedInvoices = $company->clientProfiles
                    ? $company->clientProfiles->flatMap(function ($cp) {
                        return $cp->invoices ?? collect();
                    })
                    : collect();

                // Factures directes facturées à l'entreprise (Option A) — passé par le controller
                $directInvoices = $directInvoices ?? collect();

                // Merge + ne garder que les factures (pas les devis)
                $invoices = $linkedInvoices
                    ->merge($directInvoices)
                    ->filter(function ($inv) {
                        return ($inv->type ?? 'invoice') === 'invoice';
                    })
                    // tri stable: dernier créé en premier
                    ->sortByDesc('id');
            @endphp

            <section class="bg-white p-6 rounded-2xl shadow mt-6">
                <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
                    <h2 class="text-xl font-semibold text-gray-800">
                        💶 {{ __('Factures liées à cette entreprise') }}
                    </h2>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('invoices.create') }}?company_id={{ $company->id }}"
                           class="btn-primary">
                            + Créer une facture pour cette entreprise
                        </a>
                    </div>
                </div>

                @if($invoices->isEmpty())
                    <p class="text-muted text-sm">
                        Aucune facture n’est encore associée à cette entreprise.
                    </p>
                @else
                    <div class="table-responsive mx-auto">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>N° facture</th>
                                    <th>Bénéficiaire</th>
                                    <th>Date</th>
                                    <th>Total TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    @php
                                        $benef = '—';
                                        if (!empty($invoice->corporate_client_id)) {
                                            // facture directe entreprise -> on affiche l'entreprise
                                            $benef = $company->trade_name ?: $company->name;
                                        } elseif ($invoice->clientProfile) {
                                            $benef = trim(($invoice->clientProfile->first_name ?? '').' '.($invoice->clientProfile->last_name ?? '')) ?: '—';
                                        }
                                    @endphp
                                    <tr>
                                        <td>#{{ $invoice->invoice_number }}</td>
                                        <td>{{ $benef }}</td>
                                        <td>
                                            {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td>
                                            {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €
                                        </td>
                                        <td>{{ ucfirst($invoice->status) }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice->id) }}"
                                               class="btn-primary">
                                                Voir la facture
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- ==========================
                 Actions bas de page
                 ========================== --}}
            <div class="row mt-6">
                <div class="col-md-12 text-center">
                    <a href="{{ route('corporate-clients.index') }}" class="btn-primary">
                        {{ __('Retour à la liste des entreprises') }}
                    </a>
                    <a href="{{ route('corporate-clients.edit', $company) }}" class="btn-secondary">
                        {{ __('Modifier l\'entreprise') }}
                    </a>
                    <form action="{{ route('corporate-clients.destroy', $company) }}"
                          method="POST"
                          style="display: inline-block;"
                          onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette entreprise ? Les liens avec les clients individuels seront retirés.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            {{ __('Supprimer l\'entreprise') }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* ====== Design tokens ====== */
        :root{
            --brand:#647a0b;
            --brand-2:#854f38;
            --ink:#222;
            --muted:#666;
            --bg:#f9f9f9;
            --white:#fff;
            --radius:12px;
            --shadow:0 6px 18px rgba(0,0,0,.08);
        }

        .text-muted{ color:var(--muted); }

        /* ====== Container / Section ====== */
        .container{
            max-width:100%;
            width:100%;
            padding:0 1rem;
        }
        @media (min-width:768px){
            .container{ padding:0 2rem; }
        }

        .details-container{
            background:var(--bg);
            border-radius:var(--radius);
            padding:1.25rem;
            box-shadow:var(--shadow);
            margin:0 auto;
        }
        @media (min-width:992px){
            .details-container{ padding:2rem; max-width:1100px; }
        }

        .details-title{
            font-size:clamp(1.35rem, 1.2rem + .8vw, 2rem);
            font-weight:800;
            color:var(--brand);
            margin:0 0 1rem;
            text-align:center;
        }

        /* ====== Profile info cards (grid) ====== */
        .profile-info-boxes{
            display:grid;
            grid-template-columns:1fr;
            gap:14px;
        }
        @media (min-width:640px){
            .profile-info-boxes{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @media (min-width:1024px){
            .profile-info-boxes{ grid-template-columns:repeat(3,minmax(0,1fr)); }
        }

        .profile-box{
            display:flex; align-items:flex-start; gap:14px;
            background:var(--white);
            border-radius:var(--radius);
            box-shadow:0 3px 10px rgba(0,0,0,.05);
            padding:16px 18px;
            transition:transform .2s ease, box-shadow .2s ease;
        }
        @media (hover:hover){
            .profile-box:hover{
                transform:translateY(-2px);
                box-shadow:0 8px 20px rgba(0,0,0,.08);
            }
        }

        .icon{
            font-size:1.6rem;
            color:var(--brand-2);
            margin-top:2px;
        }
        .profile-details{text-align:left;}
        .profile-label{
            font-weight:700;
            color:var(--brand);
            margin:0 0 2px;
        }
        .profile-value{
            color:var(--ink);
            font-size:.975rem;
            line-height:1.35;
        }

        /* ====== Tables ====== */
        .table-responsive{
            width:100%;
            max-width:100%;
            overflow-x:auto;
            -webkit-overflow-scrolling:touch;
            background:var(--white);
            border-radius:10px;
            box-shadow:var(--shadow);
            padding:0;
            margin:0 auto;
        }
        .table{
            width:100%;
            border-collapse:collapse;
            min-width:720px;
        }
        .table thead{
            background:var(--brand);
            color:#fff;
        }
        .table th,
        .table td{
            padding:.85rem .9rem;
            text-align:left;
            border-bottom:1px solid #eee;
            white-space:nowrap;
        }
        .table tbody tr:nth-child(odd){ background:#fafafa; }
        .table tbody tr:hover{ background:#f3f7ea; }

        /* ====== Buttons ====== */
        .btn-primary,
        .btn-secondary,
        .btn-danger{
            display:inline-block;
            cursor:pointer;
            text-decoration:none;
            padding:.6rem 1rem;
            border-radius:8px;
            font-weight:700;
            line-height:1;
            transition:filter .15s ease, transform .05s ease;
        }
        .btn-primary{
            background:var(--brand);
            color:#fff;
            border:0;
        }
        .btn-primary:hover{ filter:brightness(.95); }

        .btn-secondary{
            background:transparent;
            color:var(--brand-2);
            border:1px solid var(--brand-2);
        }
        .btn-secondary:hover{
            background:var(--brand-2);
            color:#fff;
        }

        .btn-danger{
            background:#e3342f;
            color:#fff;
            border:0;
        }
        .btn-danger:hover{
            background:#cc1f1a;
        }

        .btn-primary:active,
        .btn-secondary:active,
        .btn-danger:active{
            transform:translateY(1px);
        }

        /* ====== White sections (cards) ====== */
        section.bg-white{
            background:#fff;
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            padding:1rem;
            margin-top:1.25rem;
        }
        @media (min-width:992px){
            section.bg-white{ padding:1.25rem 1.5rem; }
        }

        /* ====== Mobile tweaks ====== */
        @media (max-width:480px){
            .details-container{ padding:1rem; }
            .btn-primary,
            .btn-secondary,
            .btn-danger{
                width:100%;
                text-align:center;
                margin-bottom:.35rem;
            }
        }
    </style>
</x-app-layout>
