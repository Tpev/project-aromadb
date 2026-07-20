<?php

namespace App\Services;

use App\Models\User;
use App\Support\AccountDataExportResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class AccountDataExportService
{
    private const EXPORT_DIRECTORY = 'private/account-exports';

    private const SENSITIVE_COLUMN_PATTERNS = [
        '/password/i',
        '/(^|_)token($|_)/i',
        '/secret/i',
        '/remember_token/i',
        '/api[_-]?key/i',
    ];

    /**
     * Tables whose ownership is directly and exclusively expressed by user_id.
     */
    private const DIRECT_USER_TABLES = [
        'client_profiles',
        'products',
        'availabilities',
        'unavailabilities',
        'special_availabilities',
        'events',
        'inventory_items',
        'questionnaires',
        'conseils',
        'corporate_clients',
        'session_note_templates',
        'practice_locations',
        'newsletters',
        'audiences',
        'newsletter_opt_outs',
        'newsletter_monthly_usages',
        'digital_trainings',
        'pack_products',
        'gift_vouchers',
        'gift_voucher_orders',
        'booking_links',
        'therapist_articles',
        'design_templates',
        'assistant_sessions',
        'user_licenses',
        'license_histories',
        'user_lesson_progress',
        'favorites',
        'google_business_accounts',
        'super_pdp_connections',
        'offer_journeys',
        'offer_journey_pipeline_stages',
        'offer_journey_contacts',
        'offer_journey_suppressions',
        'offer_journey_tags',
        'offer_journey_segments',
        'offer_journey_tasks',
        'offer_journey_automations',
        'offer_journey_message_deliveries',
        'offer_journey_deliverability_events',
        'offer_journey_sender_controls',
        'offer_journey_reusable_sections',
        'offer_journey_message_campaigns',
        'offer_journey_abandonment_candidates',
        'offer_journey_saved_filters',
        'offer_journey_pipeline_goals',
        'offer_journey_contact_imports',
        'offer_journey_email_assets',
    ];

    private const DIRECT_THERAPIST_TABLES = [
        'testimonial_requests',
        'testimonials',
        'information_requests',
        'emargements',
    ];

    public function inspect(User $user): AccountDataExportResult
    {
        [$counts, $warnings] = $this->datasetCounts($user->id);
        [$files, $fileWarnings] = $this->ownedFiles($user->id);

        return new AccountDataExportResult(
            userId: $user->id,
            datasetCounts: $counts,
            exportedFileCount: count($files),
            warnings: array_values(array_unique([...$warnings, ...$fileWarnings])),
        );
    }

    public function export(User $user, int $chunkSize = 500): AccountDataExportResult
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('L extension PHP zip est requise (paquet php8.2-zip sur le serveur de production).');
        }

        $chunkSize = max(50, min(5000, $chunkSize));
        $this->purgeExpiredExports();

        $exportId = now()->format('Ymd-His').'-'.Str::lower(Str::random(8));
        $stagingPath = storage_path('app/'.self::EXPORT_DIRECTORY.'/.tmp-'.$user->id.'-'.$exportId);
        $archiveName = 'export-olithea-compte-'.$user->id.'-'.$exportId.'.zip';
        $relativePath = self::EXPORT_DIRECTORY.'/'.$archiveName;
        $absolutePath = Storage::disk('local')->path($relativePath);
        $warnings = [];
        $counts = [];
        $exportedFiles = 0;

        File::ensureDirectoryExists($stagingPath, 0700, true);
        File::ensureDirectoryExists(dirname($absolutePath), 0700, true);

        try {
            foreach ($this->datasets() as $dataset) {
                if (! Schema::hasTable($dataset['table'])) {
                    $warnings[] = 'Table absente ignoree: '.$dataset['table'];
                    $counts[$dataset['label']] = 0;

                    continue;
                }

                $counts[$dataset['label']] = $this->writeCsv(
                    $this->ownedQuery($dataset['table'], $user->id),
                    $dataset['table'],
                    $stagingPath.'/'.$dataset['path'],
                    $chunkSize,
                );
            }

            [$ownedFiles, $fileWarnings] = $this->ownedFiles($user->id);
            $warnings = [...$warnings, ...$fileWarnings];

            foreach ($ownedFiles as $file) {
                if ($this->copyFileToStaging($file, $stagingPath)) {
                    $exportedFiles++;
                } else {
                    $warnings[] = 'Lecture impossible pendant l export: '.$file['destination'];
                }
            }

            $this->writeReadme($stagingPath, $user, $counts, $exportedFiles);
            $this->writeManifest($stagingPath, $user, $counts, $exportedFiles, $warnings);
            $this->zipDirectory($stagingPath, $absolutePath);

            @chmod($absolutePath, 0600);

            Log::notice('Account data export generated', [
                'user_id' => $user->id,
                'archive' => $relativePath,
                'rows' => array_sum($counts),
                'files' => $exportedFiles,
                'warnings' => count(array_unique($warnings)),
            ]);

            return new AccountDataExportResult(
                userId: $user->id,
                datasetCounts: $counts,
                exportedFileCount: $exportedFiles,
                warnings: array_values(array_unique($warnings)),
                relativePath: $relativePath,
                absolutePath: $absolutePath,
                sizeBytes: File::size($absolutePath),
            );
        } catch (\Throwable $exception) {
            File::delete($absolutePath);
            throw $exception;
        } finally {
            File::deleteDirectory($stagingPath);
        }
    }

    private function datasetCounts(int $userId): array
    {
        $counts = [];
        $warnings = [];

        foreach ($this->datasets() as $dataset) {
            if (! Schema::hasTable($dataset['table'])) {
                $counts[$dataset['label']] = 0;
                $warnings[] = 'Table absente ignoree: '.$dataset['table'];

                continue;
            }

            $counts[$dataset['label']] = $this->ownedQuery($dataset['table'], $userId)->count();
        }

        return [$counts, $warnings];
    }

    private function datasets(): array
    {
        return [
            ['label' => 'Compte', 'table' => 'users', 'path' => 'compte/profil.csv'],
            ['label' => 'Licences', 'table' => 'user_licenses', 'path' => 'compte/licences.csv'],
            ['label' => 'Historique des licences', 'table' => 'license_histories', 'path' => 'compte/historique-licences.csv'],
            ['label' => 'Connexions Google Business', 'table' => 'google_business_accounts', 'path' => 'compte/google-business.csv'],
            ['label' => 'Connexion facturation electronique', 'table' => 'super_pdp_connections', 'path' => 'compte/facturation-electronique.csv'],
            ['label' => 'Fiches clients', 'table' => 'client_profiles', 'path' => 'clients/fiches-clients.csv'],
            ['label' => 'Entreprises clientes', 'table' => 'corporate_clients', 'path' => 'clients/entreprises.csv'],
            ['label' => 'Rendez-vous', 'table' => 'appointments', 'path' => 'clients/rendez-vous.csv'],
            ['label' => 'Notes de seance', 'table' => 'session_notes', 'path' => 'clients/notes-de-seance.csv'],
            ['label' => 'Modeles de notes', 'table' => 'session_note_templates', 'path' => 'clients/modeles-de-notes.csv'],
            ['label' => 'Messages clients', 'table' => 'messages', 'path' => 'clients/messages.csv'],
            ['label' => 'Questionnaires', 'table' => 'questionnaires', 'path' => 'clients/questionnaires.csv'],
            ['label' => 'Questions', 'table' => 'questions', 'path' => 'clients/questions.csv'],
            ['label' => 'Reponses aux questionnaires', 'table' => 'responses', 'path' => 'clients/reponses-questionnaires.csv'],
            ['label' => 'Mesures clients', 'table' => 'metrics', 'path' => 'clients/mesures.csv'],
            ['label' => 'Valeurs des mesures', 'table' => 'metric_entries', 'path' => 'clients/valeurs-mesures.csv'],
            ['label' => 'Fichiers clients', 'table' => 'client_files', 'path' => 'clients/fichiers.csv'],
            ['label' => 'Demandes de temoignage', 'table' => 'testimonial_requests', 'path' => 'clients/demandes-temoignage.csv'],
            ['label' => 'Temoignages', 'table' => 'testimonials', 'path' => 'clients/temoignages.csv'],
            ['label' => 'Demandes d information', 'table' => 'information_requests', 'path' => 'clients/demandes-information.csv'],
            ['label' => 'Conseils', 'table' => 'conseils', 'path' => 'clients/conseils.csv'],
            ['label' => 'Conseils envoyes', 'table' => 'client_conseil', 'path' => 'clients/conseils-envoyes.csv'],
            ['label' => 'Reunions visio', 'table' => 'meetings', 'path' => 'clients/reunions-visio.csv'],
            ['label' => 'Documents', 'table' => 'documents', 'path' => 'documents/documents.csv'],
            ['label' => 'Signatures en cours', 'table' => 'document_signings', 'path' => 'documents/signatures-en-cours.csv'],
            ['label' => 'Evenements de signature', 'table' => 'document_sign_events', 'path' => 'documents/evenements-signature.csv'],
            ['label' => 'Emargements', 'table' => 'emargements', 'path' => 'documents/emargements.csv'],
            ['label' => 'Factures', 'table' => 'invoices', 'path' => 'facturation/factures.csv'],
            ['label' => 'Lignes de facture', 'table' => 'invoice_items', 'path' => 'facturation/lignes-factures.csv'],
            ['label' => 'Historique des factures', 'table' => 'invoice_activity_logs', 'path' => 'facturation/historique-factures.csv'],
            ['label' => 'Recettes et paiements', 'table' => 'receipts', 'path' => 'facturation/recettes-et-paiements.csv'],
            ['label' => 'Factures electroniques recues', 'table' => 'super_pdp_received_invoices', 'path' => 'facturation/factures-electroniques-recues.csv'],
            ['label' => 'Achats de packs', 'table' => 'pack_purchases', 'path' => 'facturation/achats-packs.csv'],
            ['label' => 'Elements consommes des packs', 'table' => 'pack_purchase_items', 'path' => 'facturation/elements-packs.csv'],
            ['label' => 'Echeances de paiement', 'table' => 'purchase_installments', 'path' => 'facturation/echeances.csv'],
            ['label' => 'Bons cadeaux', 'table' => 'gift_vouchers', 'path' => 'facturation/bons-cadeaux.csv'],
            ['label' => 'Commandes de bons cadeaux', 'table' => 'gift_voucher_orders', 'path' => 'facturation/commandes-bons-cadeaux.csv'],
            ['label' => 'Utilisations de bons cadeaux', 'table' => 'gift_voucher_redemptions', 'path' => 'facturation/utilisations-bons-cadeaux.csv'],
            ['label' => 'Produits', 'table' => 'products', 'path' => 'catalogue/produits.csv'],
            ['label' => 'Stocks', 'table' => 'inventory_items', 'path' => 'catalogue/stocks.csv'],
            ['label' => 'Packs', 'table' => 'pack_products', 'path' => 'catalogue/packs.csv'],
            ['label' => 'Composition des packs', 'table' => 'pack_product_items', 'path' => 'catalogue/composition-packs.csv'],
            ['label' => 'Evenements et ateliers', 'table' => 'events', 'path' => 'catalogue/evenements.csv'],
            ['label' => 'Reservations aux evenements', 'table' => 'reservations', 'path' => 'catalogue/reservations-evenements.csv'],
            ['label' => 'Disponibilites', 'table' => 'availabilities', 'path' => 'agenda/disponibilites.csv'],
            ['label' => 'Produits des disponibilites', 'table' => 'availability_product', 'path' => 'agenda/disponibilites-produits.csv'],
            ['label' => 'Indisponibilites', 'table' => 'unavailabilities', 'path' => 'agenda/indisponibilites.csv'],
            ['label' => 'Disponibilites speciales', 'table' => 'special_availabilities', 'path' => 'agenda/disponibilites-speciales.csv'],
            ['label' => 'Produits des disponibilites speciales', 'table' => 'special_availability_product', 'path' => 'agenda/disponibilites-speciales-produits.csv'],
            ['label' => 'Liens de reservation', 'table' => 'booking_links', 'path' => 'agenda/liens-reservation.csv'],
            ['label' => 'Lieux de pratique possedes', 'table' => 'practice_locations', 'path' => 'agenda/lieux-pratique.csv'],
            ['label' => 'Formations digitales', 'table' => 'digital_trainings', 'path' => 'contenus/formations-digitales.csv'],
            ['label' => 'Modules de formation', 'table' => 'training_modules', 'path' => 'contenus/modules-formations.csv'],
            ['label' => 'Blocs de formation', 'table' => 'training_blocks', 'path' => 'contenus/blocs-formations.csv'],
            ['label' => 'Inscriptions aux formations', 'table' => 'digital_training_enrollments', 'path' => 'contenus/inscriptions-formations.csv'],
            ['label' => 'Commentaires de formation', 'table' => 'digital_training_block_comments', 'path' => 'contenus/commentaires-formations.csv'],
            ['label' => 'Articles', 'table' => 'therapist_articles', 'path' => 'contenus/articles.csv'],
            ['label' => 'Modeles graphiques', 'table' => 'design_templates', 'path' => 'contenus/modeles-graphiques.csv'],
            ['label' => 'Communautes', 'table' => 'community_groups', 'path' => 'communautes/communautes.csv'],
            ['label' => 'Canaux des communautes', 'table' => 'community_channels', 'path' => 'communautes/canaux.csv'],
            ['label' => 'Membres des communautes', 'table' => 'community_members', 'path' => 'communautes/membres.csv'],
            ['label' => 'Messages des communautes', 'table' => 'community_messages', 'path' => 'communautes/messages.csv'],
            ['label' => 'Pieces jointes des communautes', 'table' => 'community_message_attachments', 'path' => 'communautes/pieces-jointes.csv'],
            ['label' => 'Audiences', 'table' => 'audiences', 'path' => 'marketing/audiences.csv'],
            ['label' => 'Clients des audiences', 'table' => 'audience_client_profile', 'path' => 'marketing/audiences-clients.csv'],
            ['label' => 'Newsletters', 'table' => 'newsletters', 'path' => 'marketing/newsletters.csv'],
            ['label' => 'Destinataires des newsletters', 'table' => 'newsletter_recipients', 'path' => 'marketing/destinataires-newsletters.csv'],
            ['label' => 'Desinscriptions newsletters', 'table' => 'newsletter_opt_outs', 'path' => 'marketing/desinscriptions-newsletters.csv'],
            ['label' => 'Usage newsletters', 'table' => 'newsletter_monthly_usages', 'path' => 'marketing/usage-newsletters.csv'],
            ...$this->offerJourneyDatasets(),
        ];
    }

    private function offerJourneyDatasets(): array
    {
        $tables = [
            'offer_journeys', 'offer_journey_versions', 'offer_journey_pages', 'offer_journey_page_versions',
            'offer_journey_transitions', 'offer_journey_forms', 'offer_journey_form_fields', 'offer_journey_slug_redirects',
            'offer_journey_pipeline_stages', 'offer_journey_contacts', 'offer_journey_entries', 'offer_journey_consents',
            'offer_journey_suppressions', 'offer_journey_tags', 'offer_journey_contact_tag', 'offer_journey_segments',
            'offer_journey_segment_rules', 'offer_journey_tasks', 'offer_journey_contact_activities',
            'offer_journey_events', 'offer_journey_conversions', 'offer_journey_campaign_links',
            'offer_journey_automations', 'offer_journey_automation_versions', 'offer_journey_automation_nodes',
            'offer_journey_automation_runs', 'offer_journey_message_deliveries', 'offer_journey_automation_actions',
            'offer_journey_deliverability_events', 'offer_journey_sender_controls', 'offer_journey_reusable_sections',
            'offer_journey_form_answers', 'offer_journey_message_campaigns', 'offer_journey_message_campaign_journey',
            'offer_journey_abandonment_candidates', 'offer_journey_saved_filters', 'offer_journey_pipeline_goals',
            'offer_journey_contact_imports', 'offer_journey_email_assets', 'client_profile_offer_journey_tag',
        ];

        return array_map(fn (string $table): array => [
            'label' => 'Parcours - '.$table,
            'table' => $table,
            'path' => 'parcours-offre/'.$table.'.csv',
        ], $tables);
    }

    private function ownedQuery(string $table, int $userId): Builder
    {
        $query = DB::table($table);

        if ($table === 'users') {
            return $query->where('id', $userId);
        }

        if ($table === 'appointments') {
            return $this->nullableOwnedForeign(
                $query->where('user_id', $userId),
                'client_profile_id',
                'client_profiles',
                $userId,
            );
        }

        if ($table === 'session_notes') {
            $query->where('user_id', $userId);
            $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);

            return $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);
        }

        if ($table === 'invoices') {
            $query->where('user_id', $userId);
            $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
            $this->nullableOwnedForeign($query, 'corporate_client_id', 'corporate_clients', $userId);

            return $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);
        }

        if ($table === 'receipts') {
            return $this->nullableOwnedForeign($query->where('user_id', $userId), 'invoice_id', 'invoices', $userId);
        }

        if ($table === 'messages') {
            $query->where('user_id', $userId);

            return $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);
        }

        if ($table === 'documents') {
            $query->where('owner_user_id', $userId);
            $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);

            return $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);
        }

        if ($table === 'pack_purchases') {
            $query->where('user_id', $userId);
            $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);

            return $this->ownedForeign($query, 'pack_product_id', 'pack_products', $userId);
        }

        if ($table === 'super_pdp_received_invoices') {
            $query->where('user_id', $userId);

            return $this->ownedForeign($query, 'connection_id', 'super_pdp_connections', $userId);
        }

        if ($table === 'testimonial_requests' || $table === 'testimonials') {
            $query->where('therapist_id', $userId);

            return $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
        }

        if ($table === 'emargements') {
            return $this->ownedForeign($query->where('therapist_id', $userId), 'appointment_id', 'appointments', $userId);
        }

        if ($table === 'offer_journey_contacts') {
            return $this->nullableOwnedForeign($query->where('user_id', $userId), 'client_profile_id', 'client_profiles', $userId);
        }

        if ($table === 'offer_journey_tasks') {
            $query->where('user_id', $userId);
            $this->ownedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);

            return $this->nullableOwnedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
        }

        if ($table === 'offer_journey_automations') {
            return $this->ownedForeign($query->where('user_id', $userId), 'offer_journey_id', 'offer_journeys', $userId);
        }

        if ($table === 'offer_journey_message_deliveries') {
            $query->where('user_id', $userId);
            $this->nullableOwnedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
            $this->nullableOwnedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);

            return $query;
        }

        if ($table === 'offer_journey_abandonment_candidates') {
            $query->where('user_id', $userId);
            $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
            $this->ownedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);

            return $this->nullableOwnedForeign($query, 'offer_journey_entry_id', 'offer_journey_entries', $userId);
        }

        if ($table === 'offer_journey_email_assets') {
            return $this->ownedForeign(
                $query->where('user_id', $userId),
                'offer_journey_message_campaign_id',
                'offer_journey_message_campaigns',
                $userId,
            );
        }

        if (in_array($table, self::DIRECT_USER_TABLES, true)) {
            return Schema::hasColumn($table, 'user_id')
                ? $query->where('user_id', $userId)
                : $query->whereRaw('1 = 0');
        }

        if (in_array($table, self::DIRECT_THERAPIST_TABLES, true)) {
            return Schema::hasColumn($table, 'therapist_id')
                ? $query->where('therapist_id', $userId)
                : $query->whereRaw('1 = 0');
        }

        return match ($table) {
            'questions' => $this->byParent($query, 'questionnaire_id', 'questionnaires', $userId),
            'responses' => $this->responseQuery($query, $userId),
            'metrics' => $this->byParent($query, 'client_profile_id', 'client_profiles', $userId),
            'metric_entries' => $this->byParent($query, 'metric_id', 'metrics', $userId),
            'client_files' => $this->byParent($query, 'client_profile_id', 'client_profiles', $userId),
            'client_conseil' => $this->clientConseilQuery($query, $userId),
            'meetings' => $this->meetingQuery($query, $userId),
            'document_signings', 'document_sign_events' => $this->byParent($query, 'document_id', 'documents', $userId),
            'invoice_items', 'invoice_activity_logs' => $this->byParent($query, 'invoice_id', 'invoices', $userId),
            'pack_purchase_items', 'purchase_installments' => $this->byParent($query, 'pack_purchase_id', 'pack_purchases', $userId),
            'gift_voucher_redemptions' => $this->giftVoucherRedemptionQuery($query, $userId),
            'pack_product_items' => $this->byParent($query, 'pack_product_id', 'pack_products', $userId),
            'reservations' => $this->byParent($query, 'event_id', 'events', $userId),
            'availability_product' => $this->twoParents($query, 'availability_id', 'availabilities', 'product_id', 'products', $userId),
            'special_availability_product' => $this->twoParents($query, 'special_availability_id', 'special_availabilities', 'product_id', 'products', $userId),
            'training_modules' => $this->byParent($query, 'digital_training_id', 'digital_trainings', $userId),
            'training_blocks' => $this->byParent($query, 'training_module_id', 'training_modules', $userId),
            'digital_training_enrollments' => $this->trainingEnrollmentQuery($query, $userId),
            'digital_training_block_comments' => $this->trainingCommentQuery($query, $userId),
            'community_groups' => $query->where('user_id', $userId),
            'community_channels' => $this->byParent($query, 'community_group_id', 'community_groups', $userId),
            'community_members' => $this->twoParents($query, 'community_group_id', 'community_groups', 'client_profile_id', 'client_profiles', $userId),
            'community_messages' => $this->communityMessageQuery($query, $userId),
            'community_message_attachments' => $this->byParent($query, 'community_message_id', 'community_messages', $userId),
            'audience_client_profile' => $this->twoParents($query, 'audience_id', 'audiences', 'client_profile_id', 'client_profiles', $userId),
            'newsletter_recipients' => $this->newsletterRecipientQuery($query, $userId),
            'offer_journey_versions', 'offer_journey_pages' => $this->byParent($query, 'offer_journey_id', 'offer_journeys', $userId),
            'offer_journey_campaign_links' => $this->ownedForeign($query->where('user_id', $userId), 'offer_journey_id', 'offer_journeys', $userId),
            'offer_journey_transitions' => $this->offerJourneyTransitionQuery($query, $userId),
            'offer_journey_forms' => $this->twoParents($query, 'offer_journey_id', 'offer_journeys', 'offer_journey_page_id', 'offer_journey_pages', $userId),
            'offer_journey_slug_redirects' => $this->offerJourneySlugRedirectQuery($query, $userId),
            'offer_journey_entries' => $this->offerJourneyEntryQuery($query, $userId),
            'offer_journey_page_versions' => $this->twoParents($query, 'offer_journey_version_id', 'offer_journey_versions', 'offer_journey_page_id', 'offer_journey_pages', $userId),
            'offer_journey_form_fields' => $this->byParent($query, 'offer_journey_form_id', 'offer_journey_forms', $userId),
            'offer_journey_consents', 'offer_journey_contact_activities' => $this->byParent($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId),
            'offer_journey_contact_tag' => $this->twoParents($query, 'offer_journey_contact_id', 'offer_journey_contacts', 'offer_journey_tag_id', 'offer_journey_tags', $userId),
            'client_profile_offer_journey_tag' => $this->twoParents($query, 'client_profile_id', 'client_profiles', 'offer_journey_tag_id', 'offer_journey_tags', $userId),
            'offer_journey_segment_rules' => $this->byParent($query, 'offer_journey_segment_id', 'offer_journey_segments', $userId),
            'offer_journey_events', 'offer_journey_conversions' => $this->offerJourneyEventQuery($query, $userId),
            'offer_journey_automation_versions' => $this->byParent($query, 'offer_journey_automation_id', 'offer_journey_automations', $userId),
            'offer_journey_automation_nodes' => $this->byParent($query, 'offer_journey_automation_version_id', 'offer_journey_automation_versions', $userId),
            'offer_journey_automation_runs' => $this->automationRunQuery($query, $userId),
            'offer_journey_automation_actions' => $this->byParent($query, 'offer_journey_automation_run_id', 'offer_journey_automation_runs', $userId),
            'offer_journey_message_campaign_journey' => $this->twoParents($query, 'offer_journey_message_campaign_id', 'offer_journey_message_campaigns', 'offer_journey_id', 'offer_journeys', $userId),
            'offer_journey_form_answers' => $this->offerJourneyFormAnswerQuery($query, $userId),
            default => throw new RuntimeException('Regle de propriete manquante pour la table '.$table),
        };
    }

    private function responseQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'questionnaire_id', 'questionnaires', $userId);
        $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);

        return $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);
    }

    private function clientConseilQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'client_profile_id', 'client_profiles', $userId);

        return $this->ownedForeign($query, 'conseil_id', 'conseils', $userId);
    }

    private function meetingQuery(Builder $query, int $userId): Builder
    {
        $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
        $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);

        return $query->where(function (Builder $owned) use ($userId): void {
            if (Schema::hasColumn('meetings', 'client_profile_id')) {
                $owned->whereIn('client_profile_id', $this->ownedQuery('client_profiles', $userId)->select('client_profiles.id'));
            }

            if (Schema::hasColumn('meetings', 'appointment_id')) {
                $method = Schema::hasColumn('meetings', 'client_profile_id') ? 'orWhereIn' : 'whereIn';
                $owned->{$method}('appointment_id', $this->ownedQuery('appointments', $userId)->select('appointments.id'));
            }

            if (! Schema::hasColumn('meetings', 'client_profile_id') && ! Schema::hasColumn('meetings', 'appointment_id')) {
                $owned->whereRaw('1 = 0');
            }
        });
    }

    private function giftVoucherRedemptionQuery(Builder $query, int $userId): Builder
    {
        if (Schema::hasColumn('gift_voucher_redemptions', 'user_id')) {
            $query->where('user_id', $userId);
        }

        $this->ownedForeign($query, 'gift_voucher_id', 'gift_vouchers', $userId);
        $this->nullableOwnedForeign($query, 'appointment_id', 'appointments', $userId);

        return $this->nullableOwnedForeign($query, 'invoice_id', 'invoices', $userId);
    }

    private function trainingEnrollmentQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'digital_training_id', 'digital_trainings', $userId);

        return $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
    }

    private function trainingCommentQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'digital_training_id', 'digital_trainings', $userId);
        $this->ownedForeign($query, 'training_block_id', 'training_blocks', $userId);

        return $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
    }

    private function communityMessageQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'community_group_id', 'community_groups', $userId);
        $this->ownedForeign($query, 'community_channel_id', 'community_channels', $userId);

        return $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
    }

    private function newsletterRecipientQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'newsletter_id', 'newsletters', $userId);

        return $this->nullableOwnedForeign($query, 'client_profile_id', 'client_profiles', $userId);
    }

    private function offerJourneyEventQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);

        return $this->nullableOwnedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);
    }

    private function offerJourneyTransitionQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
        $this->ownedForeign($query, 'from_page_id', 'offer_journey_pages', $userId);

        return $this->nullableOwnedForeign($query, 'to_page_id', 'offer_journey_pages', $userId);
    }

    private function offerJourneySlugRedirectQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);

        return $this->nullableOwnedForeign($query, 'offer_journey_page_id', 'offer_journey_pages', $userId);
    }

    private function offerJourneyEntryQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
        $this->ownedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);

        return $this->nullableOwnedForeign($query, 'current_page_id', 'offer_journey_pages', $userId);
    }

    private function offerJourneyFormAnswerQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_id', 'offer_journeys', $userId);
        $this->ownedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);

        return $this->nullableOwnedForeign($query, 'offer_journey_page_version_id', 'offer_journey_page_versions', $userId);
    }

    private function automationRunQuery(Builder $query, int $userId): Builder
    {
        $this->ownedForeign($query, 'offer_journey_automation_id', 'offer_journey_automations', $userId);
        $this->ownedForeign($query, 'offer_journey_automation_version_id', 'offer_journey_automation_versions', $userId);

        return $this->nullableOwnedForeign($query, 'offer_journey_contact_id', 'offer_journey_contacts', $userId);
    }

    private function byParent(Builder $query, string $foreignKey, string $parentTable, int $userId): Builder
    {
        return $this->ownedForeign($query, $foreignKey, $parentTable, $userId);
    }

    private function twoParents(
        Builder $query,
        string $firstForeignKey,
        string $firstParent,
        string $secondForeignKey,
        string $secondParent,
        int $userId,
    ): Builder {
        $this->ownedForeign($query, $firstForeignKey, $firstParent, $userId);

        return $this->ownedForeign($query, $secondForeignKey, $secondParent, $userId);
    }

    private function ownedForeign(Builder $query, string $foreignKey, string $parentTable, int $userId): Builder
    {
        if (! Schema::hasColumn($query->from, $foreignKey)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($foreignKey, $this->ownedQuery($parentTable, $userId)->select($parentTable.'.id'));
    }

    private function nullableOwnedForeign(Builder $query, string $foreignKey, string $parentTable, int $userId): Builder
    {
        if (! Schema::hasColumn($query->from, $foreignKey)) {
            return $query;
        }

        return $query->where(function (Builder $nested) use ($foreignKey, $parentTable, $userId): void {
            $nested->whereNull($foreignKey)
                ->orWhereIn($foreignKey, $this->ownedQuery($parentTable, $userId)->select($parentTable.'.id'));
        });
    }

    private function writeCsv(Builder $query, string $table, string $path, int $chunkSize): int
    {
        $columns = array_values(array_filter(
            Schema::getColumnListing($table),
            fn (string $column): bool => ! $this->isSensitiveColumn($column),
        ));

        File::ensureDirectoryExists(dirname($path), 0700, true);
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Impossible de creer '.$path);
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns, ';');
        $count = 0;

        try {
            $orderColumn = in_array('id', $columns, true) ? 'id' : ($columns[0] ?? null);

            if ($orderColumn !== null) {
                $query->orderBy($table.'.'.$orderColumn);
            }

            $query->select(array_map(fn (string $column): string => $table.'.'.$column, $columns))
                ->chunk($chunkSize, function ($rows) use ($handle, $columns, &$count): void {
                    foreach ($rows as $row) {
                        $values = [];

                        foreach ($columns as $column) {
                            $values[] = $this->csvValue($row->{$column} ?? null);
                        }

                        fputcsv($handle, $values, ';');
                        $count++;
                    }
                });
        } finally {
            fclose($handle);
        }

        return $count;
    }

    private function csvValue(mixed $value): string|int|float|null
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $value = (string) $value;

        // Quoting alone does not stop spreadsheet formula execution.
        if (preg_match('/^[=+\-@\t\r]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    private function isSensitiveColumn(string $column): bool
    {
        foreach (self::SENSITIVE_COLUMN_PATTERNS as $pattern) {
            if (preg_match($pattern, $column) === 1) {
                return true;
            }
        }

        return false;
    }

    private function ownedFiles(int $userId): array
    {
        $files = [];
        $warnings = [];
        $definitions = [
            ['table' => 'users', 'disk' => 'public', 'fields' => ['profile_picture', 'portal_logo_path', 'cgv_pdf_path', 'invoice_logo_path', 'gift_voucher_background_path', 'digital_sales_retractation_document_path'], 'directory' => 'compte/fichiers'],
            ['table' => 'client_files', 'disk' => 'public', 'fields' => ['file_path'], 'name' => 'original_name', 'directory' => 'documents/fichiers-clients', 'group' => 'client_profile_id'],
            ['table' => 'documents', 'disk' => 'public', 'fields' => ['storage_path', 'final_pdf_path'], 'name' => 'original_name', 'directory' => 'documents/documents-signes', 'group' => 'client_profile_id'],
            ['table' => 'document_sign_events', 'disk' => 'public', 'fields' => ['signature_image_path'], 'directory' => 'documents/signatures'],
            ['table' => 'emargements', 'disk' => 'public', 'fields' => ['signature_image_path', 'pdf_path'], 'directory' => 'documents/emargements', 'group' => 'appointment_id'],
            ['table' => 'products', 'disk' => 'public', 'fields' => ['image', 'brochure'], 'directory' => 'catalogue/produits'],
            ['table' => 'events', 'disk' => 'public', 'fields' => ['image'], 'directory' => 'catalogue/evenements'],
            ['table' => 'conseils', 'disk' => 'public', 'fields' => ['image', 'attachment'], 'directory' => 'clients/conseils'],
            ['table' => 'digital_trainings', 'disk' => 'public', 'fields' => ['cover_image_path'], 'directory' => 'contenus/formations'],
            ['table' => 'training_blocks', 'disk' => 'public', 'fields' => ['file_path'], 'directory' => 'contenus/blocs-formations'],
            ['table' => 'community_message_attachments', 'disk' => 'public', 'fields' => ['file_path'], 'name' => 'original_name', 'directory' => 'communautes/pieces-jointes', 'group' => 'community_message_id'],
            ['table' => 'offer_journey_email_assets', 'disk' => 'public', 'fields' => ['path'], 'name' => 'original_name', 'directory' => 'parcours-offre/images-emails', 'group' => 'offer_journey_message_campaign_id'],
        ];

        foreach ($definitions as $definition) {
            $table = $definition['table'];

            if (! Schema::hasTable($table)) {
                continue;
            }

            $availableFields = array_values(array_filter(
                $definition['fields'],
                fn (string $field): bool => Schema::hasColumn($table, $field),
            ));

            if ($availableFields === []) {
                continue;
            }

            $columns = array_values(array_unique(array_filter([
                Schema::hasColumn($table, 'id') ? 'id' : null,
                $definition['name'] ?? null,
                $definition['group'] ?? null,
                ...$availableFields,
            ])));

            foreach ($this->ownedQuery($table, $userId)->select($columns)->cursor() as $row) {
                foreach ($availableFields as $field) {
                    $source = $row->{$field} ?? null;

                    if (! is_string($source) || trim($source) === '') {
                        continue;
                    }

                    $normalized = $this->normalizeStoragePath($source, $definition['disk']);

                    if ($normalized === null) {
                        continue;
                    }

                    $recordId = $row->id ?? 'record';
                    $group = isset($definition['group']) ? ($row->{$definition['group']} ?? 'sans-groupe') : null;
                    $originalName = isset($definition['name']) ? ($row->{$definition['name']} ?? null) : null;
                    $fileName = $this->safeFileName((string) ($originalName ?: basename($normalized)));
                    $directory = $definition['directory'].($group !== null ? '/'.$group : '');
                    $destination = $directory.'/'.$recordId.'-'.$field.'-'.$fileName;

                    if (! Storage::disk($definition['disk'])->exists($normalized)) {
                        $warnings[] = 'Fichier introuvable: '.$table.' #'.$recordId.' ('.$field.')';

                        continue;
                    }

                    $files[$definition['disk'].'|'.$normalized.'|'.$destination] = [
                        'disk' => $definition['disk'],
                        'source' => $normalized,
                        'destination' => $destination,
                    ];
                }
            }
        }

        $offerResourceRoot = 'private/offer-journeys/'.$userId;
        if (Storage::disk('local')->exists($offerResourceRoot)) {
            foreach (Storage::disk('local')->allFiles($offerResourceRoot) as $source) {
                $relative = Str::after($source, $offerResourceRoot.'/');
                $destination = 'parcours-offre/ressources-privees/'.$this->safeRelativePath($relative);
                $files['local|'.$source.'|'.$destination] = [
                    'disk' => 'local',
                    'source' => $source,
                    'destination' => $destination,
                ];
            }
        }

        return [array_values($files), $warnings];
    }

    private function normalizeStoragePath(string $path, string $disk): ?string
    {
        $path = trim(str_replace('\\', '/', rawurldecode($path)));

        if (preg_match('#^https?://#i', $path) === 1) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        if (str_contains($path, "\0")) {
            return null;
        }

        $path = ltrim($path, '/');

        if ($disk === 'public') {
            foreach (['storage/app/public/', 'app/public/', 'public/storage/', 'storage/', 'public/'] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $path = Str::after($path, $prefix);
                    break;
                }
            }
        }

        if ($path === '' || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }

    private function safeFileName(string $name): string
    {
        $name = Str::ascii(basename(str_replace('\\', '/', $name)));
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $name) ?: 'fichier';

        return trim($name, '.-') ?: 'fichier';
    }

    private function safeRelativePath(string $path): string
    {
        return collect(explode('/', str_replace('\\', '/', $path)))
            ->reject(fn (string $part): bool => $part === '' || $part === '.' || $part === '..')
            ->map(fn (string $part): string => $this->safeFileName($part))
            ->implode('/');
    }

    private function copyFileToStaging(array $file, string $stagingPath): bool
    {
        $destination = $stagingPath.'/'.$file['destination'];
        File::ensureDirectoryExists(dirname($destination), 0700, true);

        $input = Storage::disk($file['disk'])->readStream($file['source']);
        if ($input === false) {
            return false;
        }

        $output = fopen($destination, 'wb');
        if ($output === false) {
            fclose($input);

            return false;
        }

        try {
            stream_copy_to_stream($input, $output);
        } finally {
            fclose($input);
            fclose($output);
        }

        return true;
    }

    private function writeReadme(string $stagingPath, User $user, array $counts, int $fileCount): void
    {
        $content = implode(PHP_EOL, [
            'EXPORT DE DONNEES OLITHEA',
            '==========================',
            '',
            'Compte : '.$user->name.' (ID '.$user->id.')',
            'Email : '.$user->email,
            'Genere le : '.now()->toIso8601String(),
            '',
            'Cette archive contient '.array_sum($counts).' ligne(s) de donnees et '.$fileCount.' fichier(s).',
            'Les fichiers CSV utilisent UTF-8, le point-virgule comme separateur et conservent les identifiants relationnels.',
            'Les valeurs pouvant etre interpretees comme des formules par un tableur sont neutralisees par une apostrophe.',
            '',
            'Par securite, les mots de passe, jetons de connexion, liens secrets et identifiants d authentification ne sont jamais exportes.',
            'Les lieux partages et les comptes des autres praticiens ne sont pas inclus.',
            '',
            'Le fichier manifest.json contient le detail des volumes, avertissements et empreintes SHA-256.',
        ]);

        File::put($stagingPath.'/README.txt', $content.PHP_EOL);
    }

    private function writeManifest(
        string $stagingPath,
        User $user,
        array $counts,
        int $fileCount,
        array $warnings,
    ): void {
        $checksums = [];

        foreach (File::allFiles($stagingPath) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $checksums[$relative] = hash_file('sha256', $file->getPathname());
        }

        ksort($checksums);

        $manifest = [
            'format_version' => 1,
            'generated_at' => now()->toIso8601String(),
            'account' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'tenant_isolation' => 'Every dataset is restricted to this user or to parent records already owned by this user.',
            'excluded_security_fields' => ['passwords', 'authentication tokens', 'OAuth tokens', 'provider secrets', 'API keys'],
            'dataset_counts' => $counts,
            'total_rows' => array_sum($counts),
            'exported_files' => $fileCount,
            'warnings' => array_values(array_unique($warnings)),
            'sha256' => $checksums,
        ];

        File::put(
            $stagingPath.'/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );
    }

    private function zipDirectory(string $sourceDirectory, string $destination): void
    {
        $zip = new ZipArchive;
        $opened = $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            throw new RuntimeException('Impossible de creer l archive ZIP (code '.$opened.').');
        }

        try {
            foreach (File::allFiles($sourceDirectory) as $file) {
                $relative = str_replace('\\', '/', $file->getRelativePathname());

                if (! $zip->addFile($file->getPathname(), $relative)) {
                    throw new RuntimeException('Impossible d ajouter '.$relative.' a l archive.');
                }
            }
        } catch (\Throwable $exception) {
            $zip->close();
            throw $exception;
        }

        if (! $zip->close()) {
            throw new RuntimeException('Impossible de finaliser l archive ZIP.');
        }
    }

    public function purgeExpiredExports(int $days = 7): int
    {
        if (! Storage::disk('local')->exists(self::EXPORT_DIRECTORY)) {
            return 0;
        }

        $cutoff = now()->subDays(max(1, $days))->timestamp;
        $deleted = 0;

        foreach (Storage::disk('local')->files(self::EXPORT_DIRECTORY) as $path) {
            if (str_ends_with($path, '.zip') && Storage::disk('local')->lastModified($path) < $cutoff) {
                if (Storage::disk('local')->delete($path)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
