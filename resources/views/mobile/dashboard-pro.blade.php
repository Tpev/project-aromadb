{{-- resources/views/mobile/dashboard-pro.blade.php --}}
@php
    $pageTitle       = 'Tableau de bord PRO';
    $pageDescription = "Suivez vos rendez-vous, factures et progression d'onboarding sur Olithea PRO.";
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
                    <h1 class="text-2xl font-extrabold text-[#6B4A3A] leading-snug break-words">
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
                        <h2 class="text-base font-semibold text-[#6B4A3A] leading-snug break-words">
                            👋 {{ __('Bienvenue sur Olithea PRO') }}
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
                                <div class="h-2 bg-[#6B4A3A] rounded-full transition-all duration-300"
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
                                    <span class="text-xs font-semibold text-[#6B4A3A]">
                                        {{ $step1Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Complétez vos informations professionnelles (nom, adresse, description, services…) pour présenter un profil clair à vos clients.') }}
                                </p>
                                <a href="{{ route('mobile.entry') }}"
                                   class="inline-flex items-center justify-center mt-1 text-xs font-semibold px-3 py-1.5 rounded-full bg-[#6B4A3A] text-white">
                                    {{ __('Mettre à jour mon profil') }}
                                </a>
                            </div>
                        @elseif($currentStep === 2)
                            <div id="step2" class="p-3 rounded-2xl bg-[#fbfaf7] border border-[#efe6d4] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ __('Étape 2 · Réservations en ligne') }}
                                    </div>
                                    <span class="text-xs font-semibold text-[#6B4A3A]">
                                        {{ $step2Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Ajoutez un lieu de pratique, configurez vos disponibilités et au moins une prestation réservable en ligne.') }}
                                </p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <a href="{{ route('practice_locations.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Mes lieux') }}
                                    </a>
                                    <a href="{{ route('availabilities.index') }}"
                                       class="text-[11px] px-3 py-1.5 rounded-full bg-white border text-gray-800">
                                        {{ __('Mes disponibilités') }}
                                    </a>
                                    <a href="{{ route('products.index') }}"
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
                                    <span class="text-xs font-semibold text-[#6B4A3A]">
                                        {{ $step3Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Explorez la facturation, les questionnaires et les téléconsultations pour aller plus loin avec Olithea PRO.') }}
                                </p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <a href="{{ route('invoices.index') }}"
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
                                    <span class="text-xs font-semibold text-[#6B4A3A]">
                                        {{ $step4Completion }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed break-words">
                                    {{ __('Invitez un(e) collègue thérapeute à découvrir Olithea PRO et profitez de mois offerts.') }}
                                </p>

                                @if(($step4Checks['referral'] ?? false) !== true)
                                    <form method="POST" action="{{ route('dashboard-pro.markReferralOnboardingDone') }}" class="mt-1">
                                        @csrf
                                        <button type="submit"
                                                class="text-[11px] px-3 py-1.5 rounded-full bg-[#6B4A3A] text-white">
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
                <x-ts-card class="rounded-3xl shadow-md border border-[#dfe5c7] bg-[#4E5F3A] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('client_profiles.index') }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#4E5F3A] flex items-center justify-center font-bold">
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
                <x-ts-card class="rounded-3xl shadow-md border border-[#d9dfc0] bg-[#6B4A3A] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('appointments.index', ['filter' => 'upcoming']) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#6B4A3A] flex items-center justify-center text-xl">
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
                            onclick="window.location='{{ route('invoices.index') }}'">
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
                <x-ts-card class="rounded-3xl shadow-md border border-[#e0c7be] bg-[#5F7048] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('invoices.index', ['filter' => 'pending']) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#5F7048] flex items-center justify-center text-xl">
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

                {{-- Revenus du mois --}}
                <x-ts-card class="rounded-3xl shadow-md border border-[#d6c2ba] bg-[#4E5F3A] text-white px-4 py-4">
                    <button class="w-full text-left"
                            onclick="window.location='{{ route('invoices.index', ['filter' => 'current_month']) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white text-[#4E5F3A] flex items-center justify-center text-xl">
                                💶
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-2xl font-bold leading-tight">
                                    {{ number_format($monthlyRevenue, 2, ',', ' ') }} €
                                </div>
                                <div class="text-sm opacity-90">
                                    {{ __('Revenus ce mois') }}
                                </div>
                            </div>
                        </div>
                    </button>
                </x-ts-card>
            </div>

            {{-- ===================================================== --}}
            {{--                GRAPHIQUES (compact mobile)           --}}
            {{-- ===================================================== --}}
            <div class="grid grid-cols-1 gap-4">
                <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4">
                    <h3 class="text-sm font-semibold text-[#6B4A3A] mb-2">
                        {{ __('Rendez-vous par mois') }}
                    </h3>
                    <div class="h-48">
                        <canvas id="appointmentsChart" class="w-full h-full"></canvas>
                    </div>
                </x-ts-card>

                <x-ts-card class="rounded-3xl bg-white shadow border px-4 py-4">
                    <h3 class="text-sm font-semibold text-[#5F7048] mb-2">
                        {{ __('Revenus mensuels') }}
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
                <h3 class="text-base font-semibold text-[#6B4A3A]">
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
                                onclick="window.location='{{ route('appointments.show', $appointment->id) }}'"
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
                <h3 class="text-base font-semibold text-[#5F7048]">
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
                                onclick="window.location='{{ route('invoices.show', $invoice->id) }}'"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-800 break-words">
                                        {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                                    </div>
                                    <div class="text-sm font-semibold text-[#5F7048]">
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
                    <h3 class="text-base font-semibold text-[#6B4A3A]">
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
                            class="!text-sm !px-4 !py-2 !bg-[#6B4A3A] !text-white hover:!bg-[#4E5F3A]"
                        >
                            {{ __('Générer le QR Code') }}
                        </x-ts-button>

                        <x-ts-button
                            id="download-qrcode"
                            tag="a"
                            href="#"
                            size="sm"
                            rounded
                            class="hidden !text-sm !px-4 !py-2 !bg-[#a96b56] !text-white hover:!bg-[#5F7048]"
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
            const revenueData      = @json(array_values($monthlyRevenueData)).map(Number);
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
                            backgroundColor: 'rgba(167, 184, 138, 0.6)',
                            borderColor: 'rgba(167, 184, 138, 1)',
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

            // Revenus mensuels
            const ctxRevenue = document.getElementById('revenueChart')?.getContext('2d');
            if (ctxRevenue) {
                new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: '{{ __("Revenus (€)") }}',
                            data: revenueData,
                            backgroundColor: 'rgba(107, 74, 58, 0.2)',
                            borderColor: 'rgba(107, 74, 58, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(107, 74, 58, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(107, 74, 58, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value + '€';
                                    }
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
                                        return context.parsed.y + ' €';
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
