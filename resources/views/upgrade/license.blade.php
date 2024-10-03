<x-app-layout>
    <!-- En-tête de la page -->
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Mettez à niveau votre compte') }}
        </h2>
    </x-slot>

    <!-- Section d'en-tête -->
    <div class="py-8 bg-gradient-to-r from-green-700 to-red-700 text-white text-center">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <h1 class="text-3xl font-bold">{{ __('Choisissez le forfait adapté à votre activité') }}</h1>
            <p class="text-lg">{{ __('Passez à un plan premium et débloquez toutes les fonctionnalités !') }}</p>
        </div>
    </div>

    <!-- Plans de tarification -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Plans avec options mensuelles et annuelles -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $mensuelLicenses->count() }} gap-6">
                @foreach($mensuelLicenses as $mensuelLicense)
                    @php
                        // Get the annual license by replacing '-mensuelle' with '-annuelle'
                        $annuelLicense = $annuelLicenses->firstWhere('name', str_replace('-mensuelle', '-annuelle', $mensuelLicense->name));
                        // Clean the name by removing '-mensuelle' and '-annuelle'
                        $cleanLicenseName = str_replace(['-mensuelle', '-annuelle'], '', $mensuelLicense->name);
                    @endphp
                    <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-700">{{ $cleanLicenseName }}</h3>
                            @if($mensuelLicense->name == 'Pro-mensuelle')
                                <span class="bg-green-100 text-green-800 text-sm font-medium px-2 py-1 rounded-full">{{ __('Populaire') }}</span>
                            @endif
                        </div>

                        <ul class="mb-6 space-y-2">
                            @foreach($mensuelLicense->features as $feature)
                                <li class="flex items-center">
                                    <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-600">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="mb-4 text-center">
                                <!-- Mensuel pricing -->
                                <div>
                                    <span class="text-4xl font-bold text-gray-800">{{ $mensuelLicense->price }}€</span>
                                    <span class="text-gray-500">/ {{ __('mois') }}</span>
                                </div>
                                <!-- Annuel pricing -->
                                @if($annuelLicense)
                                    <div class="mt-2">
                                        <span class="text-4xl font-bold text-gray-800">{{ $annuelLicense->price }}€</span>
                                        <span class="text-gray-500">/ {{ __('an') }}</span>
                                        <span class="ml-2 inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">{{ __('1 mois offert') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Buttons for selecting billing cycle -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Mensuel Button -->
                                <form method="POST" action="{{ route('upgrade.license.process') }}">
                                    @csrf
                                    <input type="hidden" name="license_tier_id" value="{{ $mensuelLicense->id }}">
                                    <input type="hidden" name="billing_cycle" value="monthly">
                                    <button type="submit" class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        {{ __('Choisir Mensuel') }}
                                    </button>
                                </form>

                                <!-- Annuel Button -->
                                @if($annuelLicense)
                                    <form method="POST" action="{{ route('upgrade.license.process') }}">
                                        @csrf
                                        <input type="hidden" name="license_tier_id" value="{{ $annuelLicense->id }}">
                                        <input type="hidden" name="billing_cycle" value="yearly">
                                        <button type="submit" class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Choisir Annuel') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        <div class="mt-12">
    <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">{{ __('Comparaison des licences') }}</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Fonctionnalités') }}
                    </th>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        @php
                            $cleanLicenseName = str_replace('-mensuelle', '', $mensuelLicense->name);
                        @endphp
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">
                            {{ $cleanLicenseName }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white">
                <!-- Feature Rows -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Compte utilisateur') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }} <!-- You can replace this text with dynamic data -->
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Suivi patient') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            @if($mensuelLicense->name == 'Starter')
                                200
                            @else
                                {{ __('Illimité') }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Agenda des rendez-vous') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Prise de notes') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Conseils') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Questionnaires') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Courriers') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Visio') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Rappels par email') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Rappels par SMS') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Partage de calendrier') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Import de données') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Page pro') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Prise de RDV en ligne') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Stockage de documents') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Facturation') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-700">{{ __('Paiement en ligne') }}</td>
                    @foreach($mensuelLicenses as $mensuelLicense)
                        <td class="px-6 py-4 border-b border-gray-200 text-center">
                            {{ __('Inclus') }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>


            <!-- Section FAQ -->
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">{{ __('Questions fréquentes') }}</h2>
                <div class="max-w-4xl mx-auto space-y-4">
                    <div x-data="{ open: false }" class="border-b border-gray-200 pb-4">
                        <button @click="open = !open" class="w-full flex justify-between items-center text-left text-gray-800 font-medium text-lg focus:outline-none">
                            <span>{{ __('Puis-je changer de plan plus tard ?') }}</span>
                            <svg x-show="!open" class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                            </svg>
                            <svg x-show="open" class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="mt-4 text-gray-600">
                            <p>{{ __('Oui, vous pouvez passer à un plan supérieur ou inférieur à tout moment en fonction de vos besoins.') }}</p>
                        </div>
                    </div>
                    <!-- Add other questions here -->
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush

</x-app-layout>
