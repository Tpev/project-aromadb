@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $formatSize = function (?int $bytes): string {
        if (! $bytes) {
            return '-';
        }

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', ' ') . ' Mo'
            : number_format($bytes / 1024, 0, ',', ' ') . ' Ko';
    };
    $statusLabel = fn (?string $status) => match ($status) {
        'draft' => 'Brouillon',
        'sent' => 'Envoye',
        'partially_signed' => 'Partiellement signe',
        'signed' => 'Signe',
        'expired' => 'Expire',
        'cancelled' => 'Annule',
        default => $status ? ucfirst($status) : 'Brouillon',
    };
    $statusClass = fn (?string $status) => match ($status) {
        'signed' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'sent', 'partially_signed' => 'border-amber-200 bg-amber-50 text-amber-700',
        'expired', 'cancelled' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-gray-200 bg-gray-50 text-gray-600',
    };
@endphp

<x-mobile-layout :title="'Documents - ' . $fullName">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.documents.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Documents clients
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-sm font-semibold text-[#647a0b]">
                        {{ strtoupper(mb_substr($clientProfile->first_name ?? 'C', 0, 1) . mb_substr($clientProfile->last_name ?? '', 0, 1)) ?: 'C' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="break-words text-xl font-semibold leading-tight text-gray-900">{{ $fullName }}</h1>
                        <p class="mt-1 break-all text-sm leading-snug text-gray-600">
                            {{ $clientProfile->email ?: 'Email non renseigne' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Fichiers</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">
                            {{ $clientProfile->clientFiles->count() }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">PDF</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">
                            {{ $documents->count() }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">En cours</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">
                            {{ $documents->whereIn('status', ['sent', 'partially_signed'])->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#647a0b]/20 bg-[#647a0b]/10 px-3 py-2 text-sm font-semibold text-[#647a0b]">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
                {{ implode(' ', $errors->all()) }}
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Fichier partage</h2>
                        <p class="mt-1 text-xs leading-snug text-gray-500">
                            Ajoutez tout type de fichier au dossier client.
                        </p>
                    </div>
                    <i class="fas fa-paperclip text-sm text-[#647a0b]"></i>
                </div>

                <form action="{{ route('mobile.documents.files.store', $clientProfile) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="mt-3 space-y-3">
                    @csrf
                    <input type="file"
                           name="file"
                           required
                           class="block w-full rounded-lg border border-[#e4e8d5] bg-white px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b]/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#647a0b]">
                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                        Importer le fichier
                    </button>
                </form>
            </section>

            <section id="documents-signing" class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">PDF a signer</h2>
                        <p class="mt-1 text-xs leading-snug text-gray-500">
                            Importez un PDF, puis envoyez un lien de signature au client.
                        </p>
                    </div>
                    <i class="fas fa-file-signature text-sm text-[#647a0b]"></i>
                </div>

                <form action="{{ route('mobile.documents.signatures.store', $clientProfile) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="mt-3 space-y-3">
                    @csrf
                    <input type="file"
                           name="file"
                           accept="application/pdf"
                           required
                           class="block w-full rounded-lg border border-[#e4e8d5] bg-white px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#647a0b]/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#647a0b]">

                    <label class="block">
                        <span class="text-xs font-semibold text-gray-600">Rendez-vous lie</span>
                        <select name="appointment_id"
                                class="mt-1 h-10 w-full rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm shadow-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Aucun</option>
                            @foreach($appointments as $appointment)
                                <option value="{{ $appointment->id }}">
                                    #{{ $appointment->id }} - {{ optional($appointment->appointment_date)->format('d/m/Y H:i') ?: 'Date inconnue' }} - {{ $appointment->product?->name ?: 'Prestation' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                        Importer le PDF
                    </button>
                </form>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Fichiers</h2>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $clientProfile->clientFiles->count() }}
                    </span>
                </div>

                @forelse($clientProfile->clientFiles as $file)
                    <article class="mb-2 rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 last:mb-0" data-mobile-client-file>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words text-sm font-semibold text-gray-900">{{ $file->original_name }}</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $formatSize($file->size) }} - {{ optional($file->created_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <i class="fas fa-file-alt shrink-0 text-sm text-gray-400"></i>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('mobile.documents.files.download', [$clientProfile, $file]) }}"
                               class="inline-flex h-9 items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-[#e4e8d5]">
                                Telecharger
                            </a>
                            <form action="{{ route('mobile.documents.files.destroy', [$clientProfile, $file]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700"
                                        onclick="return confirm('Supprimer ce fichier ?');">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-4 text-center text-sm text-gray-600">
                        Aucun fichier partage pour ce client.
                    </p>
                @endforelse
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Documents a signer</h2>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $documents->count() }}
                    </span>
                </div>

                @forelse($documents as $document)
                    @php
                        $signing = $document->signing;
                        $publicUrl = $signing ? route('documents.sign.form', $signing->token) : null;
                        $signedCount = $document->signEvents->count();
                        $canSend = $clientProfile->email && $document->status === 'draft';
                        $canResend = $signing && ! $signing->isExpired() && $signing->status !== 'signed';
                        $canDownloadFinal = $document->final_pdf_path && Storage::disk('public')->exists($document->final_pdf_path);
                    @endphp

                    <article class="mb-2 rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 last:mb-0" data-mobile-signature-document>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="break-words text-sm font-semibold text-gray-900">{{ $document->original_name }}</h3>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass($document->status) }}">
                                        {{ $statusLabel($document->status) }}
                                    </span>
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] text-gray-600 ring-1 ring-[#e4e8d5]">
                                        {{ $signedCount }} signature{{ $signedCount > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                            <i class="fas fa-file-pdf shrink-0 text-sm text-[#854f38]"></i>
                        </div>

                        @if($signing)
                            <div class="mt-3 rounded-lg bg-white p-2 text-xs text-gray-600 ring-1 ring-[#e4e8d5]">
                                @if($signing->isExpired())
                                    <span class="font-semibold text-red-700">Lien expire</span>
                                @else
                                    Expire le {{ $signing->expires_at?->format('d/m/Y H:i') ?: '-' }}
                                @endif

                                @if($publicUrl)
                                    <button type="button"
                                            class="mt-2 block w-full truncate rounded-md bg-[#f5f7eb] px-2 py-1 text-left text-[11px] text-[#647a0b]"
                                            data-copy-document-link="{{ $publicUrl }}">
                                        Copier le lien de signature
                                    </button>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('mobile.documents.signatures.original', $document) }}"
                               class="inline-flex h-9 items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-[#e4e8d5]">
                                Original
                            </a>

                            @if($canDownloadFinal)
                                <a href="{{ route('mobile.documents.signatures.final', $document) }}"
                                   class="inline-flex h-9 items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-[#e4e8d5]">
                                    PDF final
                                </a>
                            @elseif($canSend)
                                <form action="{{ route('mobile.documents.signatures.send', $document) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm">
                                        Envoyer
                                    </button>
                                </form>
                            @elseif($canResend)
                                <form action="{{ route('mobile.documents.signatures.resend', $signing) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm">
                                        Renvoyer
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex h-9 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-400">
                                    Aucune action
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-4 text-center text-sm text-gray-600">
                        Aucun PDF a signer pour ce client.
                    </p>
                @endforelse
            </section>

            <a href="{{ route('mobile.clients.show', $clientProfile) }}"
               class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm">
                Retour a la fiche client
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-copy-document-link]');
                if (!button) return;

                navigator.clipboard?.writeText(button.dataset.copyDocumentLink).then(() => {
                    const previous = button.textContent;
                    button.textContent = 'Lien copie';
                    window.setTimeout(() => {
                        button.textContent = previous;
                    }, 1400);
                });
            });
        </script>
    @endpush
</x-mobile-layout>
