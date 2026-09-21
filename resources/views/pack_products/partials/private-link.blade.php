<section class="my-4 rounded-xl border border-[#dfe6c7] bg-[#f8faf2] p-4">
    @if($editable ?? false)
        <input type="hidden" name="private_checkout_enabled" value="0">
        <label class="flex items-start gap-3">
            <input type="checkbox" name="private_checkout_enabled" value="1" @checked(old('private_checkout_enabled', ($pack ?? null)?->private_checkout_enabled))>
            <span class="font-semibold">{{ __('Activer un lien privé d’achat pour ce pack') }}</span>
        </label>
        <p class="mt-2 text-sm text-gray-600">{{ __('Toute personne disposant du lien peut acheter ce pack, même s’il est masqué sur votre portail. Un compte Stripe prêt à encaisser est nécessaire.') }}</p>
        @error('private_checkout_enabled')<p class="text-red-600">{{ $message }}</p>@enderror
    @else
        <h2 class="font-semibold">{{ __('Lien privé d’achat') }}</h2>
    @endif
    @if(($pack ?? null)?->private_checkout_enabled && $pack->private_checkout_token)
        <div class="mt-3" x-data="{ copied: false, async copy() { try { await navigator.clipboard.writeText(this.$refs.link.value); this.copied = true; } catch (error) { this.$refs.link.select(); this.copied = document.execCommand('copy'); } } }">
            <label for="private-pack-link" class="sr-only">{{ __('Lien privé d’achat') }}</label>
            <input id="private-pack-link" x-ref="link" readonly class="w-full rounded-lg border border-gray-300 p-2 text-sm"
                   value="{{ route('packs.private.show', $pack->private_checkout_token) }}">
            <div class="mt-2 flex flex-wrap gap-3">
                <button type="button" class="rounded-lg border border-[#647a0b] px-3 py-2 font-semibold text-[#526508]"
                        @click="copy()">
                    {{ __('Copier le lien') }}
                </button>
                <a href="{{ route('packs.private.show', $pack->private_checkout_token) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 underline">{{ __('Voir la page') }}</a>
                <span x-show="copied" x-cloak role="status">{{ __('Lien copié') }}</span>
            </div>
        </div>
        @unless($pack->is_active)<p class="mt-2 text-sm text-amber-800">{{ __('Ce pack est inactif : le lien ne permet pas de nouveaux achats.') }}</p>@endunless
    @elseif(!($editable ?? false))
        <p class="text-sm text-gray-600">{{ __('Activez le lien privé dans les paramètres du pack pour le partager.') }}</p>
    @endif
</section>
