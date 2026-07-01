<x-mobile-client-layout title="Messages">
    <div class="mx-auto max-w-lg space-y-5 px-4 py-5">
        <section class="space-y-2">
            <a href="{{ route('mobile.client.home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#647a0b]">
                <i class="fas fa-chevron-left text-xs"></i>
                Accueil
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Messages</h1>
            <p class="text-sm leading-6 text-gray-600">Une conversation directe avec votre praticien.</p>
        </section>

        @if(session('success'))
            <div class="rounded-xl border border-[#dfe8c8] bg-[#f7faef] px-4 py-3 text-sm font-semibold text-[#4f6508]">
                {{ session('success') }}
            </div>
        @endif

        <section class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="max-h-[52vh] space-y-3 overflow-y-auto pr-1">
                @forelse($messages as $message)
                    <article class="flex {{ $message->sender_type === 'client' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[86%] rounded-2xl px-4 py-3 {{ $message->sender_type === 'client' ? 'bg-[#647a0b] text-white' : 'bg-gray-100 text-gray-900' }}">
                            <p class="text-sm leading-6">{{ $message->content }}</p>
                            <p class="mt-2 text-[11px] {{ $message->sender_type === 'client' ? 'text-white/75' : 'text-gray-500' }}">
                                {{ $message->sender_type === 'client' ? 'Vous' : 'Praticien' }} - {{ $message->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-gray-50 px-4 py-8 text-center">
                        <p class="text-sm font-semibold text-gray-900">Aucun message</p>
                        <p class="mt-1 text-sm text-gray-500">Envoyez votre premiere question quand vous etes pret.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <form method="POST" action="{{ route('mobile.client.messages.store') }}" class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            @csrf
            <label for="content" class="text-sm font-semibold text-gray-800">Votre message</label>
            <textarea id="content"
                      name="content"
                      rows="4"
                      required
                      class="w-full rounded-xl border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"
                      placeholder="Ecrivez votre message...">{{ old('content') }}</textarea>
            <x-input-error :messages="$errors->get('content')" class="mt-2" />
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-3 text-sm font-semibold text-white">
                Envoyer
            </button>
        </form>
    </div>
</x-mobile-client-layout>
