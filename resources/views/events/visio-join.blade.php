<x-guest-layout>
    <div class="text-center">
        <p class="text-xs font-semibold uppercase text-[#647a0b]">Événement en ligne</p>
        <h1 class="mt-2 text-2xl font-semibold text-gray-900">Avant de rejoindre la visio</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">
            Indiquez le nom qui sera visible par l’organisateur et les autres participants.
        </p>
    </div>

    <div class="mt-6 border-y border-[#e4e8d5] py-4 text-center">
        <p class="text-sm font-semibold text-gray-900">{{ $event->name }}</p>
        <p class="mt-1 text-xs text-gray-500">
            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
        </p>
    </div>

    <form method="POST" action="{{ route('events.visio.join', $event) }}" class="mt-6">
        @csrf

        <label for="display_name" class="block text-sm font-semibold text-gray-800">
            Prénom et nom
        </label>
        <input
            id="display_name"
            name="display_name"
            type="text"
            value="{{ old('display_name', $suggestedName) }}"
            maxlength="80"
            autocomplete="name"
            autofocus
            required
            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
        >
        @error('display_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-[#647a0b] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#526509] focus:outline-none focus:ring-2 focus:ring-[#647a0b] focus:ring-offset-2"
        >
            Rejoindre la visio
        </button>

        <p class="mt-4 text-center text-xs leading-5 text-gray-500">
            Votre nom est uniquement utilisé pour vous identifier pendant cette visio.
        </p>
    </form>
</x-guest-layout>
