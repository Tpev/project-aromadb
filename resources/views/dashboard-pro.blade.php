{{-- resources/views/dashboard-pro.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Tableau de Bord') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- KPIs Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="col-span-1 lg:col-span-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                        <!-- Total Clients -->
                        <a href="{{ route('client_profiles.index') }}" class="bg-[#8ea633] shadow rounded-lg p-5 hover:shadow-xl transition-shadow duration-300 cursor-pointer text-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#8ea633] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M17 20h5V4H2v16h5m5-8l3 3 7-7" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $totalClients }}</div>
                                    <div class="text-sm">{{ __('Clients') }}</div>
                                </div>
                            </div>
                        </a>

                        <!-- Rendez-vous à Venir -->
                        <a href="{{ route('appointments.index', ['filter' => 'upcoming']) }}" class="bg-[#647a0b] shadow rounded-lg p-5 hover:shadow-xl transition-shadow duration-300 cursor-pointer text-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#647a0b] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M8 7V3m8 4V3m-9 8h10m5 4H3a2 2 0 01-2-2V7a2 2 0 012-2h3m16 14v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m16 0H3" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $upcomingAppointments }}</div>
                                    <div class="text-sm">{{ __('Rendez-vous à Venir') }}</div>
                                </div>
                            </div>
                        </a>

                        <!-- Factures Émises -->
                        <a href="{{ route('invoices.index') }}" class="bg-[#a96b56] shadow rounded-lg p-5 hover:shadow-xl transition-shadow duration-300 cursor-pointer text-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#a96b56] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M9 17v-6a2 2 0 012-2h3m4 0a2 2 0 012 2v6m-6 0v-6m0 0V9m0 2h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $totalInvoices }}</div>
                                    <div class="text-sm">{{ __('Factures Émises') }}</div>
                                </div>
                            </div>
                        </a>

                        <!-- Factures en Attente -->
                        <a href="{{ route('invoices.index', ['filter' => 'pending']) }}" class="bg-[#854f38] shadow rounded-lg p-5 hover:shadow-xl transition-shadow duration-300 cursor-pointer text-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#854f38] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $pendingInvoices }}</div>
                                    <div class="text-sm">{{ __('Factures en Attente') }}</div>
                                </div>
                            </div>
                        </a>

                        <!-- Revenus Ce Mois -->
                        <a href="{{ route('invoices.index', ['filter' => 'current_month']) }}" class="bg-[#6a3f2c] shadow rounded-lg p-5 hover:shadow-xl transition-shadow duration-300 cursor-pointer text-white col-span-1 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white text-[#6a3f2c] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 8c-1.657 0-3 1.343-3 3v4a3 3 0 006 0v-4c0-1.657-1.343-3-3-3z" />
                                        <path d="M6 12h.01M18 12h.01M9 12h6" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ number_format($monthlyRevenue, 2, ',', ' ') }} €</div>
                                    <div class="text-sm">{{ __('Revenus Ce Mois') }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Graphiques --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Rendez-vous par Mois -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-semibold text-[#647a0b] mb-4">{{ __('Rendez-vous par Mois') }}</h3>
                    <canvas id="appointmentsChart" class="w-full h-60"></canvas>
                </div>

                <!-- Revenus Mensuels -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-semibold text-[#854f38] mb-4">{{ __('Revenus Mensuels') }}</h3>
                    <canvas id="revenueChart" class="w-full h-60"></canvas>
                </div>
            </div>

            {{-- Prochains Rendez-vous --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-[#647a0b]">{{ __('Prochains Rendez-vous') }}</h3>
                    <a href="{{ route('appointments.index') }}" class="text-[#854f38] hover:text-[#6a3f2c] font-medium flex items-center">
                        {{ __('Voir tous') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#f5f5f5]">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Client') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Date et Heure') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Durée (min)') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Statut') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentAppointments as $appointment)
                                <tr class="hover:bg-[#f0f8e8] transition-colors duration-200 cursor-pointer" onclick="window.location='{{ route('appointments.show', $appointment->id) }}'">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->locale('fr_FR')->isoFormat('DD/MM/YYYY HH:mm') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $appointment->duration }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full
                                            @if($appointment->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($appointment->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500">
                                        {{ __('Aucun rendez-vous récent.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Dernières Factures --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-[#854f38]">{{ __('Dernières Factures') }}</h3>
                    <a href="{{ route('invoices.index') }}" class="text-[#647a0b] hover:text-[#4b5c08] font-medium flex items-center">
                        {{ __('Voir tous') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#f5f5f5]">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Client') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Montant') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Statut') }}</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentInvoices as $invoice)
                                <tr class="hover:bg-[#fdece6] transition-colors duration-200 cursor-pointer" onclick="window.location='{{ route('invoices.show', $invoice->id) }}'">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('fr_FR')->isoFormat('DD/MM/YYYY') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500">
                                        {{ __('Aucune facture récente.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts pour les Graphiques --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Données dynamiques du backend
            const appointmentsData = @json(array_values($appointmentsPerMonth)).map(Number);  // Convertir en nombres
            const revenueData = @json(array_values($monthlyRevenueData)).map(Number);  // Convertir en nombres
            const monthLabels = @json(array_values($months));  // Utiliser les noms de mois

            // Graphique "Rendez-vous par Mois"
            var ctxAppointments = document.getElementById('appointmentsChart').getContext('2d');
            var appointmentsChart = new Chart(ctxAppointments, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: '{{ __("Nombre de Rendez-vous") }}',
                        data: appointmentsData,
                        backgroundColor: 'rgba(100, 122, 11, 0.6)', // #647a0b
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
                        legend: {
                            display: false
                        },
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

            // Graphique "Revenus Mensuels"
            var ctxRevenue = document.getElementById('revenueChart').getContext('2d');
            var revenueChart = new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: '{{ __("Revenus (€)") }}',
                        data: revenueData,
                        backgroundColor: 'rgba(133, 79, 56, 0.2)', // #854f38
                        borderColor: 'rgba(133, 79, 56, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(133, 79, 56, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(133, 79, 56, 1)'
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
                        legend: {
                            display: false
                        },
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

    {{-- Styles personnalisés --}}
    <style>
        /* Styles personnalisés adaptés à votre marque */
        .bg-brand-green {
            background-color: #647a0b;
        }

        .text-brand-green {
            color: #647a0b;
        }

        .hover\:text-brand-orange:hover {
            color: #854f38;
        }

        /* Amélioration des tables */
        table th, table td {
            padding: 0.75rem 1rem;
        }

        /* Amélioration des graphiques */
        canvas {
            max-height: 300px;
        }

        /* Styles pour indiquer que les éléments sont cliquables */
        .cursor-pointer {
            cursor: pointer;
        }

        /* Styles pour les boutons "Voir tous" */
        .text-[#854f38]:hover {
            text-decoration: underline;
        }

        /* Couleurs de fond pour les lignes de tableau au survol */
        .hover\:bg-[#f0f8e8]:hover {
            background-color: #f0f8e8;
        }
        .hover\:bg-[#fdece6]:hover {
            background-color: #fdece6;
        }
    </style>

</x-app-layout>
