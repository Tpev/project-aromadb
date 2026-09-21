@php
    $importedContacts = isset($audience) && $audience->exists
        ? $audience->newsletterContacts()->where('newsletter_contacts.user_id', $audience->user_id)->orderBy('email')->get()
        : collect();
@endphp
@if($importedContacts->isNotEmpty())
    <section class="my-4 rounded-lg border border-gray-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-gray-900">Contacts newsletter importés ({{ $importedContacts->count() }})</h2>
        <p class="mt-2 text-sm text-gray-600">Cette liste a été ajoutée par l’administration. Les contacts à vérifier ou désabonnés sont exclus des envois. Les modifications des clients sélectionnés ci-dessous conservent ces contacts.</p>
        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-medium text-[#647a0b]">Voir les contacts importés</summary>
            <ul class="mt-2 max-h-72 overflow-y-auto text-sm">
                @foreach($importedContacts as $contact)
                    <li class="border-b border-gray-100 py-2 break-words">
                        {{ trim(($contact->first_name ?? '').' '.($contact->last_name ?? '')) }} · {{ $contact->email }}
                        <span class="text-gray-500">— {{ ['active' => 'Inscrit', 'pending' => 'À vérifier', 'unsubscribed' => 'Désabonné'][$contact->status] ?? 'À vérifier' }}</span>
                    </li>
                @endforeach
            </ul>
        </details>
    </section>
@endif
