<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $huileHE->NomHE }} Details
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto">
            <h1 class="details-title">{{ $huileHE->NomHE }} Details</h1>

            <div class="details-item">
                <label>Latin Name:</label>
                <p>{{ $huileHE->NomLatin }}</p>
            </div>

            <div class="details-item">
                <label>Provenance:</label>
                <p>{{ $huileHE->Provenance }}</p>
            </div>

            <div class="details-item">
                <label>Organe Producteur:</label>
                <p>{{ $huileHE->OrganeProducteur }}</p>
            </div>

            <div class="details-item">
                <label>Sb (Substances):</label>
                <p>{{ $huileHE->Sb }}</p>
            </div>

            <div class="details-item">
                <label>Properties:</label>
                <p>{{ $huileHE->Properties }}</p>
            </div>

            <div class="details-item">
                <label>Indications:</label>
                <p>{{ $huileHE->Indications }}</p>
            </div>

            <div class="details-item">
                <label>Contre Indications:</label>
                <p>{{ $huileHE->ContreIndications ?? 'None' }}</p>
            </div>

            <div class="details-item">
                <label>Note:</label>
                <p>{{ $huileHE->Note ?? 'None' }}</p>
            </div>

            <div class="details-item">
                <label>Description:</label>
                <p>{{ $huileHE->Description ?? 'None' }}</p>
            </div>

            <a href="{{ route('huilehes.index') }}" class="btn btn-primary mt-4">Back to List</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px; /* Ensure consistency with other pages */
        }
        .details-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Center the details container */
        }
        .details-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }
        .details-item {
            margin-bottom: 10px;
        }
        .details-item label {
            font-weight: bold;
            color: #555555;
        }
        .btn-primary {
            background-color: #16a34a;
            border-color: #16a34a;
        }
        .btn-primary:hover {
            background-color: #15803d;
            border-color: #15803d;
        }
    </style>
</x-app-layout>
