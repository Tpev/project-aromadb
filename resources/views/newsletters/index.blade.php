{{-- resources/views/newsletters/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Newsletters') }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-100 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @php
            $quotaLimit = (int) config('newsletters.monthly_quota', 2000);
            $monthKey   = now()->format('Y-m');

            $usageRow = \App\Models\NewsletterMonthlyUsage::where('user_id', auth()->id())
                ->where('month', $monthKey)
                ->first();

            $quotaUsed = (int) ($usageRow->sent_count ?? 0);
            $pctRaw    = $quotaLimit > 0 ? ($quotaUsed / $quotaLimit) * 100 : 0;
            $pct       = (int) round(min(100, max(0, $pctRaw)));

            $barClass = $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-orange-500' : 'bg-green-600');
        @endphp

        {{-- Header + actions --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Vos newsletters</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Créez et envoyez des emails professionnels à vos clients.
                </p>

                {{-- Quota progress --}}
                <div class="mt-4 p-4 rounded-xl border border-gray-200 bg-white shadow-sm max-w-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-gray-800">
                            Quota d’envoi ({{ $monthKey }})
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ number_format($quotaUsed) }} / {{ number_format($quotaLimit) }} emails
                        </div>
                    </div>

                    <div class="mt-2 w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full {{ $barClass }}" style="width: {{ $pct }}%;"></div>
                    </div>

                    <div class="mt-2 text-xs text-gray-500">
                        {{ $pct }}% utilisé
                        @if($quotaLimit > 0)
                            · Reste {{ number_format(max(0, $quotaLimit - $quotaUsed)) }} emails
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('audiences.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 bg-white hover:bg-gray-50">
                    Gérer les audiences
                </a>

                <a href="{{ route('audiences.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 bg-white hover:bg-gray-50">
                    Créer une audience
                </a>

                <a href="{{ route('newsletters.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
                   style="background-color:#647a0b;">
                    <span class="mr-2">+</span> Nouvelle newsletter
                </a>
            </div>
        </div>

        @if($newsletters->isEmpty())
            <div class="border border-dashed border-gray-300 rounded-xl p-8 text-center text-gray-500 text-sm">
                Vous n’avez pas encore de newsletter. <br>
                <a href="{{ route('newsletters.create') }}" class="text-[#647a0b] font-semibold">
                    Créer votre première newsletter
                </a>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Titre</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Objet</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Destinataires</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Dernière mise à jour</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($newsletters as $newsletter)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $newsletter->title }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $newsletter->subject }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $status = $newsletter->status;
                                        $colors = match($status) {
                                            'sent'      => 'bg-green-100 text-green-800 border-green-200',
                                            'scheduled' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            default     => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $colors }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $newsletter->recipients_count }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $newsletter->updated_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('newsletters.show', $newsletter) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">
                                        Aperçu
                                    </a>
                                    <a href="{{ route('newsletters.edit', $newsletter) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-white hover:opacity-90"
                                       style="background-color:#647a0b;">
                                        Modifier
                                    </a>
                                    <form action="{{ route('newsletters.destroy', $newsletter) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Supprimer cette newsletter ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $newsletters->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
