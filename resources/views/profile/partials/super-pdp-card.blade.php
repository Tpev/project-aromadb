@php
    $connection = $superPdpConnection ?? null;
    $isConnected = $connection?->isConnected() ?? false;
    $receiveInApp = old('receive_in_app', $connection?->receiving_invoices_enabled ?? true);
    $status = $connection?->status ?? 'not_started';
    $statusLabel = match ($status) {
        'connected' => __('Connecté'),
        'authorization_started' => __('Onboarding démarré'),
        'error' => __('Erreur'),
        'revoked' => __('Déconnecté'),
        default => __('Non connecté'),
    };
    $statusClass = match ($status) {
        'connected' => 'background:#ecfccb;color:#3f6212;border-color:#bef264;',
        'error' => 'background:#fef2f2;color:#991b1b;border-color:#fecaca;',
        'authorization_started' => 'background:#fff7ed;color:#9a3412;border-color:#fed7aa;',
        default => 'background:#f8fafc;color:#475569;border-color:#e2e8f0;',
    };
@endphp

<div class="details-box mt-5" style="border:1px solid #d9e8aa;border-radius:18px;background:linear-gradient(135deg,#fbfff0 0%,#ffffff 55%,#f7faf1 100%);padding:22px;box-shadow:0 12px 30px rgba(100,122,11,.08);">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-[0.18em] font-bold" style="color:#854f38;">
                {{ __('Bac à sable') }}
            </div>
            <h3 class="text-xl font-bold mt-1" style="color:#334155;">
                {{ __('Facturation électronique avec SUPER PDP') }}
            </h3>
            <p class="text-sm text-gray-600 mt-2 max-w-2xl">
                {{ __('Testez l’onboarding OAuth 2.1 SUPER PDP, puis choisissez si AromaMade doit aussi afficher les factures d’achat reçues dans l’application.') }}
            </p>
        </div>

        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold" style="{{ $statusClass }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="grid md:grid-cols-3 gap-3 mt-5">
        <div class="rounded-xl border border-lime-100 bg-white/80 p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Mode') }}</div>
            <div class="font-semibold text-gray-900 mt-1">{{ __('Sandbox uniquement') }}</div>
            <p class="text-xs text-gray-500 mt-1">{{ __('Invisible pour les autres comptes pour le moment.') }}</p>
        </div>
        <div class="rounded-xl border border-lime-100 bg-white/80 p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Entreprise SUPER PDP') }}</div>
            <div class="font-semibold text-gray-900 mt-1">
                {{ $connection?->super_pdp_company_name ?: __('À connecter') }}
            </div>
            @if($connection?->super_pdp_company_number)
                <p class="text-xs text-gray-500 mt-1">
                    {{ $connection->super_pdp_company_number_scheme }} · {{ $connection->super_pdp_company_number }}
                </p>
            @else
                <p class="text-xs text-gray-500 mt-1">{{ __('Sera renseigné après l’autorisation.') }}</p>
            @endif
        </div>
        <div class="rounded-xl border border-lime-100 bg-white/80 p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Réception') }}</div>
            <div class="font-semibold text-gray-900 mt-1">
                {{ $connection?->receiving_invoices_enabled ? __('Activée dans AromaMade') : __('Non activée') }}
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $connection?->last_synced_at ? __('Dernière synchro : ') . $connection->last_synced_at->format('d/m/Y H:i') : __('Aucune synchro pour l’instant.') }}
            </p>
        </div>
    </div>

    @if($connection?->last_error)
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ __('Dernière erreur SUPER PDP :') }} {{ $connection->last_error }}
        </div>
    @endif

    @if(!$superPdpConfigured)
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <strong>{{ __('Configuration requise :') }}</strong>
            {{ __('ajoutez le client_id, client_secret et l’URL de redirection sandbox dans le fichier .env avant de lancer l’onboarding.') }}
        </div>
    @endif

    @unless($isConnected)
        <div class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
            <label class="flex items-start gap-3">
                <input type="checkbox"
                       name="receive_in_app"
                       value="1"
                       form="superPdpConnectForm"
                       class="mt-1 form-checkbox h-5 w-5 text-green-500"
                    {{ $receiveInApp ? 'checked' : '' }}>
                <span>
                    <span class="font-semibold text-gray-900">{{ __('Recevoir aussi les factures d’achat dans AromaMade') }}</span>
                    <span class="block text-sm text-gray-500 mt-1">
                        {{ __('Si cette option est cochée, le tunnel SUPER PDP demandera l’inscription à l’annuaire pour la réception, puis AromaMade pourra synchroniser les factures reçues.') }}
                    </span>
                </span>
            </label>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit"
                        form="superPdpConnectForm"
                        class="btn-primary"
                    {{ $superPdpConfigured ? '' : 'disabled' }}>
                    {{ __('Démarrer l’onboarding SUPER PDP') }}
                </button>
                <a href="https://www.superpdp.tech/documentation/4#authorization-code"
                   target="_blank"
                   rel="noopener"
                   class="btn-secondary">
                    {{ __('Voir la doc OAuth') }}
                </a>
            </div>
        </div>
    @else
        <div class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
            <label class="flex items-start gap-3">
                <input type="checkbox"
                       name="receiving_invoices_enabled"
                       value="1"
                       form="superPdpPreferenceForm"
                       class="mt-1 form-checkbox h-5 w-5 text-green-500"
                    {{ $connection->receiving_invoices_enabled ? 'checked' : '' }}>
                <span>
                    <span class="font-semibold text-gray-900">{{ __('Afficher les factures reçues dans AromaMade') }}</span>
                    <span class="block text-sm text-gray-500 mt-1">
                        {{ __('Vous pouvez couper l’affichage côté AromaMade sans révoquer l’accès SUPER PDP.') }}
                    </span>
                </span>
            </label>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit" form="superPdpPreferenceForm" class="btn-primary">
                    {{ __('Enregistrer la préférence') }}
                </button>
                <a href="{{ route('super-pdp.received-invoices.index') }}" class="btn-secondary">
                    {{ __('Voir les factures reçues') }}
                </a>
                <button type="submit"
                        form="superPdpDisconnectForm"
                        class="btn btn-danger"
                        onclick="return confirm('{{ __('Déconnecter SUPER PDP sandbox ?') }}')">
                    {{ __('Déconnecter SUPER PDP') }}
                </button>
            </div>
        </div>
    @endunless
</div>
