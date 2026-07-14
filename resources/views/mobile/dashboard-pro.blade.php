{{-- resources/views/mobile/dashboard-pro.blade.php --}}
@php
    $pageTitle       = 'Tableau de bord PRO';
    $pageDescription = "Suivez vos rendez-vous, factures et progression d'onboarding sur Olithea.";
@endphp

<x-mobile-layout :title="$pageTitle">
    @section('title', $pageTitle)
    @section('meta_description', $pageDescription)

    <div
        class="min-h-screen flex flex-col px-5 py-6"
        style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);"
    >
        <div class="w-full max-w-lg mx-auto space-y-6">

            {{-- ───────────────────── TOP HEADER ───────────────────── --}}
            <div class="flex items-center justify-between gap-3">
                <div class="space-y-1 min-w-0">
                    <h1 class="text-2xl font-extrabold text-[#647a0b] leading-snug break-words">
                        {{ __('Tableau de bord PRO') }}
                    </h1>
                    <p class="text-sm text-gray-700 leading-snug break-words">
                        {{ __('Vue d’ensemble de votre activité : clients, rendez-vous, factures et plus encore.') }}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <div class="text-xs text-gray-500">
                        {{ __('Connecté en tant que') }}
                    </div>
                    <div class="text-sm font-semibold text-gray-800 break-words">
                        {{ $therapist->company_name ?? $therapist->name }}
                    </div>
                </div>
            </div>

            @include('partials.e-invoicing-banner')

            {{-- ===================================================== --}}
            {{--                 ONBOARDING SEQUENTIEL (MOBILE)       --}}
            {{-- ===================================================== --}}
            @if(!$onboardingCompleted)
                @php
                    $currentStep = 1;

                    if ($step1Completion == 100 && $step2Completion < 100) {
                        $currentStep = 2;
                    } elseif ($step1Completion == 100 && $step2Completion == 100 && !$skipStep3 && $step3Completion < 100) {
                        $currentStep = 3;
                    } elseif (
                        $step1Completion == 100 &&
                        $step2Completion == 100 &&
                        ($step3Completion == 100 || $skipStep3) &&
                        !$skipStep4 &&
                        $step4Completion < 100
                    ) {
                        $currentStep = 4;
                    }
                @endphp

                <x-ts-card class="rounded-3xl border border-[#edf1df] bg-white/95 px-4 py-4 space-y-4">
                    {{-- Bandeau titre + progression globale --}}
                    <div class="space-y-2">
                        <h2 class="text-base font-semibold text-[#647a0b] leading-snug break-words">
                            👋 {{ __('Bienvenue sur Olithea') }}
                        </h2>
                        <p class="text-xs text-gray-600 leading-relaxed break-words">
                            {{ __('Complétez ces étapes pour être prêt à recevoir des réservations en ligne et utiliser toutes les fonctionnalités.') }}
                        </p>

                        <div class="mt-1">
                            <div class="flex items-center justify-between text-[11px] text-gray-600 mb-1">
                                <span>{{ __('Progression globale') }}</span>
                                <span class="font-semibold">{{ $globalCompletion }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 bg-[#647a0b] rounded-full transition-all duration-300"
                                     style="width: {{ $globalCompletion }}%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Étape active (vue compacte mobile) --}}
                    <div class="mt-2 space-y-3">
                        @if($currentStep === 1)
                            <div id="step1" class="p-3 rounded-2xl bg-[#fafcf5] border border-[#e3ecd0] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ __('Étape 1 · Profil & informations') }}
                                    </div>
                                    <span class="text-xs font-semibold text-[#647a0b]">
                                        {{ $step1Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Complétez vos informations professionnelles (nom, adresse, description, services…) pour présenter un profil clair à vos clients.') }}
                                </p>
                                <a href="{{ route('mobile.entry') }}"
                                   class="inline-flex items-center justify-center mt-1 text-xs font-semibold px-3 py-1.5 rounded-full bg-[#647a0b] text-white">
                                    {{ __('Mettre à jour mon profil') }}
                                </a>
                            </div>
                        @elseif($currentStep === 2)
                            <div id="step2" class="p-3 rounded-2xl bg-[#fbfaf7] border border-[#efe6d4] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ __('Étape 2 · Réservations en ligne') }}
                                    </div>
                                    <span class="text-xs font-semibold text-[#647a0b]">
                                        {{ $step2Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Ajoutez un lieu de pratique, configurez vos disponibilités et au moins une prestation réservable en ligne.') }}
                                </p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <a href="{{ route('mobile.practice-locations.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Mes lieux') }}
                                    </a>
                                    <a href="{{ route('mobile.availabilities.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Mes disponibilités') }}
                                    </a>
                                    <a href="{{ route('mobile.products.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Mes prestations') }}
                                    </a>
                                </div>
                            </div>
                        @elseif($currentStep === 3)
                            <div id="step3" class="p-3 rounded-2xl bg-[#fef9f7] border border-[#f5d5c6] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ __('Étape 3 · Découvrir les fonctionnalités') }}
                                    </div>
                                    <span class="text-xs font-semibold text-[#647a0b]">
                                        {{ $step3Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Explorez la facturation, les questionnaires et les téléconsultations pour aller plus loin avec Olithea.') }}
                                </p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <a href="{{ route('mobile.invoices.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Facturation') }}
                                    </a>
                                    @if(class_exists(\App\Models\Questionnaire::class) || class_exists(\App\Models\QuestionnaireTemplate::class))
                                        <a href="{{ route('questionnaires.index') }}"
                                           class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                            {{ __('Questionnaires') }}
                                        </a>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('dashboard-pro.skipStep3') }}" class="mt-2">
                                    @csrf
                                    <button type="submit"
                                            class="text-[11px] text-gray-500 underline">
                                        {{ __('Ignorer cette étape (optionnel)') }}
                                    </button>
                                </form>
                            </div>
                        @elseif($currentStep === 4)
                            <div id="step4" class="p-3 rounded-2xl bg-[#f4f7ff] border border-[#dae3ff] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ __('Étape 4 · Parrainer un thérapeute') }}
                                    </div>
                                    <span class="text-xs font-semibold text-[#647a0b]">
                                        {{ $step4Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Invitez un(e) collègue thérapeute à découvrir Olithea et profitez de mois offerts.') }}
                                </p>

                                @if(($step4Checks['referral'] ?? false) !== true)
                                    <form method="POST" action="{{ route('dashboard-pro.markReferralOnboardingDone') }}" class="mt-1">
                                        @csrf
                                        <button type="submit"
                                                class="text-[11px] px-3 py-1.5 rounded-full bg-[#647a0b] text-white">
                                            {{ __('J’ai parrainé un thérapeute') }}
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('dashboard-pro.skipStep4') }}" class="mt-2">
                                    @csrf
                                    <button type="submit"
                                            class="text-[11px] text-gray-500 underline">
                                        {{ __('Ignorer cette étape (bonus optionnel)') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- Mini résumé des 4 étapes --}}
                    <div class="mt-3 grid grid-cols-1 gap-2 text-[11px]">
                        <div class="flex items-center justify-between px-2 py-1 rounded-xl bg-[#fafcf5]">
                            <span>{{ __('1. Profil & infos') }}</span>
                            <span class="font-semibold">
                                {{ $step1Completion == 100 ? '✔️' : $step1Completion.'%' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-2 py-1 rounded-xl bg-[#fbfaf7]">
                            <span>{{ __('2. Réservations en ligne') }}</span>
                            <span class="font-semibold">
                                {{ $step2Completion == 100 ? '✔️' : $step2Completion.'%' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-2 py-1 rounded-xl bg-[#fef9f7]">
                            <span>{{ __('3. Fonctionnalités') }}</span>
                            <span class="font-semibold">
                                @if($skipStep3)
                                    {{ __('Ignorée') }}
                                @else
                                    {{ $step3Completion == 100 ? '✔️' : $step3Completion.'%' }}
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-2 py-1 rounded-xl bg-[#f4f7ff]">
                            <span>{{ __('4. Parrainage') }}</span>
                            <span class="font-semibold">
                                @if($skipStep4)
                                    {{ __('Ignorée') }}
                                @else
                                    {{ $step4Completion == 100 ? '✔️' : $step4Completion.'%' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </x-ts-card>
            @endif

            {{-- ===================================================== --}}
            {{--                       KPI CARDS (MOBILE)             --}}
            {{-- ===================================================== --}}
            <div class="grid grid-cols-1 gap-4">
                {{-- Clients --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#dfe5c7] bg-[#8ea633] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('client_profiles.index') }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#8ea633] flex items-center justify-center font-bold">
                                ✔
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-2xl font-bold leading-tight">
                                    {{ $totalClients }}
                                </div>
                                <div class="text-sm opacity-90">
                                    {{ __('Clients') }}
                                </div>
                            </div>
                        </div>
                    </button>
                </x-ts-card>

                {{-- RDV à venir --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#d9dfc0] bg-[#647a0b] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('mobile.appointments.index', ['filter' => 'upcoming']) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#647a0b] flex items-center justify-center text-xl">
                                📅
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-2xl font-bold leading-tight">
                                    {{ $upcomingAppointments }}
                                </div>
                                <div class="text-sm opacity-90">
                                    {{ __('Rendez-vous à venir') }}
                                </div>
                            </div>
                        </div>
                    </button>
                </x-ts-card>

                {{-- Vues portail --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#e5d0c7] bg-[#a96b56] text-white px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white text-[#a96b56] flex items-center justify-center text-xl">
                            👁
                        </div>
                        <div class="space-y-0.5">
                            <div class="text-2xl font-bold leading-tight">
                                {{ $therapist->view_count }}
                            </div>
                            <div class="text-sm opacity-90">
                                {{ __('Vues du Portail') }}
                            </div>
                        </div>
                    </div>
                </x-ts-card>

                {{-- Factures émises --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#e5d0c7] bg-[#a96b56] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('mobile.invoices.index') }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#a96b56] flex items-center justify-center text-xl">
                                🧾
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-2xl font-bold leading-tight">
                                    {{ $totalInvoices }}
                                </div>
                                <div class="text-sm opacity-90">
                                    {{ __('Factures') }}
                                </div>
                            </div>
                        </div>
                    </button>
                </x-ts-card>

                {{-- Factures en attente --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#e0c7be] bg-[#854f38] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('mobile.invoices.index', ['filter' => 'pending']) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#854f38] flex items-center justify-center text-xl">
                                ⏳
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-2xl font-bold leading-tight">
                                    {{ $pendingInvoices }}
                                </div>
                                <div class="text-sm opacity-90">
                                    {{ __('Factures en attente') }}
                                </div>
                            </div>
                        </div>
                    </button>
                </x-ts-card>

                <div class="pt-2">
                    <h2 class="text-base font-semibold text-gray-900">Pilotage financier</h2>
                    <p class="text-xs text-gray-500">Montants TTC, selon la date réelle du règlement.</p>
                </div>

                <x-ts-card class="rounded-lg shadow-sm border border-[#dfe5c7] bg-white px-4 py-4">
                    <button class="w-full text-left" onclick="window.location='{{ route('mobile.receipts.index') }}'">
                        <div class="text-[11px] font-semibold uppercase text-[#647a0b]" title="Encaissements TTC moins corrections et remboursements du mois.">Encaissements nets ce mois</div>
                        <div class="mt-1 text-2xl font-bold {{ $financialMetrics['net_received_this_month'] < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ number_format($financialMetrics['net_received_this_month'], 2, ',', ' ') }} €</div>
                    </button>
                </x-ts-card>

                <x-ts-card class="rounded-lg shadow-sm border border-[#e5d0c7] bg-white px-4 py-4">
                    <button class="w-full text-left" onclick="window.location='{{ route('mobile.invoices.index', ['filter' => 'current_month']) }}'">
                        <div class="text-[11px] font-semibold uppercase text-[#854f38]" title="Total TTC des factures émises ce mois, hors devis.">Facturé ce mois</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($financialMetrics['billed_this_month'], 2, ',', ' ') }} €</div>
                    </button>
                </x-ts-card>

                <x-ts-card class="rounded-lg shadow-sm border border-[#ead9c7] bg-white px-4 py-4">
                    <button class="w-full text-left" onclick="window.location='{{ route('mobile.invoices.index', ['filter' => 'pending']) }}'">
                        <div class="text-[11px] font-semibold uppercase text-[#9a6a31]" title="Solde TTC restant sur toutes les factures.">À encaisser</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($financialMetrics['outstanding'], 2, ',', ' ') }} €</div>
                    </button>
                </x-ts-card>

                <x-ts-card class="rounded-lg shadow-sm border border-[#ead0d0] bg-white px-4 py-4">
                    <button class="w-full text-left" onclick="window.location='{{ route('mobile.receipts.index') }}'">
                        <div class="text-[11px] font-semibold uppercase text-[#9b3a3a]" title="Contre-passations et remboursements TTC du mois.">Corrections et remboursements</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($financialMetrics['corrections_and_refunds'], 2, ',', ' ') }} €</div>
                    </button>
                </x-ts-card>

                @if($financialMetrics['legacy_paid_without_receipt_count'] > 0)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                        {{ $financialMetrics['legacy_paid_without_receipt_count'] }} facture(s) ancienne(s) payée(s) sans écriture d'encaissement restent intactes et sont exclues des encaissements.
                    </div>
                @endif
            </div>

            {{-- ===================================================== --}}
            {{--                GRAPHIQUES (compact mobile)           --}}
            {{-- ===================================================== --}}
            <div class="grid grid-cols-1 gap-4">
                <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4">
                    <h3 class="text-sm font-semibold text-[#647a0b] mb-2">
                        {{ __('Rendez-vous par mois') }}
                    </h3>
                    <div class="h-48">
                        <canvas id="appointmentsChart" class="w-full h-full"></canvas>
                    </div>
                </x-ts-card>

                <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4">
                    <h3 class="text-sm font-semibold text-[#854f38] mb-2">
                        {{ __('Facturation et encaissements') }}
                    </h3>
                    <div class="h-48">
                        <canvas id="revenueChart" class="w-full h-full"></canvas>
                    </div>
                </x-ts-card>
            </div>

            {{-- ===================================================== --}}
            {{--           PROCHAINS RDV + DERNIÈRES FACTURES        --}}
            {{-- ===================================================== --}}
            <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4 space-y-3">
                <h3 class="text-base font-semibold text-[#647a0b]">
                    {{ __('Prochains rendez-vous') }}
                </h3>

                @if($recentAppointments->isEmpty())
                    <p class="text-sm text-gray-500">
                        {{ __('Aucun rendez-vous à venir pour le moment.') }}
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach($recentAppointments as $appointment)
                            <button
                                class="w-full text-left px-3 py-2 rounded-2xl border bg-[#f8fbf2] hover:bg-[#f0f8e8] transition flex flex-col gap-1"
                                onclick="window.location='{{ route('mobile.appointments.show', $appointment) }}'"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        {{ $appointment->duration }} min
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-xs text-gray-600">
                                    <span>
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->locale('fr_FR')->isoFormat('DD/MM/YYYY HH:mm') }}
                                    </span>
                                    <span class="capitalize">
                                        {{ $appointment->status }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-ts-card>

            <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4 space-y-3">
                <h3 class="text-base font-semibold text-[#854f38]">
                    {{ __('Dernières factures') }}
                </h3>

                @if($recentInvoices->isEmpty())
                    <p class="text-sm text-gray-500">
                        {{ __('Aucune facture récente.') }}
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach($recentInvoices as $invoice)
                            <button
                                class="w-full text-left px-3 py-2 rounded-2xl border bg-[#fef8f5] hover:bg-[#fdece6] transition flex flex-col gap-1"
                                onclick="window.location='{{ route('mobile.invoices.show', $invoice) }}'"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                                    </div>
                                    <div class="text-sm font-semibold text-[#854f38]">
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-xs text-gray-600">
                                    <span class="capitalize">
                                        {{ $invoice->status }}
                                    </span>
                                    <span>
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->isoFormat('DD/MM/YYYY') }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-ts-card>

            {{-- ===================================================== --}}
            {{--                       QR CODE PRO                    --}}
            {{-- ===================================================== --}}
            @if($therapist->slug)
                <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4 space-y-3">
                    <h3 class="text-base font-semibold text-[#647a0b]">
                        {{ __('QR Code pour votre Portail') }}
                    </h3>
                    <p class="text-xs text-gray-600 leading-relaxed break-words">
                        {{ __('Scannez ou téléchargez ce QR Code pour l’utiliser sur vos cartes de visite ou supports imprimés.') }}
                    </p>

                    <div class="flex items-center gap-3 mt-1">
                        <x-ts-button
                            id="generate-qrcode"
                            size="sm"
                            rounded
                            class="!text-sm !px-4 !py-2 !bg-[#647a0b] !text-white hover:!bg-[#8ea633]"
                        >
                            {{ __('Générer le QR Code') }}
                        </x-ts-button>

                        <x-ts-button
                            id="download-qrcode"
                            tag="a"
                            href="#"
                            size="sm"
                            rounded
                            class="hidden !text-sm !px-4 !py-2 !bg-[#a96b56] !text-white hover:!bg-[#854f38]"
                            download="qrcode.png"
                        >
                            {{ __('Télécharger') }}
                        </x-ts-button>
                    </div>

                    <div id="qrcode-container" class="mt-4 flex justify-center"></div>
                </x-ts-card>
            @endif

        </div>
    </div>

    {{-- ===================== --}}
    {{-- Scripts Graphiques   --}}
    {{-- ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const appointmentsData = @json(array_values($appointmentsPerMonth)).map(Number);
            const receivedData     = @json(array_values($monthlyRevenueData)).map(Number);
            const billedData       = @json(array_values($monthlyBilledData)).map(Number);
            const monthLabels      = @json(array_values($months));

            // Rendez-vous par mois
            const ctxAppointments = document.getElementById('appointmentsChart')?.getContext('2d');
            if (ctxAppointments) {
                new Chart(ctxAppointments, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: '{{ __("Nombre de Rendez-vous") }}',
                            data: appointmentsData,
                            backgroundColor: 'rgba(100, 122, 11, 0.6)',
                            borderColor: 'rgba(100, 122, 11, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.7)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                callbacks: {
                                    label: function (context) {
                                        return context.parsed.y + ' {{ __("Rendez-vous") }}';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Facturation et encaissements mensuels
            const ctxRevenue = document.getElementById('revenueChart')?.getContext('2d');
            if (ctxRevenue) {
                new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: '{{ __("Encaissements nets TTC") }}',
                            data: receivedData,
                            backgroundColor: 'rgba(100, 122, 11, 0.12)',
                            borderColor: 'rgba(100, 122, 11, 1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(100, 122, 11, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(100, 122, 11, 1)'
                        }, {
                            label: '{{ __("Facturé TTC") }}',
                            data: billedData,
                            backgroundColor: 'rgba(133, 79, 56, 0.10)',
                            borderColor: 'rgba(133, 79, 56, 1)',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            fill: false,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(133, 79, 56, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function (value) {
                                        return value + '€';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'bottom' },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.7)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ' : ' + context.parsed.y.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>

    {{-- ===================== --}}
    {{-- Script QR Code        --}}
    {{-- ===================== --}}
    <script>
        document.getElementById('generate-qrcode')?.addEventListener('click', function () {
            fetch('{{ route("dashboard-pro.qrcode") }}')
                .then(r => r.json())
                .then(data => {
                    if (!data.qrCode) return;

                    const img = `<img src="${data.qrCode}" class="w-40 h-40">`;
                    document.getElementById('qrcode-container').innerHTML = img;

                    const link = document.getElementById('download-qrcode');
                    link.href = data.qrCode;
                    link.classList.remove('hidden');
                })
                .catch(() => {});
        });
    </script>

    <style>
        canvas {
            max-height: 260px;
        }
    </style>
</x-mobile-layout>
