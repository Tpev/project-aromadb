{{-- resources/views/newsletters/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Modifier la newsletter') }} – {{ $newsletter->title }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 space-y-6">
        @if(session('success'))
            <div class="rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg bg-red-100 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Modifier la newsletter</h1>
            <p class="text-sm text-gray-500">
                Mettez à jour le contenu, puis envoyez un test ou lancez l’envoi à vos clients.
            </p>
        </div>

        @include('newsletters._form', [
            'route'         => route('newsletters.update', $newsletter),
            'method'        => 'PUT',
            'initialBlocks' => $initialBlocks ?? [],
        ])

        {{-- bloc envoi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">
                Envois
            </h2>

            <form action="{{ route('newsletters.send-test', $newsletter) }}" method="POST" class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 gap-2">
                @csrf
                <div class="flex-1">
                    <label for="test_email" class="block text-xs font-medium text-gray-600 mb-1">
                        Envoyer un email de test à
                    </label>
                    <input id="test_email"
                           name="test_email"
                           type="email"
                           value="{{ old('test_email', auth()->user()->email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]">
                </div>
                <div class="mt-2 sm:mt-6">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
                            style="background-color:#647a0b;">
                        Envoyer un test
                    </button>
                </div>
            </form>

@php
    $audienceLabel = $newsletter->audience
        ? 'l’audience “' . e($newsletter->audience->name) . '”'
        : 'tous vos clients';
@endphp

<div class="border-t border-dashed border-gray-200 pt-4 flex items-center justify-between flex-col sm:flex-row gap-3">
    <div class="text-xs text-gray-500">
        Cette action enverra la newsletter à {{ $audienceLabel }} disposant d’un email (hors désabonnés).
    </div>

    <form action="{{ route('newsletters.send-now', $newsletter) }}" method="POST"
          onsubmit="return confirm('Envoyer cette newsletter à {{ $audienceLabel }} ?');">
        @csrf
        <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
                style="background-color:#854f38;">
            Envoyer maintenant
        </button>
    </form>
</div>

        </div>
    </div>
</x-app-layout>
