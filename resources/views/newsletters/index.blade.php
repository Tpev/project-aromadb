{{-- resources/views/newsletters/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Newsletters') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();

        // Feature gate (Premium only)
        $canUseNewsletter = $user && method_exists($user, 'canUseFeature') ? $user->canUseFeature('newsletter') : false;

        // Compute required plan badge (like elsewhere)
        $plansConfig = config('license_features.plans', []);
        $familyOrder = ['free', 'starter', 'pro', 'premium']; // ignore trial on purpose
        $requiredFamily = null;
        foreach ($familyOrder as $family) {
            if (in_array('newsletter', $plansConfig[$family] ?? [], true)) {
                $requiredFamily = $family;
                break;
            }
        }
        $requiredLabel = match ($requiredFamily) {
            'starter' => 'Starter',
            'pro' => 'Pro',
            'premium' => 'Premium',
            default => 'Premium',
        };

        $upgradeUrl = url('/license-tiers/pricing');
    @endphp

    <style>
        .btn-locked {
            border: 1px solid #efe7d0;
            background: #fffaf0;
            color: #a67c00;
            cursor: pointer;
        }
        .btn-locked:hover { filter: brightness(0.99); }

        .link-locked {
            color: #a67c00;
            font-weight: 700;
        }
        .link-locked:hover { text-decoration: underline; }

        .locked-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #efe7d0;
            background: #fffaf0;
            color: #a67c00;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }
        .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
    </style>

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
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-800">Vos newsletters</h1>

                    @unless($canUseNewsletter)
                        <span class="locked-chip">
                            <span class="dot bg-[#a67c00]"></span>
                            Fonction “Newsletter” · {{ $requiredLabel }}
                        </span>
                    @endunless
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    Créez et envoyez des emails professionnels à vos clients.
                </p>

                @unless($canUseNewsletter)
                    <div class="mt-3 rounded-xl border border-[#efe7d0] bg-[#fffaf0] p-4 max-w-xl">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="text-sm text-[#7a5a00]">
                                Les actions (créer, modifier, supprimer, envoyer) sont réservées à l’offre
                                <strong>{{ $requiredLabel }}</strong>.
                            </div>
                            <a href="{{ $upgradeUrl }}"
                               class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm"
                               style="background-color:#647a0b;">
                                Voir les offres
                            </a>
                        </div>
                    </div>
                @endunless

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
                @if($canUseNewsletter)
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
                @else
                    <a href="{{ $upgradeUrl }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold btn-locked">
                        🔒 Gérer les audiences
                    </a>

                    <a href="{{ $upgradeUrl }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold btn-locked">
                        🔒 Créer une audience
                    </a>

                    <a href="{{ $upgradeUrl }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold btn-locked">
                        🔒 Nouvelle newsletter
                    </a>
                @endif
            </div>
        </div>

        @if($newsletters->isEmpty())
            <div class="border border-dashed border-gray-300 rounded-xl p-8 text-center text-gray-500 text-sm">
                Vous n’avez pas encore de newsletter. <br>
                @if($canUseNewsletter)
                    <a href="{{ route('newsletters.create') }}" class="text-[#647a0b] font-semibold">
                        Créer votre première newsletter
                    </a>
                @else
                    <a href="{{ $upgradeUrl }}" class="link-locked font-semibold">
                        🔒 Débloquer la Newsletter ({{ $requiredLabel }})
                    </a>
                @endif
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
                                    @if($canUseNewsletter)
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
                                    @else
                                        <a href="{{ $upgradeUrl }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium btn-locked">
                                            🔒 Aperçu
                                        </a>
                                        <a href="{{ $upgradeUrl }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium btn-locked">
                                            🔒 Modifier
                                        </a>
                                        <a href="{{ $upgradeUrl }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium btn-locked">
                                            🔒 Supprimer
                                        </a>
                                    @endif
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
