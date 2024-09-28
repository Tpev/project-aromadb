{{-- resources/views/dashboard-pro.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tableau de Bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- KPIs --}}
            <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Clients -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m5-8l3 3 7-7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-700">{{ $totalClients }}</div>
                            <div class="text-sm text-gray-500">{{ __('Clients') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous à Venir -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m5 4H3a2 2 0 01-2-2V7a2 2 0 012-2h3m16 14v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m16 0H3" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-700">{{ $upcomingAppointments }}</div>
                            <div class="text-sm text-gray-500">{{ __('Rendez-vous à Venir') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Factures Émises -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h3m4 0a2 2 0 012 2v6m-6 0v-6m0 0V9m0 2h.01" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-700">{{ $totalInvoices }}</div>
                            <div class="text-sm text-gray-500">{{ __('Factures Émises') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Factures en Attente -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-700">{{ $pendingInvoices }}</div>
                            <div class="text-sm text-gray-500">{{ __('Factures en Attente') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Revenus Ce Mois -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v4a3 3 0 006 0v-4c0-1.657-1.343-3-3-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h.01M18 12h.01M9 12h6" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-700">{{ number_format($monthlyRevenue, 2, ',', ' ') }} €</div>
                            <div class="text-sm text-gray-500">{{ __('Revenus Ce Mois') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Graphiques --}}
            <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Rendez-vous par Mois -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Rendez-vous par Mois') }}</h3>
                    <canvas id="appointmentsChart"></canvas>
                </div>

                <!-- Revenus Mensuels -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Revenus Mensuels') }}</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Prochains Rendez-vous --}}
            <div class="mb-8 bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Prochains Rendez-vous') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date et Heure') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Durée (min)') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Statut') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentAppointments as $appointment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $appointment->duration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($appointment->status === 'confirmed') bg-green-100 text-green-800 
                                            @elseif($appointment->status === 'pending') bg-yellow-100 text-yellow-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucun rendez-vous récent.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="{{ route('appointments.index') }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Voir tous les rendez-vous') }}</a>
                </div>
            </div>

            {{-- Dernières Factures --}}
            <div class="mb-8 bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Dernières Factures') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Montant') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Statut') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentInvoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800 
                                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune facture récente.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Voir toutes les factures') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts pour les Graphiques --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Rendez-vous par Mois
                var ctxAppointments = document.getElementById('appointmentsChart').getContext('2d');
                var appointmentsChart = new Chart(ctxAppointments, {
                    type: 'bar',
                    data: {
                        labels: @json(array_map(function($month) {
                            return \Carbon\Carbon::create()->month($month)->translatedFormat('F');
                        }, array_keys($appointmentsPerMonth))),
                        datasets: [{
                            label: '{{ __("Nombre de Rendez-vous") }}',
                            data: @json(array_values($appointmentsPerMonth)),
                            backgroundColor: 'rgba(100, 122, 11, 0.6)', // Couleur de votre marque
                            borderColor: 'rgba(100, 122, 11, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                precision:0
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                // Revenus Mensuels
                var ctxRevenue = document.getElementById('revenueChart').getContext('2d');
                var revenueChart = new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: @json(array_map(function($month) {
                            return \Carbon\Carbon::create()->month($month)->translatedFormat('F');
                        }, array_keys($monthlyRevenueData))),
                        datasets: [{
                            label: '{{ __("Revenus (€)") }}',
                            data: @json(array_values($monthlyRevenueData)),
                            backgroundColor: 'rgba(133, 79, 56, 0.2)', // Couleur de votre marque
                            borderColor: 'rgba(133, 79, 56, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + '€';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + '€';
                                    }
                                }
                            },
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            });
        </script>
    @endpush

    {{-- Styles personnalisés (si nécessaire) --}}
    @push('styles')
        <style>
            /* Styles personnalisés adaptés à votre marque */
            .bg-brand-green {
                background-color: #647a0b; /* Couleur principale de votre marque */
            }

            .text-brand-green {
                color: #647a0b; /* Couleur principale de votre marque */
            }

            .hover\:text-brand-orange:hover {
                color: #854f38; /* Couleur d'accent de votre marque */
            }

            /* Autres styles personnalisés si nécessaire */
        </style>
    @endpush
</x-app-layout>
