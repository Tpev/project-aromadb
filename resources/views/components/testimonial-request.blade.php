@props(['clientProfile', 'requests'])

@can('requestTestimonial', $clientProfile)
@if(auth()->user()->canUseFeature('review'))
    @php
        $internalRequest = $requests->first(fn ($item) => ! $item->isGoogle());
        $googleRequest = $requests->first(fn ($item) => $item->isGoogle());
        $canRequest = ! $internalRequest || ! $googleRequest;
        $hasEmail = filter_var($clientProfile->email, FILTER_VALIDATE_EMAIL) !== false;
        $reviewErrors = $errors->getBag('reviewRequest');
        $defaultDestination = $internalRequest ? 'google' : 'internal';
        $selectedDestination = old('destination', $defaultDestination);
        if (! in_array($selectedDestination, ['internal', 'google'], true)
            || ($selectedDestination === 'internal' && $internalRequest)
            || ($selectedDestination === 'google' && $googleRequest)) {
            $selectedDestination = $defaultDestination;
        }
        $dialogId = 'review-request-'.$clientProfile->id;
    @endphp

    <div class="space-y-3 text-sm" x-data="{
        destination: @js($selectedDestination),
        googleUrl: @js(old('google_review_url', auth()->user()->google_review_url ?? '')),
        sending: false,
        previewUrl() {
            try {
                const url = new URL(this.googleUrl.trim());
                if (url.protocol !== 'https:' || url.username || url.password || url.port || url.hash
                    || !@js(\App\Rules\GoogleReviewUrl::HOSTS).includes(url.hostname)) return null;
                const path = url.pathname;
                const valid = url.hostname === 'g.page' ? /^\/(?:r\/)?[a-zA-Z0-9_-]+\/review\/?$/.test(path)
                    : url.hostname === 'maps.app.goo.gl' ? /^\/[a-zA-Z0-9]+\/?$/.test(path)
                    : url.hostname === 'goo.gl' ? /^\/maps\/[a-zA-Z0-9]+\/?$/.test(path)
                    : /^\/local\/writereview\/?$/.test(path) && /^[a-zA-Z0-9_-]+$/.test(url.searchParams.get('placeid') || '');
                return valid ? url.href : null;
            } catch (_) { return null; }
        }
    }" @if($reviewErrors->any() && $canRequest && $hasEmail) x-init="$nextTick(() => $refs.dialog.showModal())" @endif>
        @if(session('review_request_success'))
            <p role="status" class="rounded-xl bg-green-50 p-3 text-green-800">{{ session('review_request_success') }}</p>
        @endif

        @if($internalRequest)
            <p class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-gray-700" data-review-status="internal">
                <strong>Olithea</strong> ·
                @if($internalRequest->status === 'completed')
                    Témoignage reçu le {{ $internalRequest->updated_at->format('d/m/Y') }}.
                @else
                    Demande envoyée le {{ $internalRequest->created_at->format('d/m/Y') }}.
                @endif
            </p>
        @endif
        @if($googleRequest)
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-gray-700" data-review-status="google">
                <p><strong>Google</strong> · Demande d’avis envoyée le {{ $googleRequest->created_at->format('d/m/Y') }}.</p>
                <p class="mt-1 text-xs text-gray-500">Olithea ne peut pas confirmer si le client a publié son avis sur Google.</p>
            </div>
        @endif

        @if(! $hasEmail)
            <p class="text-amber-800">Ajoutez une adresse email valide à la fiche client pour demander un témoignage.</p>
        @endif

        @if($canRequest)
            <p class="text-gray-500">Invitez votre client à partager son expérience sur Olithea ou sur Google.</p>
            <button type="button" @click="$refs.dialog.showModal()" @disabled(! $hasEmail)
                    class="inline-flex items-center justify-center rounded-lg bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#526509] focus:ring-2 focus:ring-[#647a0b] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                Demander un témoignage
            </button>

            <dialog x-ref="dialog" id="{{ $dialogId }}" aria-labelledby="{{ $dialogId }}-title"
                    class="review-request-dialog" @cancel="if (sending) $event.preventDefault()">
                <form method="POST" action="{{ route('testimonial.request', $clientProfile) }}" @submit="sending = true" class="p-5 sm:p-7">
                    @csrf
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="{{ $dialogId }}-title" class="text-xl font-semibold text-gray-900">Demander un témoignage</h2>
                            <p class="mt-2 text-sm text-gray-500">Choisissez où {{ $clientProfile->first_name }} pourra partager son expérience.</p>
                        </div>
                        <button type="button" @click="$refs.dialog.close()" :disabled="sending" aria-label="Fermer" class="rounded-lg px-2 py-1 text-xl text-gray-500 hover:bg-gray-100">×</button>
                    </div>

                    <div class="mt-4 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Destinataire</span>
                        <span class="mt-1 block break-words">{{ $clientProfile->first_name }} {{ $clientProfile->last_name }} · {{ $clientProfile->email }}</span>
                    </div>

                    @if($reviewErrors->any())
                        <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            @foreach($reviewErrors->all() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif

                    <fieldset class="mt-5 space-y-3">
                        <legend class="mb-3 text-sm font-semibold text-gray-900">Où souhaitez-vous recevoir cet avis ?</legend>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4" :class="destination === 'internal' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200'">
                            <input type="radio" name="destination" value="internal" x-model="destination" @disabled($internalRequest !== null) class="mt-1 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                            <span class="text-sm"><strong class="block text-gray-900">Témoignage sur Olithea</strong>
                                <span class="mt-1 block text-gray-600">Le client remplit le formulaire Olithea. Son témoignage apparaît sur votre profil.</span>
                                @if($internalRequest)<span class="mt-2 block text-xs text-amber-800">Une demande Olithea existe déjà pour ce client.</span>@endif
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4" :class="destination === 'google' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200'">
                            <input type="radio" name="destination" value="google" x-model="destination" @disabled($googleRequest !== null) class="mt-1 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                            <span class="text-sm"><strong class="block text-gray-900">Avis Google</strong>
                                <span class="mt-1 block text-gray-600">Le client reçoit votre lien et rédige son avis directement sur Google.</span>
                                @if($googleRequest)<span class="mt-2 block text-xs text-amber-800">Une demande Google a déjà été envoyée à ce client.</span>@endif
                            </span>
                        </label>
                    </fieldset>

                    <fieldset x-show="destination === 'google'" :disabled="destination !== 'google'" class="mt-5 space-y-3">
                        <label for="{{ $dialogId }}-url" class="block text-sm font-semibold text-gray-900">Lien pour laisser un avis Google</label>
                        <input id="{{ $dialogId }}-url" name="google_review_url" type="url" x-model="googleUrl" :required="destination === 'google'"
                               maxlength="2048" placeholder="https://g.page/r/…/review" aria-describedby="{{ $dialogId }}-help" autocomplete="off"
                               class="block w-full min-w-0 rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <a x-show="previewUrl()" :href="previewUrl()" target="_blank" rel="noopener noreferrer" class="inline-block text-sm font-semibold text-[#647a0b] underline">Tester le lien ↗</a>
                        <div id="{{ $dialogId }}-help" class="rounded-xl bg-[#f7f9ec] p-4 text-sm text-gray-700">
                            <p class="font-semibold text-gray-900">Où trouver ce lien ?</p>
                            <ol class="mt-2 list-decimal space-y-1 pl-5">
                                <li>Ouvrez la fiche Google de votre établissement avec le compte qui la gère.</li>
                                <li>Cliquez sur « Voir les avis », puis « Recevoir plus d’avis ».</li>
                                <li>Copiez le lien proposé et collez-le ici.</li>
                            </ol>
                            <p class="mt-3">Testez le lien : il doit ouvrir la rédaction d’un avis pour votre établissement.</p>
                            <a href="https://support.google.com/business/answer/16816815?hl=fr" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-[#647a0b] underline">Consulter l’aide Google ↗</a>
                        </div>
                        <input type="hidden" name="remember_google_review_url" value="0">
                        <label class="flex items-start gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember_google_review_url" value="1" @checked(old('remember_google_review_url', '1') === '1') class="mt-0.5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">
                            Mémoriser ce lien pour mes prochaines demandes. Je pourrai le modifier avant chaque envoi.
                        </label>
                    </fieldset>

                    <p class="mt-5 text-xs text-gray-500">Un email personnalisé sera envoyé à ce client après votre clic sur « Envoyer la demande ».</p>
                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" @click="$refs.dialog.close()" :disabled="sending" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-600">Annuler</button>
                        <button type="submit" :disabled="sending" class="rounded-lg bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#526509] disabled:opacity-50" x-text="sending ? 'Envoi en cours…' : 'Envoyer la demande'">Envoyer la demande</button>
                    </div>
                </form>
            </dialog>
        @elseif($reviewErrors->any())
            <p role="alert" class="text-red-700">{{ $reviewErrors->first() }}</p>
        @endif
    </div>
    @once
        <style>
            body:has(.review-request-dialog[open]) { overflow: hidden; }
            .review-request-dialog { width: min(36rem, calc(100vw - 2rem)); max-height: calc(100dvh - 2rem); margin: auto; padding: 0; border: 0; border-radius: 1rem; background: white; color: #1f2937; box-shadow: 0 24px 64px #0003; overscroll-behavior: contain; }
            .review-request-dialog::backdrop { background: rgb(25 32 21 / 55%); }
        </style>
    @endonce
@else
    <a href="{{ url('/license-tiers/pricing') }}" class="text-sm text-[#647a0b] underline">Les demandes de témoignage sont disponibles avec la formule PRO.</a>
@endif
@endcan
