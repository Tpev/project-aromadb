<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $page->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($content['summary'] ?? ''), 155) }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="{{ $page->seo_title ?: ($content['title'] ?? $journey->name) }}">
    <meta property="og:description" content="{{ $page->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($content['summary'] ?? ''), 155) }}">
    <meta property="og:url" content="{{ route('offer-journeys.public.show', ['therapist' => $therapist, 'journeySlug' => $journey->slug, 'pageSlug' => $page->slug]) }}">
    <meta name="robots" content="{{ ($isPreview ?? false) ? 'noindex,nofollow' : ($page->is_indexable ? 'index,follow' : 'noindex,follow') }}">
    @unless($isPreview ?? false)<link rel="canonical" href="{{ route('offer-journeys.public.show', ['therapist' => $therapist, 'journeySlug' => $journey->slug, 'pageSlug' => $page->slug]) }}">@endunless
    <title>{{ $page->seo_title ?: ($content['title'] ?? $journey->name) }} · {{ $therapist->company_name ?: $therapist->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f3] font-sans text-gray-900 antialiased">
    @php
        $publicForm = $content['_form'] ?? null;
        $isPreview = $isPreview ?? false;
        $continueUrl = $primaryActionUrl ?? route('offer-journeys.public.continue', ['therapist' => $therapist, 'journeySlug' => $journey->slug, 'pageSlug' => $page->slug, ...request()->only(['oj_campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content'])]);
        $blocks = collect($content['blocks'] ?? [])->sortBy('position')->values();
        $heroImage = data_get($blocks->firstWhere('type', 'hero_image'), 'data');
    @endphp
    <header class="border-b border-[#e2e7d3] bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('therapist.show', $therapist->slug) }}" class="min-w-0">
                <span class="block truncate font-semibold text-[#647a0b]">{{ $therapist->company_name ?: trim(($therapist->first_name ?? '').' '.($therapist->last_name ?? '')) }}</span>
                <span class="block truncate text-xs text-gray-500">Profil professionnel Olithea</span>
            </a>
            <a href="{{ route('therapist.show', $therapist->slug) }}" class="shrink-0 text-sm font-medium text-gray-600 hover:text-[#854f38]">Voir le profil</a>
        </div>
    </header>

    <main>
        @if($isPreview)<div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-900">Aperçu du brouillon · le formulaire est désactivé</div>@endif
        <section class="bg-white">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-center lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-[#854f38]">{{ $journey->name }}</p>
                    <h1 class="mt-3 max-w-3xl text-3xl font-bold leading-tight text-gray-950 sm:text-4xl">{{ $content['title'] ?? $journey->name }}</h1>
                    @if(filled($content['summary'] ?? null))<p class="mt-5 max-w-2xl text-lg leading-8 text-gray-600">{{ $content['summary'] }}</p>@endif
                    <div class="mt-7">
                        @if($hasPublicAction && !in_array($page->type, ['opt_in', 'qualification'], true))
                            <a href="{{ $continueUrl }}" class="inline-flex w-full items-center justify-center rounded-md bg-[#647a0b] px-5 py-3 text-base font-semibold text-white hover:bg-[#526509] focus:outline-none focus:ring-2 focus:ring-[#647a0b] focus:ring-offset-2 sm:w-auto">{{ $content['cta_label'] ?? 'Continuer' }}</a>
                        @elseif(in_array($page->type, ['opt_in', 'qualification'], true) && $publicForm)
                            @php
                                $trackingQuery = array_filter(request()->only(['oj_campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content']));
                                $captureUrl = $isPreview ? '#' : route('offer-journeys.public.capture', [
                                    'therapist' => $therapist,
                                    'journeySlug' => $journey->slug,
                                    'pageSlug' => $page->slug,
                                ]).($trackingQuery ? '?'.http_build_query($trackingQuery) : '');
                            @endphp
                            <form method="POST" action="{{ $captureUrl }}" @if($isPreview) onsubmit="return false" @endif class="max-w-xl rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                @csrf
                                <div class="hidden" aria-hidden="true"><label for="website">Site internet</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
                                @if($errors->any())
                                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                                        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                                    </div>
                                @endif
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach($publicForm['fields'] ?? [] as $field)
                                        @php
                                            $fieldName = $field['name'] ?? '';
                                            $fieldType = in_array($field['type'] ?? '', ['text', 'email', 'tel'], true) ? $field['type'] : 'text';
                                            $fieldOptions = $field['options_json']['options'] ?? [];
                                            $showIf = $field['options_json']['show_if'] ?? null;
                                        @endphp
                                        <div class="{{ $fieldName === 'email' || str_starts_with($fieldName, 'custom_') ? 'sm:col-span-2' : '' }}"
                                             @if(is_array($showIf)) data-show-if-field="{{ $showIf['field'] ?? '' }}" data-show-if-value="{{ $showIf['value'] ?? '' }}" @endif>
                                            <label for="field-{{ $fieldName }}" class="block text-sm font-medium text-gray-700">{{ $field['label'] ?? $fieldName }} @if(!empty($field['is_required']))<span aria-hidden="true">*</span>@endif</label>
                                            @if($fieldName === 'contact_preference')
                                                <select id="field-{{ $fieldName }}" name="{{ $fieldName }}" @required(!empty($field['is_required'])) class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="email">Par email</option><option value="phone">Par téléphone</option></select>
                                            @elseif(($field['type'] ?? null) === 'select')
                                                <select id="field-{{ $fieldName }}" name="{{ $fieldName }}" @required(!empty($field['is_required'])) data-required="{{ !empty($field['is_required']) ? '1' : '0' }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Choisir</option>@foreach($fieldOptions as $option)<option value="{{ $option }}" @selected(old($fieldName)===$option)>{{ $option }}</option>@endforeach</select>
                                            @elseif(($field['type'] ?? null) === 'multiselect')
                                                <div id="field-{{ $fieldName }}" class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($fieldOptions as $option)<label class="flex items-center gap-2 border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option }}" @checked(in_array($option, old($fieldName, []), true)) data-required="{{ !empty($field['is_required']) ? '1' : '0' }}" class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">{{ $option }}</label>@endforeach</div>
                                            @else
                                                <input id="field-{{ $fieldName }}" type="{{ $fieldType }}" name="{{ $fieldName }}" value="{{ old($fieldName) }}" @required(!empty($field['is_required'])) data-required="{{ !empty($field['is_required']) ? '1' : '0' }}" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                            @endif
                                            @if(str_starts_with($fieldName, 'custom_') && filled($field['purpose'] ?? null))<p class="mt-1 text-xs text-gray-500">Pourquoi cette question : {{ $field['purpose'] }}</p>@endif
                                        </div>
                                    @endforeach
                                </div>
                                <label class="mt-4 flex items-start gap-3"><input type="checkbox" name="privacy_ack" value="1" required class="mt-1 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"><span class="text-sm leading-5 text-gray-600">{{ $publicForm['privacy_text'] ?? 'J’ai pris connaissance de l’utilisation de mes informations pour répondre à cette demande.' }}</span></label>
                                @if(($publicForm['marketing_consent_mode'] ?? 'optional') !== 'disabled')
                                    <label class="mt-3 flex items-start gap-3"><input type="checkbox" name="marketing_consent" value="1" class="mt-1 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"><span class="text-sm leading-5 text-gray-600">J'accepte aussi de recevoir les conseils et actualités liés à cette offre. Je peux me désinscrire à tout moment.</span></label>
                                @endif
                                <button @disabled($isPreview) class="mt-5 inline-flex w-full items-center justify-center rounded-md bg-[#647a0b] px-5 py-3 text-base font-semibold text-white hover:bg-[#526509] focus:outline-none focus:ring-2 focus:ring-[#647a0b] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">{{ $publicForm['submit_label'] ?? ($content['cta_label'] ?? 'Continuer') }}</button>
                            </form>
                        @else
                            <span class="inline-flex rounded-md bg-gray-100 px-4 py-3 text-sm text-gray-600">Cette offre n'est pas disponible actuellement.</span>
                        @endif
                    </div>
                </div>
                <aside>
                    @if(filled($heroImage['url'] ?? null))
                        <img src="{{ $heroImage['url'] }}" alt="{{ $heroImage['alt'] ?? '' }}" class="aspect-[4/3] w-full object-cover" fetchpriority="high">
                    @endif
                    <div class="{{ filled($heroImage['url'] ?? null) ? 'mt-4 border-t border-[#dfe6c7] pt-4' : 'border border-[#dfe6c7] bg-[#f7f9ec] p-5' }}">
                        <p class="text-sm font-semibold text-[#647a0b]">Votre praticien</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $therapist->company_name ?: trim(($therapist->first_name ?? '').' '.($therapist->last_name ?? '')) }}</p>
                        @if($therapist->city)<p class="mt-1 text-sm text-gray-600">{{ $therapist->city }}</p>@endif
                        <p class="mt-4 text-sm leading-6 text-gray-600">Les informations et la réservation restent gérées dans votre espace Olithea sécurisé.</p>
                    </div>
                </aside>
            </div>
        </section>

        @if($blocks->isNotEmpty())
            @include('offer-journeys.public.blocks', ['blocks' => $blocks])
        @else
        @if(filled($content['audience'] ?? null))
            <section class="border-y border-[#e5e8dc] bg-[#f7f8f3]"><div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-xl font-semibold text-gray-900">À qui s'adresse cette offre ?</h2><p class="mt-3 max-w-3xl leading-7 text-gray-600">{{ $content['audience'] }}</p></div></section>
        @endif

        @if(!empty($content['outcomes']))
            <section class="bg-white"><div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-xl font-semibold text-gray-900">Ce que vous allez obtenir</h2><ul class="mt-5 grid gap-3 sm:grid-cols-2">@foreach($content['outcomes'] as $outcome)<li class="flex gap-3 rounded-md border border-gray-200 p-4 text-gray-700"><span class="mt-0.5 text-[#647a0b]" aria-hidden="true">✓</span><span>{{ $outcome }}</span></li>@endforeach</ul></div></section>
        @endif

        @if(!empty($content['steps']))
            <section class="border-y border-[#e5e8dc] bg-[#f7f8f3]"><div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-xl font-semibold text-gray-900">Comment cela se déroule</h2><ol class="mt-5 grid gap-4 md:grid-cols-3">@foreach($content['steps'] as $step)<li class="rounded-md bg-white p-4 shadow-sm"><span class="text-sm font-semibold text-[#854f38]">Étape {{ $loop->iteration }}</span><p class="mt-2 text-gray-700">{{ $step }}</p></li>@endforeach</ol></div></section>
        @endif

        @if(filled($content['practical_details'] ?? null))
            <section class="bg-white"><div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-xl font-semibold text-gray-900">Informations pratiques</h2><p class="mt-3 max-w-3xl whitespace-pre-line leading-7 text-gray-600">{{ $content['practical_details'] }}</p></div></section>
        @endif

        @if(!empty($content['faq']))
            <section class="border-t border-[#e5e8dc] bg-[#f7f8f3]"><div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-xl font-semibold text-gray-900">Questions fréquentes</h2><div class="mt-5 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">@foreach($content['faq'] as $item)<details class="p-4"><summary class="cursor-pointer font-medium text-gray-900">{{ $item['question'] ?? '' }}</summary><p class="mt-3 leading-6 text-gray-600">{{ $item['answer'] ?? '' }}</p></details>@endforeach</div></div></section>
        @endif
        @endif

        <section class="bg-[#2f3b24] text-white"><div class="mx-auto max-w-6xl px-4 py-10 text-center sm:px-6 lg:px-8"><h2 class="text-2xl font-semibold">Prêt à passer à la prochaine étape ?</h2>@if($hasPublicAction && !in_array($page->type, ['opt_in', 'qualification'], true))<a href="{{ $continueUrl }}" class="mt-5 inline-flex rounded-md bg-white px-5 py-3 font-semibold text-[#2f3b24] hover:bg-[#f7f9ec]">{{ $content['cta_label'] ?? 'Continuer' }}</a>@endif</div></section>
    </main>

    <footer class="border-t border-gray-200 bg-white"><div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"><p>Propulsé par Olithea</p><a href="{{ route('therapist.show', $therapist->slug) }}" class="hover:text-[#647a0b]">Informations du praticien et confidentialité</a></div></footer>
    <script>
        document.querySelectorAll('[data-show-if-field]').forEach(function (container) {
            const field = container.dataset.showIfField;
            const expected = container.dataset.showIfValue;
            const source = document.querySelector('[name="' + CSS.escape(field) + '"]');
            const refresh = function () {
                const visible = source && source.value === expected;
                container.hidden = !visible;
                container.querySelectorAll('input, select, textarea').forEach(function (input) {
                    input.disabled = !visible;
                    input.required = visible && input.dataset.required === '1';
                });
            };
            source?.addEventListener('change', refresh);
            refresh();
        });
    </script>
</body>
</html>
