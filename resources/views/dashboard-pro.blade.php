{{-- resources/views/dashboard-pro.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#6B4A3A] leading-tight">
            {{ __('Tableau de Bord') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

{{-- ===================================================== --}}
{{--                 ONBOARDING SEQUENTIEL                --}}
{{-- ===================================================== --}}

@if(!$onboardingCompleted)
    <div class="bg-white shadow rounded-2xl p-6 border border-[#edf1df] mb-6">

        {{-- Bandeau titre + progression globale --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-[#6B4A3A]">
                    👋 Bienvenue sur Olithea PRO
                </h3>
                <p class="text-sm text-gray-600">
                    Suivez ces étapes pour être prêt à recevoir des réservations en ligne et profiter de toutes les fonctionnalités.
                </p>
            </div>

            <div class="min-w-[220px]">
                <div class="flex justify-between text-xs font-medium text-gray-600 mb-1">
                    <span>Progression globale</span>
                    <span>{{ $globalCompletion }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-[#6B4A3A] rounded-full transition-all duration-300"
                         style="width: {{ $globalCompletion }}%;"></div>
                </div>
            </div>
        </div>

        {{-- Détermination de l'étape active --}}
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

        {{-- ÉTAPE ACTIVE (BIG CARD) --}}
        @if($currentStep == 1)
            @include('partials.onboarding.step1-big')
        @elseif($currentStep == 2)
            @include('partials.onboarding.step2-big')
        @elseif($currentStep == 3)
            @include('partials.onboarding.step3-big')
        @elseif($currentStep == 4)
            @include('partials.onboarding.step4-big')
        @endif

        {{-- MINI-CARDS --}}
        <div class="mt-6 space-y-3">

            {{-- Étape 1 --}}
            @if($currentStep != 1)
                <div class="flex items-center justify-between p-3 rounded-lg border bg-[#fafcf5]">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">1. Profil & informations</div>
                        <div class="text-xs text-gray-500">
                            @if($step1Completion == 100)
                                ✔️ Terminé
                            @else
                                {{ $step1Completion }}% — En attente
                            @endif
                        </div>
                    </div>
                    <a href="#step1" class="text-xs text-[#6B4A3A] hover:underline font-medium">
                        Voir
                    </a>
                </div>
            @endif

            {{-- Étape 2 --}}
            @if($currentStep != 2)
                <div class="flex items-center justify-between p-3 rounded-lg border bg-[#fbfaf7]">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">
                            2. Réservations en ligne
                        </div>
                        <div class="text-xs text-gray-500">
                            @if($step2Completion == 100)
                                ✔️ Terminé
                            @else
                                {{ $step2Completion }}% — En cours
                            @endif
                        </div>
                    </div>
                    <a href="#step2" class="text-xs text-[#6B4A3A] hover:underline font-medium">
                        Voir
                    </a>
                </div>
            @endif

            {{-- Étape 3 (optionnelle) --}}
            @if($currentStep != 3)
                <div class="flex items-center justify-between p-3 rounded-lg border bg-[#fef9f7]">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">
                            3. Découvrir les fonctionnalités
                        </div>
                        <div class="text-xs text-gray-500">
                            @if($skipStep3)
                                Ignorée (optionnel)
                            @elseif($step3Completion == 100)
                                ✔️ Terminé
                            @else
                                {{ $step3Completion }}% — Optionnel
                            @endif
                        </div>
                    </div>
                    @unless($skipStep3)
                        <a href="#step3" class="text-xs text-[#6B4A3A] hover:underline font-medium">
                            Voir
                        </a>
                    @endunless
                </div>
            @endif

            {{-- Étape 4 (parrainage, optionnelle) --}}
            @if($currentStep != 4)
                <div class="flex items-center justify-between p-3 rounded-lg border bg-[#f4f7ff]">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">
                            4. Parrainer un thérapeute
                        </div>
                        <div class="text-xs text-gray-500">
                            @if($skipStep4)
                                Ignorée (bonus optionnel)
                            @elseif(($step4Checks['referral'] ?? false) === true)
                                ✔️ Parrainage validé
                            @else
                                {{ $step4Completion }}% — Bonus : 1 mois offert
                            @endif
                        </div>
                    </div>
                    @unless($skipStep4)
                        <a href="#step4" class="text-xs text-[#6B4A3A] hover:underline font-medium">
                            Voir
                        </a>
                    @endunless
                </div>
            @endif

        </div>
    </div>
@endif


            {{-- ===================================================== --}}
            {{--                       KPI CARDS                     --}}
            {{-- ===================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="col-span-1 lg:col-span-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                        {{-- Total Clients --}}
                        <a href="{{ route('client_profiles.index') }}"
                           class="bg-[#4E5F3A] shadow rounded-lg p-5 text-white hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#4E5F3A] mr-4">
                                    ✔
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $totalClients }}</div>
                                    <div class="text-sm">Clients</div>
                                </div>
                            </div>
                        </a>

                        {{-- RDV à venir --}}
                        <a href="{{ route('appointments.index', ['filter' => 'upcoming']) }}"
                           class="bg-[#6B4A3A] shadow rounded-lg p-5 text-white hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#6B4A3A] mr-4">
                                    📅
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $upcomingAppointments }}</div>
                                    <div class="text-sm">Rendez-vous à venir</div>
                                </div>
                            </div>
                        </a>

                        {{-- Portail Pro views --}}
                        <div class="bg-[#a96b56] shadow rounded-lg p-5 text-white hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#a96b56] mr-4">👁</div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $therapist->view_count }}</div>
                                    <div class="text-sm">Vues du Portail</div>
                                </div>
                            </div>
                        </div>

                        {{-- Factures émises --}}
                        <a href="{{ route('invoices.index') }}"
                           class="bg-[#a96b56] shadow rounded-lg p-5 text-white hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#a96b56] mr-4">🧾</div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $totalInvoices }}</div>
                                    <div class="text-sm">Factures</div>
                                </div>
                            </div>
                        </a>

                        {{-- Factures en attente --}}
                        <a href="{{ route('invoices.index', ['filter' => 'pending']) }}"
                           class="bg-[#5F7048] shadow rounded-lg p-5 text-white hover:shadow-lg transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#5F7048] mr-4">⏳</div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $pendingInvoices }}</div>
                                    <div class="text-sm">En attente</div>
                                </div>
                            </div>
                        </a>

                        {{-- Revenus du mois --}}
                        <a href="{{ route('invoices.index', ['filter' => 'current_month']) }}"
                           class="bg-[#4E5F3A] shadow rounded-lg p-5 text-white hover:shadow-lg transition col-span-1 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#4E5F3A] mr-4">💶</div>
                                <div>
                                    <div class="text-2xl font-bold">
                                        {{ number_format($monthlyRevenue, 2, ',', ' ') }} €
                                    </div>
                                    <div class="text-sm">Revenus ce mois</div>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>


            {{-- ===================================================== --}}
            {{--                GRAPHIQUES + RDV + FACTURES            --}}
            {{-- ===================================================== --}}

            {{-- Graphiques --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- RDV par mois --}}
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-semibold text-[#6B4A3A] mb-4">Rendez-vous par Mois</h3>
                    <div class="h-60">
                        <canvas id="appointmentsChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                {{-- Revenus mensuels --}}
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-semibold text-[#5F7048] mb-4">Revenus Mensuels</h3>
                    <div class="h-60">
                        <canvas id="revenueChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>


            {{-- Prochains Rendez-vous --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-[#6B4A3A] mb-3">Prochains Rendez-vous</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Client</th>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Durée</th>
                                <th class="px-4 py-2 text-left">Statut</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($recentAppointments as $appointment)
                                <tr class="hover:bg-[#f0f8e8] cursor-pointer"
                                    onclick="window.location='{{ route('appointments.show', $appointment->id) }}'">
                                    <td class="px-4 py-2">
                                        {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->locale('fr_FR')->isoFormat('DD/MM/YYYY HH:mm') }}
                                    </td>
                                    <td class="px-4 py-2">{{ $appointment->duration }} min</td>
                                    <td class="px-4 py-2">{{ ucfirst($appointment->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                                        Aucun rendez-vous récent.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- Dernières Factures --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-[#5F7048] mb-3">Dernières Factures</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Client</th>
                                <th class="px-4 py-2 text-left">Montant</th>
                                <th class="px-4 py-2 text-left">Statut</th>
                                <th class="px-4 py-2 text-left">Date</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($recentInvoices as $invoice)
                                <tr class="hover:bg-[#fdece6] cursor-pointer"
                                    onclick="window.location='{{ route('invoices.show', $invoice->id) }}'">
                                    <td class="px-4 py-2">
                                        @php
                                            $cp = $invoice->clientProfile;
                                            $corp = null;

                                            if (!empty($invoice->corporate_client_id)) {
                                                $corp = $invoice->corporateClient ?? \App\Models\CorporateClient::find($invoice->corporate_client_id);
                                            }

                                            $clientLabel = $corp
                                                ? ($corp->trade_name ?: $corp->name)
                                                : trim(($cp?->first_name ?? '') . ' ' . ($cp?->last_name ?? ''));

                                            if (!$clientLabel) {
                                                $clientLabel = '—';
                                            }
                                        @endphp
                                        {{ $clientLabel }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-4 py-2">{{ ucfirst($invoice->status) }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->isoFormat('DD/MM/YYYY') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                                        Aucune facture récente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- QR Code --}}
            @if($therapist->slug)
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-[#6B4A3A] mb-3">QR Code pour votre Portail</h3>

                    <p class="text-sm text-gray-500 mb-4">
                        Utilisez-le sur vos cartes de visite ou supports imprimés.
                    </p>

                    <button id="generate-qrcode" class="bg-[#6B4A3A] text-white px-4 py-2 rounded hover:bg-[#4E5F3A]">
                        Générer le QR Code
                    </button>

                    <a id="download-qrcode" href="#" download="qrcode.png"
                       class="bg-[#a96b56] text-white px-4 py-2 rounded hover:bg-[#5F7048] ml-3 hidden">
                        Télécharger
                    </a>

                    <div id="qrcode-container" class="mt-6 flex justify-center"></div>
                </div>
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
            var ctxAppointments = document.getElementById('appointmentsChart').getContext('2d');
            var appointmentsChart = new Chart(ctxAppointments, {
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

            // Revenus mensuels
            var ctxRevenue = document.getElementById('revenueChart').getContext('2d');
            var revenueChart = new Chart(ctxRevenue, {
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
                    const img = `<img src="${data.qrCode}" class="w-48 h-48">`;
                    document.getElementById('qrcode-container').innerHTML = img;

                    const link = document.getElementById('download-qrcode');
                    link.href = data.qrCode;
                    link.classList.remove('hidden');
                });
        });
    </script>

    {{-- ===================== --}}
    {{-- Styles complémentaires --}}
    {{-- ===================== --}}
    <style>
        /* limite supplémentaire de sécu, comme dans ton ancienne version */
        canvas {
            max-height: 300px;
        }
    </style>

</x-app-layout>
