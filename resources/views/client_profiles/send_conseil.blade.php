<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Envoyer un Conseil à ') }}{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Envoyer un Conseil') }}</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('client_profiles.conseils.send', $clientProfile->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-[#647a0b] font-semibold mb-2" for="conseil_id">{{ __('Sélectionner un Conseil à envoyer') }}</label>
                    <select name="conseil_id" id="conseil_id" class="border border-[#854f38] rounded-md w-full py-2 px-3">
                        <option value="" disabled selected>{{ __('Choisir un conseil') }}</option>
                        @foreach($conseils as $conseil)
                            <option value="{{ $conseil->id }}" {{ old('conseil_id') == $conseil->id ? 'selected' : '' }}>
                                {{ $conseil->name }} @if($conseil->tag) ({{ $conseil->tag }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('client_profiles.show', $clientProfile->id) }}" class="btn-secondary">{{ __('Annuler') }}</a>
                    <button type="submit" class="btn-primary">{{ __('Envoyer') }}</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .container {
            max-width: 800px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }
    </style>
</x-app-layout>
