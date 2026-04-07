<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Invitation cabinet partagé') }}
        </h2>
    </x-slot>

    <div class="container mt-5" style="max-width: 760px;">
        <div class="details-container mx-auto p-4" style="background:#f9f9f9;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.1);">
            <h1 class="details-title text-center mb-4" style="font-size:1.9rem;font-weight:700;color:#647a0b;">
                Invitation à rejoindre un cabinet partagé
            </h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <p>
                <strong>{{ $invite->invitedBy->company_name ?: trim(($invite->invitedBy->first_name ?? '').' '.($invite->invitedBy->last_name ?? '')) ?: $invite->invitedBy->name }}</strong>
                vous invite à rejoindre le cabinet <strong>{{ $invite->practiceLocation->label }}</strong>.
            </p>

            <div class="mb-4 p-3" style="background:#fff;border:1px solid rgba(133,79,56,.2);border-radius:8px;">
                <p class="mb-1"><strong>Adresse :</strong> {{ $invite->practiceLocation->full_address }}</p>
                <p class="mb-1"><strong>Statut :</strong> {{ ucfirst($invite->status) }}</p>
                <p class="mb-0"><strong>Expire le :</strong> {{ optional($invite->expires_at)->format('d/m/Y H:i') ?? '—' }}</p>
            </div>

            @guest
                <p class="mb-4">Connectez-vous avec l’adresse <strong>{{ $invite->invited_email }}</strong> pour accepter ou refuser cette invitation.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
            @else
                <p class="mb-4">
                    Compte connecté : <strong>{{ $currentUser->email }}</strong>
                </p>

                @if($invite->status === \App\Models\PracticeLocationInvite::STATUS_PENDING)
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('practice-locations.invites.accept', $invite->token) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Accepter l’invitation</button>
                        </form>

                        <form method="POST" action="{{ route('practice-locations.invites.decline', $invite->token) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Refuser</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('practice-locations.index') }}" class="btn btn-primary">Retour à mes cabinets</a>
                @endif
            @endguest
        </div>
    </div>
</x-app-layout>
