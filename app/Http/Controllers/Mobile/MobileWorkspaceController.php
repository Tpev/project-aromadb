<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\Availability;
use App\Models\CommunityGroup;
use App\Models\CorporateClient;
use App\Models\DigitalTraining;
use App\Models\Event;
use App\Models\GiftVoucher;
use App\Models\GoogleBusinessAccount;
use App\Models\InventoryItem;
use App\Models\Newsletter;
use App\Models\PackProduct;
use App\Models\PracticeLocation;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\Receipt;
use App\Models\ReferralCode;
use App\Models\ReferralInvite;
use App\Models\SuperPdpReceivedInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class MobileWorkspaceController extends Controller
{
    public function products()
    {
        $items = Product::query()
            ->where('user_id', Auth::id())
            ->orderBy('display_order')
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Prestations',
            'subtitle' => 'Prix, durees, reservation en ligne et visibilite portail.',
            'icon' => 'fa-spa',
            'primaryAction' => $this->action('Ajouter', 'mobile.products.create'),
            'webAction' => $this->action('Vue web', 'products.index'),
            'stats' => [
                ['label' => 'Total', 'value' => $items->count()],
                ['label' => 'Reservables', 'value' => $items->where('can_be_booked_online', true)->count()],
                ['label' => 'Portail', 'value' => $items->where('visible_in_portal', true)->count()],
            ],
            'items' => $items->map(fn (Product $product) => [
                'title' => $product->name,
                'subtitle' => $this->money($product->price_incl_tax) . ' TTC · ' . ($product->duration ?: '-') . ' min',
                'meta' => [$product->getConsultationModes()],
                'badge' => $product->can_be_booked_online ? 'Reservable' : 'Interne',
                'badgeTone' => $product->can_be_booked_online ? 'green' : 'slate',
                'href' => $this->routeOrNull('mobile.products.edit', $product),
            ]),
            'emptyTitle' => 'Aucune prestation',
            'emptyBody' => 'Ajoutez vos premieres prestations pour alimenter votre agenda et votre portail.',
        ]);
    }

    public function createProduct()
    {
        return view('mobile.products.form', [
            'title' => 'Nouvelle prestation',
            'product' => new Product([
                'tax_rate' => 0,
                'duration' => 60,
                'can_be_booked_online' => true,
                'visible_in_portal' => true,
                'price_visible_in_portal' => true,
            ]),
            'mode' => old('mode', 'dans_le_cabinet'),
            'questionnaires' => $this->ownedQuestionnaires(),
            'action' => route('mobile.products.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function storeProduct(Request $request)
    {
        $payload = $this->validatedProductPayload($request);
        $payload['user_id'] = Auth::id();

        Product::create($payload);

        return redirect()
            ->route('mobile.products.index')
            ->with('success', 'Prestation creee.');
    }

    public function editProduct(Product $product)
    {
        $this->ensureOwnsProduct($product);

        return view('mobile.products.form', [
            'title' => 'Modifier la prestation',
            'product' => $product,
            'mode' => old('mode', $this->productMode($product)),
            'questionnaires' => $this->ownedQuestionnaires(),
            'action' => route('mobile.products.update', $product),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->ensureOwnsProduct($product);

        $product->update($this->validatedProductPayload($request));

        return redirect()
            ->route('mobile.products.index')
            ->with('success', 'Prestation mise a jour.');
    }

    public function availabilities()
    {
        $items = Availability::query()
            ->with(['products:id,name', 'practiceLocation:id,label,city'])
            ->where('user_id', Auth::id())
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->moduleView([
            'title' => 'Disponibilites',
            'subtitle' => 'Creneaux de reservation, lieux et prestations rattachees.',
            'icon' => 'fa-clock',
            'primaryAction' => $this->action('Ajouter', 'availabilities.create'),
            'webAction' => $this->action('Vue web', 'availabilities.index'),
            'stats' => [
                ['label' => 'Creneaux', 'value' => $items->count()],
                ['label' => 'Tous services', 'value' => $items->where('applies_to_all', true)->count()],
                ['label' => 'Avec lieu', 'value' => $items->whereNotNull('practice_location_id')->count()],
            ],
            'items' => $items->map(fn (Availability $availability) => [
                'title' => $this->dayLabel((int) $availability->day_of_week),
                'subtitle' => $this->timeLabel($availability->start_time) . ' - ' . $this->timeLabel($availability->end_time),
                'meta' => [
                    $availability->practiceLocation?->label ?: 'Sans lieu specifique',
                    $availability->applies_to_all ? 'Toutes les prestations' : $availability->products->pluck('name')->take(2)->implode(', '),
                ],
                'badge' => $availability->applies_to_all ? 'Global' : 'Cible',
                'badgeTone' => $availability->applies_to_all ? 'green' : 'amber',
                'href' => $this->routeOrNull('availabilities.edit', $availability),
            ]),
            'emptyTitle' => 'Aucune disponibilite',
            'emptyBody' => 'Definissez vos creneaux pour ouvrir la reservation en ligne.',
        ]);
    }

    public function locations()
    {
        $items = PracticeLocation::query()
            ->withCount('appointments')
            ->where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Lieux de pratique',
            'subtitle' => 'Cabinets, adresses partagees et lieux visibles pour les reservations.',
            'icon' => 'fa-map-marker-alt',
            'primaryAction' => $this->action('Ajouter', 'practice-locations.create'),
            'webAction' => $this->action('Vue web', 'practice-locations.index'),
            'stats' => [
                ['label' => 'Lieux', 'value' => $items->count()],
                ['label' => 'Principal', 'value' => $items->where('is_primary', true)->count()],
                ['label' => 'Partages', 'value' => $items->where('is_shared', true)->count()],
            ],
            'items' => $items->map(fn (PracticeLocation $location) => [
                'title' => $location->label ?: 'Lieu sans nom',
                'subtitle' => $location->full_address ?: 'Adresse non renseignee',
                'meta' => [$location->appointments_count . ' RDV lies'],
                'badge' => $location->is_primary ? 'Principal' : ($location->is_shared ? 'Partage' : 'Cabinet'),
                'badgeTone' => $location->is_primary ? 'green' : 'slate',
                'href' => $this->routeOrNull('practice-locations.edit', $location),
            ]),
            'emptyTitle' => 'Aucun lieu',
            'emptyBody' => 'Ajoutez vos cabinets ou lieux de consultation pour organiser les reservations.',
        ]);
    }

    public function questionnaires()
    {
        $items = Questionnaire::query()
            ->withCount('questions')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Questionnaires',
            'subtitle' => 'Formulaires envoyes aux clients et automatisations de reservation.',
            'icon' => 'fa-clipboard-list',
            'primaryAction' => $this->action('Ajouter', 'questionnaires.create'),
            'webAction' => $this->action('Vue web', 'questionnaires.index'),
            'stats' => [
                ['label' => 'Modeles', 'value' => $items->count()],
                ['label' => 'Questions', 'value' => $items->sum('questions_count')],
                ['label' => 'Avec questions', 'value' => $items->filter(fn ($questionnaire) => $questionnaire->questions_count > 0)->count()],
            ],
            'items' => $items->map(fn (Questionnaire $questionnaire) => [
                'title' => $questionnaire->title,
                'subtitle' => $questionnaire->description ?: 'Sans description',
                'meta' => [$questionnaire->questions_count . ' questions'],
                'badge' => 'Modele',
                'badgeTone' => 'slate',
                'href' => $this->routeOrNull('questionnaires.show', $questionnaire),
            ]),
            'emptyTitle' => 'Aucun questionnaire',
            'emptyBody' => 'Creez un modele pour preparer vos suivis et demandes avant rendez-vous.',
        ]);
    }

    public function events()
    {
        $items = Event::query()
            ->withCount('reservations')
            ->where('user_id', Auth::id())
            ->orderByDesc('start_date_time')
            ->limit(30)
            ->get();

        return $this->moduleView([
            'title' => 'Evenements',
            'subtitle' => 'Ateliers, stages, visios et reservations associees.',
            'icon' => 'fa-calendar-plus',
            'primaryAction' => $this->action('Ajouter', 'events.create'),
            'webAction' => $this->action('Vue web', 'events.index'),
            'stats' => [
                ['label' => 'Evenements', 'value' => $items->count()],
                ['label' => 'A venir', 'value' => $items->filter(fn ($event) => $this->dateOrNull($event->start_date_time)?->isFuture())->count()],
                ['label' => 'Reservations', 'value' => $items->sum('reservations_count')],
            ],
            'items' => $items->map(fn (Event $event) => [
                'title' => $event->name,
                'subtitle' => $this->dateOrNull($event->start_date_time)?->format('d/m/Y H:i') ?: 'Date non renseignee',
                'meta' => [$event->event_type === 'visio' ? 'Visio' : ($event->location ?: 'Presentiel'), $event->reservations_count . ' reservations'],
                'badge' => $event->showOnPortail ? 'Portail' : 'Prive',
                'badgeTone' => $event->showOnPortail ? 'green' : 'slate',
                'href' => $this->routeOrNull('events.show', $event),
            ]),
            'emptyTitle' => 'Aucun evenement',
            'emptyBody' => 'Creez un atelier ou une visio pour ouvrir des reservations groupees.',
        ]);
    }

    public function documents()
    {
        return $this->clientScopedModule(
            'Documents clients',
            'fa-folder-open',
            'Les documents restent rattaches aux fiches clients. Ouvrez un client pour consulter, televerser ou envoyer ses fichiers.'
        );
    }

    public function signatures()
    {
        return $this->clientScopedModule(
            'Emargements',
            'fa-signature',
            'Les demandes de signature et preuves d emargement sont rattachees aux rendez-vous. Ouvrez un rendez-vous pour agir.'
        );
    }

    public function receipts()
    {
        $items = Receipt::query()
            ->where('user_id', Auth::id())
            ->latest('encaissement_date')
            ->latest('id')
            ->limit(40)
            ->get();

        return $this->moduleView([
            'title' => 'Livre de recettes',
            'subtitle' => 'Encaissements scelles, corrections et total net.',
            'icon' => 'fa-receipt',
            'primaryAction' => $this->action('Ajouter', 'receipts.create'),
            'webAction' => $this->action('Vue web', 'receipts.index'),
            'stats' => [
                ['label' => 'Lignes', 'value' => $items->count()],
                ['label' => 'Net TTC', 'value' => $this->money($items->sum('signed_amount_ttc'))],
                ['label' => 'Corrections', 'value' => $items->where('is_reversal', true)->count()],
            ],
            'items' => $items->map(fn (Receipt $receipt) => [
                'title' => '#' . $receipt->record_number . ' · ' . ($receipt->client_name ?: 'Sans client'),
                'subtitle' => optional($receipt->encaissement_date)->format('d/m/Y') . ' · ' . $this->money($receipt->signed_amount_ttc),
                'meta' => [$receipt->payment_method_label, $receipt->nature ?: 'Recette'],
                'badge' => $receipt->direction === 'debit' ? 'Debit' : 'Credit',
                'badgeTone' => $receipt->direction === 'debit' ? 'amber' : 'green',
                'href' => $this->routeOrNull('receipts.index'),
            ]),
            'emptyTitle' => 'Aucune recette',
            'emptyBody' => 'Les encaissements apparaitront ici apres saisie ou paiement.',
        ]);
    }

    public function inventory()
    {
        $items = InventoryItem::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return $this->moduleView([
            'title' => 'Stock',
            'subtitle' => 'Huiles, produits, quantites restantes et prix de vente.',
            'icon' => 'fa-boxes',
            'primaryAction' => $this->action('Ajouter', 'inventory_items.create'),
            'webAction' => $this->action('Vue web', 'inventory_items.index'),
            'stats' => [
                ['label' => 'Articles', 'value' => $items->count()],
                ['label' => 'Bas stock', 'value' => $items->filter(fn ($item) => (float) ($item->quantity_remaining ?? $item->quantity_in_stock ?? 0) <= 1)->count()],
                ['label' => 'Valeur vente', 'value' => $this->money($items->sum(fn ($item) => (float) ($item->selling_price ?? 0) * (float) ($item->quantity_remaining ?? $item->quantity_in_stock ?? 0)))],
            ],
            'items' => $items->map(fn (InventoryItem $item) => [
                'title' => $item->name,
                'subtitle' => trim(($item->brand ? $item->brand . ' · ' : '') . ($item->reference ?: 'Sans reference')),
                'meta' => ['Stock: ' . ($item->quantity_remaining ?? $item->quantity_in_stock ?? 0), 'Vente: ' . $this->money($item->selling_price)],
                'badge' => ((float) ($item->quantity_remaining ?? $item->quantity_in_stock ?? 0) <= 1) ? 'A verifier' : 'OK',
                'badgeTone' => ((float) ($item->quantity_remaining ?? $item->quantity_in_stock ?? 0) <= 1) ? 'amber' : 'green',
                'href' => $this->routeOrNull('inventory_items.edit', $item),
            ]),
            'emptyTitle' => 'Aucun article',
            'emptyBody' => 'Ajoutez votre stock pour suivre vos couts et consommations.',
        ]);
    }

    public function corporateClients()
    {
        $items = CorporateClient::query()
            ->withCount('clientProfiles')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return $this->moduleView([
            'title' => 'Entreprises',
            'subtitle' => 'Clients entreprise, facturation et contacts associes.',
            'icon' => 'fa-building',
            'primaryAction' => $this->action('Ajouter', 'corporate-clients.create'),
            'webAction' => $this->action('Vue web', 'corporate-clients.index'),
            'stats' => [
                ['label' => 'Entreprises', 'value' => $items->count()],
                ['label' => 'Contacts', 'value' => $items->sum('client_profiles_count')],
                ['label' => 'Avec email', 'value' => $items->whereNotNull('billing_email')->count()],
            ],
            'items' => $items->map(fn (CorporateClient $company) => [
                'title' => $company->name,
                'subtitle' => $company->billing_city ?: ($company->billing_email ?: 'Coordonnees a completer'),
                'meta' => [$company->client_profiles_count . ' contacts', $company->siret ? 'SIRET ' . $company->siret : 'SIRET manquant'],
                'badge' => 'Entreprise',
                'badgeTone' => 'slate',
                'href' => $this->routeOrNull('corporate-clients.show', $company),
            ]),
            'emptyTitle' => 'Aucune entreprise',
            'emptyBody' => 'Ajoutez vos clients professionnels pour simplifier la facturation B2B.',
        ]);
    }

    public function packs()
    {
        $items = PackProduct::query()
            ->withCount(['items', 'purchases'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Packs',
            'subtitle' => 'Offres groupees, achats clients et echeanciers.',
            'icon' => 'fa-layer-group',
            'primaryAction' => $this->action('Ajouter', 'pack-products.create'),
            'webAction' => $this->action('Vue web', 'pack-products.index'),
            'stats' => [
                ['label' => 'Packs', 'value' => $items->count()],
                ['label' => 'Actifs', 'value' => $items->where('is_active', true)->count()],
                ['label' => 'Vendus', 'value' => $items->sum('purchases_count')],
            ],
            'items' => $items->map(fn (PackProduct $pack) => [
                'title' => $pack->name,
                'subtitle' => $this->money($pack->price_incl_tax) . ' TTC',
                'meta' => [$pack->items_count . ' elements', $pack->purchases_count . ' achats'],
                'badge' => $pack->is_active ? 'Actif' : 'Inactif',
                'badgeTone' => $pack->is_active ? 'green' : 'slate',
                'href' => $this->routeOrNull('pack-products.show', $pack),
            ]),
            'emptyTitle' => 'Aucun pack',
            'emptyBody' => 'Creez des offres groupees pour vendre plusieurs prestations ensemble.',
        ]);
    }

    public function giftVouchers()
    {
        $items = GiftVoucher::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->limit(40)
            ->get();

        return $this->moduleView([
            'title' => 'Bons cadeaux',
            'subtitle' => 'Ventes, soldes restants et expirations.',
            'icon' => 'fa-gift',
            'primaryAction' => $this->action('Ajouter', 'pro.gift-vouchers.create'),
            'webAction' => $this->action('Vue web', 'pro.gift-vouchers.index'),
            'stats' => [
                ['label' => 'Bons', 'value' => $items->count()],
                ['label' => 'Actifs', 'value' => $items->filter->isUsable()->count()],
                ['label' => 'Solde', 'value' => $this->money($items->sum('remaining_amount_cents') / 100)],
            ],
            'items' => $items->map(fn (GiftVoucher $voucher) => [
                'title' => $voucher->code,
                'subtitle' => ($voucher->recipient_name ?: 'Beneficiaire non renseigne') . ' · reste ' . $voucher->remainingAmountStr(),
                'meta' => [$voucher->buyer_name ?: 'Acheteur non renseigne', $voucher->expiresAtStr() ? 'Expire le ' . $voucher->expiresAtStr() : 'Sans expiration'],
                'badge' => $voucher->statusLabel(),
                'badgeTone' => $voucher->isUsable() ? 'green' : 'slate',
                'href' => $this->routeOrNull('pro.gift-vouchers.show', $voucher),
            ]),
            'emptyTitle' => 'Aucun bon cadeau',
            'emptyBody' => 'Activez les bons cadeaux pour vendre des credits utilisables plus tard.',
        ]);
    }

    public function receivedInvoices()
    {
        $items = SuperPdpReceivedInvoice::query()
            ->where('user_id', Auth::id())
            ->latest('invoice_date')
            ->limit(40)
            ->get();

        return $this->moduleView([
            'title' => 'Factures recues',
            'subtitle' => 'Suivi Super PDP des factures fournisseurs recues.',
            'icon' => 'fa-file-import',
            'webAction' => $this->action('Vue web', 'super-pdp.received-invoices.index'),
            'stats' => [
                ['label' => 'Factures', 'value' => $items->count()],
                ['label' => 'Total TTC', 'value' => $this->money($items->sum('total_with_vat'))],
                ['label' => 'Synchronisees', 'value' => $items->whereNotNull('last_synced_at')->count()],
            ],
            'items' => $items->map(fn (SuperPdpReceivedInvoice $invoice) => [
                'title' => $invoice->invoice_number ?: 'Facture sans numero',
                'subtitle' => ($invoice->seller_name ?: 'Fournisseur inconnu') . ' · ' . $this->money($invoice->total_with_vat),
                'meta' => [optional($invoice->invoice_date)->format('d/m/Y') ?: 'Date inconnue', $invoice->latest_event_text ?: $invoice->latest_event_code],
                'badge' => $invoice->currency_code ?: 'EUR',
                'badgeTone' => 'slate',
                'href' => $this->routeOrNull('super-pdp.received-invoices.index'),
            ]),
            'emptyTitle' => 'Aucune facture recue',
            'emptyBody' => 'Les factures synchronisees depuis Super PDP apparaitront ici.',
        ]);
    }

    public function digitalTrainings()
    {
        $items = DigitalTraining::query()
            ->withCount(['modules', 'enrollments'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Formations digitales',
            'subtitle' => 'Programmes, modules, acces clients et ventes numeriques.',
            'icon' => 'fa-graduation-cap',
            'primaryAction' => $this->action('Ajouter', 'digital-trainings.create'),
            'webAction' => $this->action('Vue web', 'digital-trainings.index'),
            'stats' => [
                ['label' => 'Formations', 'value' => $items->count()],
                ['label' => 'Publiees', 'value' => $items->where('status', 'published')->count()],
                ['label' => 'Inscrits', 'value' => $items->sum('enrollments_count')],
            ],
            'items' => $items->map(fn (DigitalTraining $training) => [
                'title' => $training->title,
                'subtitle' => $training->formatted_price ?: 'Gratuit',
                'meta' => [$training->modules_count . ' modules', $training->enrollments_count . ' inscrits'],
                'badge' => $training->status ?: 'Brouillon',
                'badgeTone' => $training->status === 'published' ? 'green' : 'slate',
                'href' => $this->routeOrNull('digital-trainings.edit', $training),
            ]),
            'emptyTitle' => 'Aucune formation',
            'emptyBody' => 'Creez des contenus digitaux pour vendre ou partager vos programmes.',
        ]);
    }

    public function communities()
    {
        $items = CommunityGroup::query()
            ->withCount(['channels', 'members', 'messages'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Communautes',
            'subtitle' => 'Groupes clients, salons, messages et fichiers partages.',
            'icon' => 'fa-comments',
            'primaryAction' => $this->action('Ajouter', 'communities.create'),
            'webAction' => $this->action('Vue web', 'communities.index'),
            'stats' => [
                ['label' => 'Groupes', 'value' => $items->count()],
                ['label' => 'Membres', 'value' => $items->sum('members_count')],
                ['label' => 'Messages', 'value' => $items->sum('messages_count')],
            ],
            'items' => $items->map(fn (CommunityGroup $community) => [
                'title' => $community->name,
                'subtitle' => $community->description ?: 'Sans description',
                'meta' => [$community->channels_count . ' salons', $community->members_count . ' membres'],
                'badge' => $community->is_archived ? 'Archivee' : 'Active',
                'badgeTone' => $community->is_archived ? 'slate' : 'green',
                'href' => $this->routeOrNull('communities.show', $community),
            ]),
            'emptyTitle' => 'Aucune communaute',
            'emptyBody' => 'Creez un groupe pour animer vos clients autour de contenus ou programmes.',
        ]);
    }

    public function audiences()
    {
        $items = Audience::query()
            ->withCount('clients')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return $this->moduleView([
            'title' => 'Audiences',
            'subtitle' => 'Segments clients utilises pour vos communications.',
            'icon' => 'fa-users',
            'primaryAction' => $this->action('Ajouter', 'audiences.create'),
            'webAction' => $this->action('Vue web', 'audiences.index'),
            'stats' => [
                ['label' => 'Audiences', 'value' => $items->count()],
                ['label' => 'Contacts', 'value' => $items->sum('clients_count')],
                ['label' => 'Actives', 'value' => $items->filter(fn ($audience) => $audience->clients_count > 0)->count()],
            ],
            'items' => $items->map(fn (Audience $audience) => [
                'title' => $audience->name,
                'subtitle' => $audience->description ?: 'Sans description',
                'meta' => [$audience->clients_count . ' clients'],
                'badge' => 'Segment',
                'badgeTone' => 'slate',
                'href' => $this->routeOrNull('audiences.show', $audience),
            ]),
            'emptyTitle' => 'Aucune audience',
            'emptyBody' => 'Creez des segments pour preparer vos newsletters.',
        ]);
    }

    public function newsletters()
    {
        $items = Newsletter::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->limit(40)
            ->get();

        return $this->moduleView([
            'title' => 'Newsletters',
            'subtitle' => 'Brouillons, envois programmes et campagnes envoyees.',
            'icon' => 'fa-envelope-open-text',
            'primaryAction' => $this->action('Ajouter', 'newsletters.create'),
            'webAction' => $this->action('Vue web', 'newsletters.index'),
            'stats' => [
                ['label' => 'Campagnes', 'value' => $items->count()],
                ['label' => 'Envoyees', 'value' => $items->whereNotNull('sent_at')->count()],
                ['label' => 'Destinataires', 'value' => $items->sum('recipients_count')],
            ],
            'items' => $items->map(fn (Newsletter $newsletter) => [
                'title' => $newsletter->title,
                'subtitle' => $newsletter->subject ?: 'Sujet non renseigne',
                'meta' => [$newsletter->recipients_count . ' destinataires', $newsletter->sent_at ? 'Envoyee le ' . $newsletter->sent_at->format('d/m/Y') : 'Non envoyee'],
                'badge' => $newsletter->status ?: 'Brouillon',
                'badgeTone' => $newsletter->sent_at ? 'green' : 'slate',
                'href' => $this->routeOrNull('newsletters.show', $newsletter),
            ]),
            'emptyTitle' => 'Aucune newsletter',
            'emptyBody' => 'Preparez vos communications clients depuis le module web complet.',
        ]);
    }

    public function googleReviews()
    {
        $items = GoogleBusinessAccount::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        return $this->moduleView([
            'title' => 'Avis Google',
            'subtitle' => 'Connexion Google Business et derniere synchronisation.',
            'icon' => 'fa-star',
            'webAction' => $this->action('Vue web', 'pro.google-reviews.index'),
            'stats' => [
                ['label' => 'Connexions', 'value' => $items->count()],
                ['label' => 'Synchronisees', 'value' => $items->whereNotNull('last_synced_at')->count()],
                ['label' => 'A configurer', 'value' => $items->isEmpty() ? 1 : 0],
            ],
            'items' => $items->map(fn (GoogleBusinessAccount $account) => [
                'title' => $account->location_title ?: $account->account_display_name,
                'subtitle' => $account->account_display_name ?: 'Compte Google Business',
                'meta' => [$account->last_synced_at ? 'Sync ' . $account->last_synced_at->format('d/m/Y H:i') : 'Jamais synchronise'],
                'badge' => 'Connecte',
                'badgeTone' => 'green',
                'href' => $this->routeOrNull('pro.google-reviews.index'),
            ]),
            'emptyTitle' => 'Google non connecte',
            'emptyBody' => 'Connectez votre fiche Google Business depuis la vue web pour suivre vos avis.',
        ]);
    }

    public function referrals()
    {
        $code = ReferralCode::query()->where('user_id', Auth::id())->first();
        $items = ReferralInvite::query()
            ->where('referrer_user_id', Auth::id())
            ->latest('id')
            ->limit(40)
            ->get();

        return $this->moduleView([
            'title' => 'Parrainage',
            'subtitle' => $code ? 'Code: ' . $code->code : 'Invitez des praticiens et suivez vos invitations.',
            'icon' => 'fa-handshake',
            'webAction' => $this->action('Vue web', 'pro.referrals.index'),
            'stats' => [
                ['label' => 'Invites', 'value' => $items->count()],
                ['label' => 'Inscrits', 'value' => $items->whereNotNull('signed_up_at')->count()],
                ['label' => 'Recompenses', 'value' => $items->whereNotNull('reward_granted_at')->count()],
            ],
            'items' => $items->map(fn (ReferralInvite $invite) => [
                'title' => $invite->email,
                'subtitle' => $invite->message ?: 'Invitation envoyee',
                'meta' => [$invite->signed_up_at ? 'Inscrit le ' . $invite->signed_up_at->format('d/m/Y') : 'Statut: ' . ($invite->status ?: 'en attente')],
                'badge' => $invite->isExpired() ? 'Expiree' : ($invite->status ?: 'Invite'),
                'badgeTone' => $invite->signed_up_at ? 'green' : 'slate',
                'href' => $this->routeOrNull('pro.referrals.index'),
            ]),
            'emptyTitle' => 'Aucune invitation',
            'emptyBody' => 'Envoyez vos invitations depuis le module parrainage.',
        ]);
    }

    public function profile()
    {
        $user = Auth::user();

        return $this->moduleView([
            'title' => 'Profil',
            'subtitle' => 'Identite publique, coordonnees et configuration de votre espace.',
            'icon' => 'fa-user-cog',
            'primaryAction' => $this->action('Modifier', 'profile.edit'),
            'webAction' => $this->action('Entreprise', 'profile.editCompanyInfo'),
            'stats' => [
                ['label' => 'Profil public', 'value' => $user->visible_annuarire_admin_set ? 'Visible' : 'Prive'],
                ['label' => 'RDV en ligne', 'value' => $user->accept_online_appointments ? 'Oui' : 'Non'],
                ['label' => 'Vues', 'value' => $user->view_count ?? 0],
            ],
            'items' => collect([
                [
                    'title' => $user->company_name ?: $user->name,
                    'subtitle' => $user->email,
                    'meta' => [$user->company_phone ?: 'Telephone non renseigne', $user->slug ? '/pro/' . $user->slug : 'Slug manquant'],
                    'badge' => $user->license_status ?: 'Compte',
                    'badgeTone' => $user->license_status === 'active' ? 'green' : 'amber',
                    'href' => $this->routeOrNull('profile.edit'),
                ],
            ]),
            'emptyTitle' => 'Profil indisponible',
            'emptyBody' => 'Votre profil sera disponible apres connexion.',
        ]);
    }

    public function subscription()
    {
        $user = Auth::user();

        return $this->moduleView([
            'title' => 'Abonnement',
            'subtitle' => 'Licence, statut et raccourcis de gestion.',
            'icon' => 'fa-id-card',
            'primaryAction' => $this->action('Gerer', 'profile.license'),
            'webAction' => $this->action('Offres', 'license-tiers.pricing'),
            'stats' => [
                ['label' => 'Statut', 'value' => $user->license_status ?: 'inconnu'],
                ['label' => 'Offre', 'value' => $user->license_product ?: '-'],
                ['label' => 'Stripe', 'value' => $user->stripe_customer_id ? 'Oui' : 'Non'],
            ],
            'items' => collect([
                [
                    'title' => 'Licence actuelle',
                    'subtitle' => $user->license_product ?: 'Aucune offre renseignee',
                    'meta' => [$user->license_status === 'active' ? 'Compte actif' : 'Action requise'],
                    'badge' => $user->license_status ?: 'Inconnu',
                    'badgeTone' => $user->license_status === 'active' ? 'green' : 'amber',
                    'href' => $this->routeOrNull('profile.license'),
                ],
            ]),
            'emptyTitle' => 'Abonnement indisponible',
            'emptyBody' => 'Votre abonnement sera disponible apres connexion.',
        ]);
    }

    protected function clientScopedModule(string $title, string $icon, string $message)
    {
        return $this->moduleView([
            'title' => $title,
            'subtitle' => $message,
            'icon' => $icon,
            'primaryAction' => $this->action('Ouvrir clients', 'mobile.clients.index'),
            'webAction' => $this->action('Ouvrir RDV', 'mobile.appointments.index'),
            'stats' => [
                ['label' => 'Acces', 'value' => 'Client'],
                ['label' => 'Mobile', 'value' => 'Oui'],
                ['label' => 'Web', 'value' => 'Complet'],
            ],
            'items' => collect([
                [
                    'title' => 'Depuis une fiche client',
                    'subtitle' => $message,
                    'meta' => ['Optimise pour retrouver vite le bon dossier'],
                    'badge' => 'Client',
                    'badgeTone' => 'green',
                    'href' => $this->routeOrNull('mobile.clients.index'),
                ],
            ]),
            'emptyTitle' => $title,
            'emptyBody' => $message,
        ]);
    }

    protected function validatedProductPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'mode' => ['required', 'string', 'in:visio,adomicile,en_entreprise,dans_le_cabinet'],
            'max_per_day' => ['nullable', 'integer', 'min:1'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'can_be_booked_online' => ['nullable', 'boolean'],
            'collect_payment' => ['nullable', 'boolean'],
            'requires_emargement' => ['nullable', 'boolean'],
            'visible_in_portal' => ['nullable', 'boolean'],
            'price_visible_in_portal' => ['nullable', 'boolean'],
            'booking_questionnaire_enabled' => ['nullable', 'boolean'],
            'booking_questionnaire_id' => ['nullable', 'integer', 'exists:questionnaires,id'],
            'booking_questionnaire_frequency' => ['nullable', 'string', 'in:first_time_only,every_booking'],
        ]);

        $questionnaireEnabled = $request->boolean('booking_questionnaire_enabled');
        $questionnaireId = $questionnaireEnabled ? (int) ($validated['booking_questionnaire_id'] ?? 0) : null;

        if ($questionnaireEnabled) {
            if (method_exists(Auth::user(), 'canUseFeature') && ! Auth::user()->canUseFeature('questionnaires')) {
                throw ValidationException::withMessages([
                    'booking_questionnaire_enabled' => 'Votre formule ne permet pas encore les questionnaires automatiques.',
                ]);
            }

            $ownsQuestionnaire = Questionnaire::query()
                ->where('user_id', Auth::id())
                ->whereKey($questionnaireId)
                ->exists();

            if (! $ownsQuestionnaire) {
                throw ValidationException::withMessages([
                    'booking_questionnaire_id' => 'Selectionnez un questionnaire de votre compte.',
                ]);
            }
        }

        $modeFlags = [
            'visio' => false,
            'adomicile' => false,
            'en_entreprise' => false,
            'dans_le_cabinet' => false,
        ];
        $modeFlags[$validated['mode']] = true;

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'tax_rate' => $validated['tax_rate'],
            'duration' => $validated['duration'] ?? null,
            'max_per_day' => $validated['max_per_day'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'can_be_booked_online' => $request->boolean('can_be_booked_online'),
            'collect_payment' => $request->boolean('collect_payment'),
            'requires_emargement' => $request->boolean('requires_emargement'),
            'visible_in_portal' => $request->boolean('visible_in_portal'),
            'price_visible_in_portal' => $request->boolean('price_visible_in_portal'),
            'booking_questionnaire_enabled' => $questionnaireEnabled,
            'booking_questionnaire_id' => $questionnaireEnabled ? $questionnaireId : null,
            'booking_questionnaire_frequency' => $questionnaireEnabled
                ? ($validated['booking_questionnaire_frequency'] ?? Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY)
                : null,
            ...$modeFlags,
        ];
    }

    protected function ownedQuestionnaires(): Collection
    {
        return Questionnaire::query()
            ->where('user_id', Auth::id())
            ->orderBy('title')
            ->get();
    }

    protected function ensureOwnsProduct(Product $product): void
    {
        abort_unless((int) $product->user_id === (int) Auth::id(), 403);
    }

    protected function productMode(Product $product): string
    {
        if ($product->visio) {
            return 'visio';
        }

        if ($product->adomicile) {
            return 'adomicile';
        }

        if ($product->en_entreprise) {
            return 'en_entreprise';
        }

        return 'dans_le_cabinet';
    }

    protected function moduleView(array $data)
    {
        $data['items'] = $data['items'] instanceof Collection ? $data['items'] : collect($data['items'] ?? []);
        $data['stats'] = $data['stats'] ?? [];
        $data['primaryAction'] = $data['primaryAction'] ?? null;
        $data['webAction'] = $data['webAction'] ?? null;

        return view('mobile.modules.index', $data);
    }

    protected function action(string $label, string $route, mixed $parameters = []): ?array
    {
        $href = $this->routeOrNull($route, $parameters);

        return $href ? ['label' => $label, 'href' => $href] : null;
    }

    protected function routeOrNull(string $route, mixed $parameters = []): ?string
    {
        if (! Route::has($route)) {
            return null;
        }

        return route($route, $parameters);
    }

    protected function dateOrNull(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ') . ' EUR';
    }

    protected function timeLabel(mixed $value): string
    {
        if (! $value) {
            return '--:--';
        }

        return substr((string) $value, 0, 5);
    }

    protected function dayLabel(int $day): string
    {
        return [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
            0 => 'Dimanche',
        ][$day] ?? 'Jour ' . $day;
    }
}
