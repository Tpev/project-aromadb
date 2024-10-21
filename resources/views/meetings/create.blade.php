<x-app-layout>
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Créer une nouvelle réunion</h2>

        @if(session('success'))
            <div class="mb-4 text-green-600">{{ session('success') }}</div>
        @endif

        <form action="{{ route('meetings.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la réunion</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="start_time" class="block text-sm font-medium text-gray-700">Heure de début</label>
                <input type="datetime-local" name="start_time" id="start_time" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="duration" class="block text-sm font-medium text-gray-700">Durée (en minutes)</label>
                <input type="number" name="duration" id="duration" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="participant_email" class="block text-sm font-medium text-gray-700">Email du participant</label>
                <input type="email" name="participant_email" id="participant_email" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="client_profile_id" class="block text-sm font-medium text-gray-700">Profil client (optionnel)</label>
                <select name="client_profile_id" id="client_profile_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    <option value="">Sélectionnez un profil client</option>
                    <!-- Populate this with client profiles from the database -->
                    @foreach($clientProfiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full bg-[#647a0b] hover:bg-[#854f38] text-white font-bold py-2 rounded">Créer la réunion</button>
        </form>
    </div>
</x-app-layout>
