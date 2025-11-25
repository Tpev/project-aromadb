<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Créer une entreprise cliente') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle entreprise') }}</h1>

            <form action="{{ route('corporate-clients.store') }}" method="POST">
                @csrf

                @include('corporate_clients._form', ['company' => null])

                <button type="submit" class="btn-primary mt-4">
                    {{ __('Enregistrer') }}
                </button>
                <a href="{{ route('corporate-clients.index') }}" class="btn-secondary mt-4">
                    {{ __('Annuler') }}
                </a>
            </form>
        </div>
    </div>

    <style>
        .container { max-width: 900px; }
        .details-container {
            background-color:#f9f9f9;
            border-radius:10px;
            padding:30px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }
        .details-title {
            font-size:2rem;
            font-weight:bold;
            color:#647a0b;
            margin-bottom:20px;
        }
        .details-box { margin-bottom:15px; }
        .details-label {
            font-weight:bold;
            color:#647a0b;
            display:block;
            margin-bottom:5px;
        }
        .form-control {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:5px;
        }
        .btn-primary {
            background-color:#647a0b;
            color:#fff;
            padding:10px 20px;
            border:none;
            border-radius:5px;
            text-decoration:none;
        }
        .btn-primary:hover { background-color:#854f38; }
        .btn-secondary {
            background-color:transparent;
            color:#854f38;
            padding:10px 20px;
            border:1px solid #854f38;
            border-radius:5px;
            text-decoration:none;
        }
        .btn-secondary:hover { background-color:#854f38;color:#fff; }
        .text-red-500 {
            color:#e3342f;
            font-size:0.875rem;
        }
        hr.my-4 { margin:1.5rem 0; border:none; border-bottom:1px solid #e5e7eb; }
    </style>
</x-app-layout>
