<nav class="mb-4 flex flex-wrap items-center gap-2 text-sm" aria-label="Fil d'Ariane">
    <a href="{{ route('client_profiles.index') }}" class="font-semibold text-[#647a0b]">Clients</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Modèles de notes</span>
    <span class="ml-auto"></span>
    <a href="{{ $templateReturnUrl }}" class="am-btn am-btn-soft">
        <i class="fas fa-arrow-left"></i> {{ $templateReturnLabel }}
    </a>
</nav>
