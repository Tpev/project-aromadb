# AromaMade / Olithea Mobile App Inventory

Generated from the current Laravel codebase on 2026-07-01 06:10:15 UTC.

## Current Surface Counts

| Surface | Count | Source |
| --- | ---: | --- |
| Routes | 741 | `php artisan route:list --json` |
| Controllers | 131 | `app/Http/Controllers` |
| Models | 102 | `app/Models` |
| Blade screens/templates | 482 | `resources/views` |
| Mobile Blade screens | 80 | `resources/views/mobile` |
| Migrations | 228 | `database/migrations` |

## Route Families

| Family | Routes | Primary purpose |
| --- | ---: | --- |
| `admin` | 54 | Internal admin, CRM, finance, therapists, marketing, licenses. |
| `api` | 5 | JSON/API endpoints used by frontend tools and integrations. |
| `client_portal` | 22 | End-client authentication, account, messages, communities, files. |
| `mobile` | 199 | Mobile-specific app shell, PRO dashboard, client portal, therapist search, booking, appointments, clients, invoices. |
| `pro_authenticated` | 181 | Practitioner workspace: dashboard, agenda, clients, billing, products, availability, documents, trainings, communities, marketing. |
| `public_or_marketing` | 280 | Public website, SEO pages, practitioner directory, checkout/booking, blog, legal/help pages. |

## Mobile Scope Decisions

| Module | Routes | Blade screens/templates | Decision | Treatment | Rationale |
| --- | ---: | ---: | --- | --- | --- |
| `pro_authenticated` | 181 | 115 | In Android app scope | Dedicated `/mobile` routes and mobile Blade screens for practitioner workflows. | This is the daily practitioner product: agenda, clients, billing, products, documents, marketing, and settings. |
| `mobile` | 199 | 80 | Canonical Android surface | Packaged by Capacitor and verified at phone viewport. | This route family is the mobile app shell and should stay isolated from desktop routes. |
| `client_portal` | 22 | 7 | In Android app scope as isolated client role | Dedicated `/mobile/client` routes, client-guard auth, and mobile Blade screens for client account workflows. | Client login, messages, documents, invoices, and communities are useful in the app, but must stay separate from practitioner navigation and web routes. |
| `public_or_marketing` | 280 | 152 | Selective app scope | Mobile app covers practitioner search, therapist profile, booking, and appointment confirmation. Marketing/blog/legal pages remain responsive web. | Only transactional public journeys belong inside the app; SEO and content pages should stay web-first. |
| `admin` | 54 | 21 | Out of Android app scope for now | Keep admin web-first unless a specific mobile admin use case is requested. | Admin CRM, finance, imports, design templates, and impersonation are operational desktop workflows with higher risk. |
| `api` | 5 | 0 | Support surface | No Blade mobile screen required; verify only when mobile UI depends on the endpoint. | API routes are consumed by screens/integrations rather than rendered as standalone mobile pages. |

## Screen Families

These counts are based on Blade source folders. Use the feature coverage tables below for the more important product question: which practitioner and client-portal web features have `/mobile` equivalents.

| Family | Screens/templates | Mobile-specific screens | Notes |
| --- | ---: | ---: | --- |
| `admin` | 21 | 0 | Admin surfaces should stay web-first unless a specific mobile admin need is approved. |
| `auth` | 11 | 2 | Shared auth screens; mobile has its own practitioner login. |
| `client_portal` | 7 | 5 | Client portal now has isolated `/mobile/client` screens for auth, dashboard, messages, documents, invoices, and communities. |
| `mobile` | 0 | 80 | Current mobile foundation already exists. |
| `pro_authenticated` | 115 | 0 | Highest-priority product area for the Android app. |
| `public_or_marketing` | 152 | 0 | Public pages can stay responsive web; only booking/search flows need app-grade treatment. |

## Practitioner Mobile Coverage

| Feature | Web routes | Mobile routes | Mobile views | Controller mode | Status |
| --- | ---: | ---: | ---: | --- | --- |
| Dashboard PRO | 19 | 1 | 1 | Shared controller | Covered |
| Rendez-vous | 23 | 12 | 6 | Dedicated + shared | Covered |
| Clients | 2 | 6 | 3 | Dedicated + shared | Covered |
| Factures et devis | 22 | 3 | 2 | Dedicated + shared | Covered |
| Prestations | 9 | 7 | 3 | Dedicated mobile | Covered |
| Disponibilites | 12 | 6 | 2 | Shared controller | Covered |
| Lieux de pratique | 13 | 6 | 2 | Shared controller | Covered |
| Questionnaires | 13 | 8 | 3 | Dedicated mobile | Covered |
| Evenements | 15 | 8 | 3 | Dedicated mobile | Covered |
| Documents et signatures | 6 | 10 | 2 | Dedicated mobile | Covered |
| Emargements | 5 | 4 | 1 | Dedicated mobile | Covered |
| Recettes | 6 | 5 | 3 | Dedicated mobile | Covered |
| Stock | 1 | 8 | 2 | Shared controller | Covered |
| Entreprises | 7 | 7 | 3 | Dedicated mobile | Covered |
| Packs | 10 | 9 | 3 | Dedicated mobile | Covered |
| Bons cadeaux | 13 | 8 | 3 | Dedicated mobile | Covered |
| Factures recues | 0 | 3 | 2 | Dedicated mobile | Covered |
| Formations digitales | 30 | 7 | 3 | Dedicated mobile | Covered |
| Communautes | 15 | 11 | 3 | Dedicated + shared | Covered |
| Audiences | 7 | 7 | 3 | Dedicated mobile | Covered |
| Newsletters | 12 | 9 | 3 | Dedicated mobile | Covered |
| Avis Google | 5 | 4 | 1 | Dedicated mobile | Covered |
| Parrainage | 4 | 3 | 1 | Dedicated mobile | Covered |
| Profil praticien | 6 | 3 | 2 | Dedicated mobile | Covered |
| Abonnement | 0 | 1 | 1 | Dedicated mobile | Covered |
| Statistiques | 14 | 12 | 4 | Dedicated mobile | Covered |
| Notes de seance | 7 | 7 | 3 | Dedicated mobile | Covered |

## Client Portal Mobile Coverage

| Feature | Web routes | Mobile routes | Mobile views | Controller mode | Status |
| --- | ---: | ---: | ---: | --- | --- |
| Connexion et accueil client | 3 | 3 | 2 | Dedicated mobile | Covered |
| Messagerie client | 3 | 2 | 1 | Dedicated mobile | Covered |
| Documents client | 3 | 2 | 1 | Dedicated + shared | Covered |
| Factures client | 1 | 1 | 1 | Shared controller | Covered |
| Communautes client | 5 | 5 | 2 | Dedicated + shared | Covered |

## Actionable Mobile Gaps

- No catalogued practitioner or client-portal feature is missing both mobile routes and mobile views.

## Shared Mobile Controller Routes

These `/mobile` routes intentionally reuse non-mobile controllers. They need controller-level mobile view branching tests whenever touched.

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| `GET|HEAD` | `mobile/client/communautes/fichiers/{attachment}` | `mobile.client.communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForClient` |
| `GET|HEAD` | `mobile/client/documents/{file}/download` | `mobile.client.files.download` | `App\Http\Controllers\ClientFileController@downloadClient` |
| `GET|HEAD` | `mobile/client/factures/{invoice}/pdf` | `mobile.client.invoices.pdf` | `App\Http\Controllers\InvoiceController@clientPdf` |
| `GET|HEAD` | `mobile/clients` | `mobile.clients.index` | `App\Http\Controllers\ClientProfileController@index` |
| `GET|HEAD` | `mobile/clients/{clientProfile}` | `mobile.clients.show` | `App\Http\Controllers\ClientProfileController@show` |
| `GET|HEAD` | `mobile/communautes/fichiers/{attachment}` | `mobile.communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForPractitioner` |
| `GET|HEAD` | `mobile/dashboard` | `mobile.dashboard` | `App\Http\Controllers\DashboardController@index` |
| `GET|HEAD` | `mobile/disponibilites` | `mobile.availabilities.index` | `App\Http\Controllers\AvailabilityController@index` |
| `POST` | `mobile/disponibilites` | `mobile.availabilities.store` | `App\Http\Controllers\AvailabilityController@store` |
| `GET|HEAD` | `mobile/disponibilites/create` | `mobile.availabilities.create` | `App\Http\Controllers\AvailabilityController@create` |
| `PUT` | `mobile/disponibilites/{availability}` | `mobile.availabilities.update` | `App\Http\Controllers\AvailabilityController@update` |
| `DELETE` | `mobile/disponibilites/{availability}` | `mobile.availabilities.destroy` | `App\Http\Controllers\AvailabilityController@destroy` |
| `GET|HEAD` | `mobile/disponibilites/{availability}/edit` | `mobile.availabilities.edit` | `App\Http\Controllers\AvailabilityController@edit` |
| `GET|HEAD` | `mobile/invoices` | `mobile.invoices.index` | `App\Http\Controllers\InvoiceController@index` |
| `GET|HEAD` | `mobile/lieux` | `mobile.practice-locations.index` | `App\Http\Controllers\PracticeLocationController@index` |
| `POST` | `mobile/lieux` | `mobile.practice-locations.store` | `App\Http\Controllers\PracticeLocationController@store` |
| `GET|HEAD` | `mobile/lieux/create` | `mobile.practice-locations.create` | `App\Http\Controllers\PracticeLocationController@create` |
| `PUT` | `mobile/lieux/{practice_location}` | `mobile.practice-locations.update` | `App\Http\Controllers\PracticeLocationController@update` |
| `DELETE` | `mobile/lieux/{practice_location}` | `mobile.practice-locations.destroy` | `App\Http\Controllers\PracticeLocationController@destroy` |
| `GET|HEAD` | `mobile/lieux/{practice_location}/edit` | `mobile.practice-locations.edit` | `App\Http\Controllers\PracticeLocationController@edit` |
| `GET|HEAD` | `mobile/login` | `mobile.login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@createMobile` |
| `POST` | `mobile/login` | `mobile.login.store` | `App\Http\Controllers\Auth\AuthenticatedSessionController@storeMobile` |
| `POST` | `mobile/logout` | `mobile.logout` | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` |
| `GET|HEAD` | `mobile/rendez-vous` | `mobile.appointments.index` | `App\Http\Controllers\AppointmentController@index` |
| `POST` | `mobile/rendez-vous` | `mobile.appointments.store_practitioner` | `App\Http\Controllers\AppointmentController@store` |
| `GET|HEAD` | `mobile/rendez-vous/create` | `mobile.appointments.create` | `App\Http\Controllers\AppointmentController@create` |
| `PUT` | `mobile/rendez-vous/{appointment}` | `mobile.appointments.update` | `App\Http\Controllers\AppointmentController@update` |
| `GET|HEAD` | `mobile/rendez-vous/{appointment}` | `mobile.appointments.show` | `App\Http\Controllers\AppointmentController@show` |
| `GET|HEAD` | `mobile/rendez-vous/{appointment}/edit` | `mobile.appointments.edit` | `App\Http\Controllers\AppointmentController@edit` |
| `GET|HEAD` | `mobile/stock` | `mobile.inventory.index` | `App\Http\Controllers\InventoryItemController@index` |
| `POST` | `mobile/stock` | `mobile.inventory.store` | `App\Http\Controllers\InventoryItemController@store` |
| `GET|HEAD` | `mobile/stock/create` | `mobile.inventory.create` | `App\Http\Controllers\InventoryItemController@create` |
| `DELETE` | `mobile/stock/{id}` | `mobile.inventory.destroy` | `App\Http\Controllers\InventoryItemController@destroy` |
| `PUT` | `mobile/stock/{inventoryItem}` | `mobile.inventory.update` | `App\Http\Controllers\InventoryItemController@update` |
| `POST` | `mobile/stock/{inventoryItem}/consume` | `mobile.inventory.consume` | `App\Http\Controllers\InventoryItemController@consume` |
| `POST` | `mobile/stock/{inventoryItem}/consume-unit` | `mobile.inventory.consume.unit` | `App\Http\Controllers\InventoryItemController@consumeUnit` |
| `GET|HEAD` | `mobile/stock/{inventoryItem}/edit` | `mobile.inventory.edit` | `App\Http\Controllers\InventoryItemController@edit` |

## Mobile Controller Usage

| Controller | Mobile route count | Status | File |
| --- | ---: | --- | --- |
| `MobileAppointmentController` | 6 | Used by routes | `app/Http/Controllers/Mobile/MobileAppointmentController.php` |
| `MobileAudienceController` | 7 | Used by routes | `app/Http/Controllers/Mobile/MobileAudienceController.php` |
| `MobileClientController` | 4 | Used by routes | `app/Http/Controllers/Mobile/MobileClientController.php` |
| `MobileClientPortalController` | 11 | Used by routes | `app/Http/Controllers/Mobile/MobileClientPortalController.php` |
| `MobileCommunityController` | 10 | Used by routes | `app/Http/Controllers/Mobile/MobileCommunityController.php` |
| `MobileCorporateClientController` | 7 | Used by routes | `app/Http/Controllers/Mobile/MobileCorporateClientController.php` |
| `MobileDigitalTrainingController` | 7 | Used by routes | `app/Http/Controllers/Mobile/MobileDigitalTrainingController.php` |
| `MobileDocumentController` | 10 | Used by routes | `app/Http/Controllers/Mobile/MobileDocumentController.php` |
| `MobileEmargementController` | 4 | Used by routes | `app/Http/Controllers/Mobile/MobileEmargementController.php` |
| `MobileEventController` | 8 | Used by routes | `app/Http/Controllers/Mobile/MobileEventController.php` |
| `MobileGiftVoucherController` | 8 | Used by routes | `app/Http/Controllers/Mobile/MobileGiftVoucherController.php` |
| `MobileGoogleReviewController` | 4 | Used by routes | `app/Http/Controllers/Mobile/MobileGoogleReviewController.php` |
| `MobileInvoiceController` | 2 | Used by routes | `app/Http/Controllers/Mobile/MobileInvoiceController.php` |
| `MobileMetricController` | 12 | Used by routes | `app/Http/Controllers/Mobile/MobileMetricController.php` |
| `MobileNewsletterController` | 9 | Used by routes | `app/Http/Controllers/Mobile/MobileNewsletterController.php` |
| `MobilePackProductController` | 9 | Used by routes | `app/Http/Controllers/Mobile/MobilePackProductController.php` |
| `MobileProductController` | 7 | Used by routes | `app/Http/Controllers/Mobile/MobileProductController.php` |
| `MobileProfileController` | 3 | Used by routes | `app/Http/Controllers/Mobile/MobileProfileController.php` |
| `MobileQuestionnaireController` | 8 | Used by routes | `app/Http/Controllers/Mobile/MobileQuestionnaireController.php` |
| `MobileReceiptController` | 5 | Used by routes | `app/Http/Controllers/Mobile/MobileReceiptController.php` |
| `MobileReceivedInvoiceController` | 3 | Used by routes | `app/Http/Controllers/Mobile/MobileReceivedInvoiceController.php` |
| `MobileReferralController` | 3 | Used by routes | `app/Http/Controllers/Mobile/MobileReferralController.php` |
| `MobileSessionNoteController` | 7 | Used by routes | `app/Http/Controllers/Mobile/MobileSessionNoteController.php` |
| `MobileSubscriptionController` | 1 | Used by routes | `app/Http/Controllers/Mobile/MobileSubscriptionController.php` |
| `MobileTherapistController` | 2 | Used by routes | `app/Http/Controllers/Mobile/MobileTherapistController.php` |
| `MobileWorkspaceController` | 0 | No route usage detected | `app/Http/Controllers/Mobile/MobileWorkspaceController.php` |
| `TherapistSearchController` | 2 | Used by routes | `app/Http/Controllers/Mobile/TherapistSearchController.php` |

## Existing Mobile Screens

- `mobile/appointments/create.blade.php`
- `mobile/appointments/form.blade.php`
- `mobile/appointments/index.blade.php`
- `mobile/appointments/partials/card.blade.php`
- `mobile/appointments/show.blade.php`
- `mobile/appointments/show1.blade.php`
- `mobile/audiences/form.blade.php`
- `mobile/audiences/index.blade.php`
- `mobile/audiences/show.blade.php`
- `mobile/auth/login.blade.php`
- `mobile/availabilities/form.blade.php`
- `mobile/availabilities/index.blade.php`
- `mobile/client/auth/login.blade.php`
- `mobile/client/communities/index.blade.php`
- `mobile/client/communities/show.blade.php`
- `mobile/client/home.blade.php`
- `mobile/client/messages/index.blade.php`
- `mobile/clients/form.blade.php`
- `mobile/clients/index.blade.php`
- `mobile/clients/show.blade.php`
- `mobile/communities/form.blade.php`
- `mobile/communities/index.blade.php`
- `mobile/communities/show.blade.php`
- `mobile/corporate-clients/form.blade.php`
- `mobile/corporate-clients/index.blade.php`
- `mobile/corporate-clients/show.blade.php`
- `mobile/dashboard-pro.blade.php`
- `mobile/digital-trainings/form.blade.php`
- `mobile/digital-trainings/index.blade.php`
- `mobile/digital-trainings/show.blade.php`
- `mobile/documents/index.blade.php`
- `mobile/documents/show.blade.php`
- `mobile/emargements/index.blade.php`
- `mobile/entry.blade.php`
- `mobile/events/form.blade.php`
- `mobile/events/index.blade.php`
- `mobile/events/show.blade.php`
- `mobile/gift-vouchers/form.blade.php`
- `mobile/gift-vouchers/index.blade.php`
- `mobile/gift-vouchers/show.blade.php`
- `mobile/google-reviews/index.blade.php`
- `mobile/inventory/form.blade.php`
- `mobile/inventory/index.blade.php`
- `mobile/invoices/index.blade.php`
- `mobile/invoices/show.blade.php`
- `mobile/menu.blade.php`
- `mobile/metrics/entry-form.blade.php`
- `mobile/metrics/form.blade.php`
- `mobile/metrics/index.blade.php`
- `mobile/metrics/show.blade.php`
- `mobile/modules/index.blade.php`
- `mobile/newsletters/form.blade.php`
- `mobile/newsletters/index.blade.php`
- `mobile/newsletters/show.blade.php`
- `mobile/packs/form.blade.php`
- `mobile/packs/index.blade.php`
- `mobile/packs/show.blade.php`
- `mobile/practice-locations/form.blade.php`
- `mobile/practice-locations/index.blade.php`
- `mobile/products/form.blade.php`
- `mobile/products/index.blade.php`
- `mobile/products/show.blade.php`
- `mobile/profile/form.blade.php`
- `mobile/profile/index.blade.php`
- `mobile/questionnaires/form.blade.php`
- `mobile/questionnaires/index.blade.php`
- `mobile/questionnaires/show.blade.php`
- `mobile/receipts/form.blade.php`
- `mobile/receipts/index.blade.php`
- `mobile/receipts/monthly.blade.php`
- `mobile/received-invoices/index.blade.php`
- `mobile/received-invoices/show.blade.php`
- `mobile/referrals/index.blade.php`
- `mobile/session-notes/form.blade.php`
- `mobile/session-notes/index.blade.php`
- `mobile/session-notes/show.blade.php`
- `mobile/subscription/index.blade.php`
- `mobile/therapists/index.blade.php`
- `mobile/therapists/results.blade.php`
- `mobile/therapists/show.blade.php`

## Blade Screen Inventory

| Family | Blade screen/template |
| --- | --- |
| `admin` | `admin/crm/index.blade.php` |
| `admin` | `admin/crm/show.blade.php` |
| `admin` | `admin/design-templates/index.blade.php` |
| `admin` | `admin/finance/customers.blade.php` |
| `admin` | `admin/finance/failures.blade.php` |
| `admin` | `admin/finance/forecast.blade.php` |
| `admin` | `admin/finance/layout.blade.php` |
| `admin` | `admin/finance/overview.blade.php` |
| `admin` | `admin/finance/payouts.blade.php` |
| `admin` | `admin/finance/show.blade.php` |
| `admin` | `admin/index.blade.php` |
| `admin` | `admin/lesson/edit.blade.php` |
| `admin` | `admin/licenses/index.blade.php` |
| `admin` | `admin/marketing/emails.blade.php` |
| `admin` | `admin/marketing/templates/edit.blade.php` |
| `admin` | `admin/marketing/templates/index.blade.php` |
| `admin` | `admin/marketing/upload-csv.blade.php` |
| `admin` | `admin/therapists/index.blade.php` |
| `admin` | `admin/therapists/show.blade.php` |
| `admin` | `admin/usage/weekly.blade.php` |
| `admin` | `admin/welcome.blade.php` |
| `public_or_marketing` | `aide/agenda/configurer-disponibilites.blade.php` |
| `public_or_marketing` | `aide/agenda/creer-un-atelier-ou-evenement.blade.php` |
| `public_or_marketing` | `aide/agenda/creer-un-rendez-vous-en-ligne.blade.php` |
| `public_or_marketing` | `aide/agenda/duree-prestation-temps-de-pause.blade.php` |
| `public_or_marketing` | `aide/agenda/gerer-indisponibilites.blade.php` |
| `public_or_marketing` | `aide/agenda/synchroniser-calendrier.blade.php` |
| `pro_authenticated` | `appointments/create.blade.php` |
| `pro_authenticated` | `appointments/createPatient.blade.php` |
| `pro_authenticated` | `appointments/create_patient_partner.blade.php` |
| `pro_authenticated` | `appointments/edit.blade.php` |
| `pro_authenticated` | `appointments/index.blade.php` |
| `pro_authenticated` | `appointments/show.blade.php` |
| `pro_authenticated` | `appointments/show_patient.blade.php` |
| `public_or_marketing` | `assistant/chat.blade.php` |
| `pro_authenticated` | `audiences/_form.blade.php` |
| `pro_authenticated` | `audiences/create.blade.php` |
| `pro_authenticated` | `audiences/edit.blade.php` |
| `pro_authenticated` | `audiences/index.blade.php` |
| `auth` | `auth/client-passwords/email.blade.php` |
| `auth` | `auth/client-passwords/reset.blade.php` |
| `auth` | `auth/confirm-password.blade.php` |
| `auth` | `auth/forgot-password.blade.php` |
| `auth` | `auth/login-choice.blade.php` |
| `auth` | `auth/login.blade.php` |
| `auth` | `auth/register-formation.blade.php` |
| `auth` | `auth/register-pro.blade.php` |
| `auth` | `auth/register.blade.php` |
| `auth` | `auth/reset-password.blade.php` |
| `auth` | `auth/verify-email.blade.php` |
| `pro_authenticated` | `availabilities/create.blade.php` |
| `pro_authenticated` | `availabilities/edit.blade.php` |
| `pro_authenticated` | `availabilities/index.blade.php` |
| `public_or_marketing` | `blog/index.blade.php` |
| `public_or_marketing` | `blog/show.blade.php` |
| `public_or_marketing` | `cgu.blade.php` |
| `public_or_marketing` | `cgv.blade.php` |
| `client_portal` | `client/communities/index.blade.php` |
| `client_portal` | `client/communities/show.blade.php` |
| `client_portal` | `client/forgot-password.blade.php` |
| `client_portal` | `client/home.blade.php` |
| `client_portal` | `client/login.blade.php` |
| `client_portal` | `client/reset-password.blade.php` |
| `client_portal` | `client/setup-password.blade.php` |
| `pro_authenticated` | `client_profiles/create.blade.php` |
| `pro_authenticated` | `client_profiles/edit.blade.php` |
| `pro_authenticated` | `client_profiles/index.blade.php` |
| `pro_authenticated` | `client_profiles/send_conseil.blade.php` |
| `pro_authenticated` | `client_profiles/show.blade.php` |
| `pro_authenticated` | `communities/create.blade.php` |
| `pro_authenticated` | `communities/edit.blade.php` |
| `pro_authenticated` | `communities/index.blade.php` |
| `pro_authenticated` | `communities/manage.blade.php` |
| `pro_authenticated` | `communities/partials/attachment-list.blade.php` |
| `pro_authenticated` | `communities/partials/message-card.blade.php` |
| `pro_authenticated` | `communities/show.blade.php` |
| `shared_ui` | `components/application-logo.blade.php` |
| `shared_ui` | `components/auth-session-status.blade.php` |
| `shared_ui` | `components/client-app-layout.blade.php` |
| `shared_ui` | `components/danger-button.blade.php` |
| `shared_ui` | `components/dropdown-link.blade.php` |
| `shared_ui` | `components/dropdown.blade.php` |
| `shared_ui` | `components/input-error.blade.php` |
| `shared_ui` | `components/input-label.blade.php` |
| `shared_ui` | `components/mobile-client-layout.blade.php` |
| `shared_ui` | `components/mobile-layout.blade.php` |
| `shared_ui` | `components/modal.blade.php` |
| `shared_ui` | `components/nav-link.blade.php` |
| `shared_ui` | `components/password-toggle-input.blade.php` |
| `shared_ui` | `components/primary-button.blade.php` |
| `shared_ui` | `components/responsive-nav-link.blade.php` |
| `shared_ui` | `components/secondary-button.blade.php` |
| `shared_ui` | `components/text-input.blade.php` |
| `public_or_marketing` | `conseils/create.blade.php` |
| `public_or_marketing` | `conseils/edit.blade.php` |
| `public_or_marketing` | `conseils/index.blade.php` |
| `public_or_marketing` | `conseils/show.blade.php` |
| `public_or_marketing` | `contact-confirmation.blade.php` |
| `public_or_marketing` | `contact.blade.php` |
| `public_or_marketing` | `corporate_clients/_form.blade.php` |
| `public_or_marketing` | `corporate_clients/create.blade.php` |
| `public_or_marketing` | `corporate_clients/edit.blade.php` |
| `public_or_marketing` | `corporate_clients/index.blade.php` |
| `public_or_marketing` | `corporate_clients/show.blade.php` |
| `public_or_marketing` | `dashboard-pro.blade.php` |
| `pro_authenticated` | `dashboard-pro/articles/create.blade.php` |
| `pro_authenticated` | `dashboard-pro/articles/edit.blade.php` |
| `pro_authenticated` | `dashboard-pro/articles/index.blade.php` |
| `pro_authenticated` | `dashboard-pro/articles/show.blade.php` |
| `pro_authenticated` | `dashboard-pro/gift-vouchers/create.blade.php` |
| `pro_authenticated` | `dashboard-pro/gift-vouchers/index.blade.php` |
| `pro_authenticated` | `dashboard-pro/gift-vouchers/show.blade.php` |
| `public_or_marketing` | `dashboard.blade.php` |
| `pro_authenticated` | `digital-trainings/access-invalid.blade.php` |
| `pro_authenticated` | `digital-trainings/builder.blade.php` |
| `pro_authenticated` | `digital-trainings/comments/index.blade.php` |
| `pro_authenticated` | `digital-trainings/create.blade.php` |
| `pro_authenticated` | `digital-trainings/edit.blade.php` |
| `pro_authenticated` | `digital-trainings/enrollments/index.blade.php` |
| `pro_authenticated` | `digital-trainings/index.blade.php` |
| `pro_authenticated` | `digital-trainings/player.blade.php` |
| `pro_authenticated` | `digital-trainings/preview.blade.php` |
| `pro_authenticated` | `digital-trainings/public/show.blade.php` |
| `pro_authenticated` | `documents/pdf_annex.blade.php` |
| `pro_authenticated` | `documents/sign/expired.blade.php` |
| `pro_authenticated` | `documents/sign/form.blade.php` |
| `pro_authenticated` | `documents/sign/thanks.blade.php` |
| `system_templates` | `emails/admin_new_user_notification.blade.php` |
| `system_templates` | `emails/appointment_created_patient.blade.php` |
| `system_templates` | `emails/appointment_created_therapist.blade.php` |
| `system_templates` | `emails/appointment_edited.blade.php` |
| `system_templates` | `emails/appointment_reminder.blade.php` |
| `system_templates` | `emails/appointments/cancelled-by-client.blade.php` |
| `system_templates` | `emails/client_file_uploaded_therapist.blade.php` |
| `system_templates` | `emails/client_message_received_therapist.blade.php` |
| `system_templates` | `emails/client_set_password.blade.php` |
| `system_templates` | `emails/client_set_password_plain.blade.php` |
| `system_templates` | `emails/communities/invite.blade.php` |
| `system_templates` | `emails/conseil_sent_markdown.blade.php` |
| `system_templates` | `emails/contact.blade.php` |
| `system_templates` | `emails/daily_kpi.blade.php` |
| `system_templates` | `emails/digital-trainings/access.blade.php` |
| `system_templates` | `emails/documents/sign-request.blade.php` |
| `system_templates` | `emails/documents/signature_link.blade.php` |
| `system_templates` | `emails/documents/signed-final.blade.php` |
| `system_templates` | `emails/emargement/request.blade.php` |
| `system_templates` | `emails/event_reminder.blade.php` |
| `system_templates` | `emails/formation_completed.blade.php` |
| `system_templates` | `emails/formation_started.blade.php` |
| `system_templates` | `emails/gift-voucher/buyer.blade.php` |
| `system_templates` | `emails/gift-voucher/recipient.blade.php` |
| `system_templates` | `emails/invoices/mail.blade.php` |
| `system_templates` | `emails/invoices/payment_link.blade.php` |
| `system_templates` | `emails/invoices/payment_reminder.blade.php` |
| `system_templates` | `emails/meeting_invitation.blade.php` |
| `system_templates` | `emails/milestone_reached.blade.php` |
| `system_templates` | `emails/new_reservation_notification.blade.php` |
| `system_templates` | `emails/newsletter.blade.php` |
| `system_templates` | `emails/practice_locations/invite.blade.php` |
| `system_templates` | `emails/questionnaire_completed.blade.php` |
| `system_templates` | `emails/questionnaire_sent.blade.php` |
| `system_templates` | `emails/quotes/mail.blade.php` |
| `system_templates` | `emails/referrals/invite.blade.php` |
| `system_templates` | `emails/reservation_confirmation.blade.php` |
| `system_templates` | `emails/testimonial_request.blade.php` |
| `system_templates` | `emails/therapist-request.blade.php` |
| `system_templates` | `emails/therapist_file_uploaded_to_client.blade.php` |
| `system_templates` | `emails/therapist_message_sent_to_client.blade.php` |
| `system_templates` | `emails/welcome_pro.blade.php` |
| `pro_authenticated` | `emargement/already-signed.blade.php` |
| `pro_authenticated` | `emargement/expired.blade.php` |
| `pro_authenticated` | `emargement/pdf.blade.php` |
| `pro_authenticated` | `emargement/sign.blade.php` |
| `pro_authenticated` | `emargement/thanks.blade.php` |
| `pro_authenticated` | `events/create.blade.php` |
| `pro_authenticated` | `events/duplicate.blade.php` |
| `pro_authenticated` | `events/edit.blade.php` |
| `pro_authenticated` | `events/index.blade.php` |
| `pro_authenticated` | `events/public-show.blade.php` |
| `pro_authenticated` | `events/show.blade.php` |
| `public_or_marketing` | `facturationtherapeute.blade.php` |
| `public_or_marketing` | `fonctionnalites/agenda.blade.php` |
| `public_or_marketing` | `fonctionnalites/dossiers-clients.blade.php` |
| `public_or_marketing` | `fonctionnalites/facturation.blade.php` |
| `public_or_marketing` | `fonctionnalites/index.blade.php` |
| `public_or_marketing` | `fonctionnalites/paiements.blade.php` |
| `public_or_marketing` | `fonctionnalites/portail-pro.blade.php` |
| `public_or_marketing` | `fonctionnalites/questionnaires.blade.php` |
| `public_or_marketing` | `formation1.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie1.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie10.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie11.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie12.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie13.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie14.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie15.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie16.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie17.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie18.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie19.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie2.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie20.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie21.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie22.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie23.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie24.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie25.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie26.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie27.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie28.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie29.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie3.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie30.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie31.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie32.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie33.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie34.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie35.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie36.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie37.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie38.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie39.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie4.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie40.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie41.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie42.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie43.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie44.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie45.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie46.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie47.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie48.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie49.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie5.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie6.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie7.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie8.blade.php` |
| `public_or_marketing` | `formation/Utilisateur-Aromatherapie9.blade.php` |
| `pro_authenticated` | `gift-vouchers/checkout.blade.php` |
| `public_or_marketing` | `huilehe/index.blade.php` |
| `public_or_marketing` | `huilehe/show.blade.php` |
| `public_or_marketing` | `huilehe/showhuilehepropriete.blade.php` |
| `public_or_marketing` | `huilehvs/index.blade.php` |
| `public_or_marketing` | `huilehvs/show.blade.php` |
| `pro_authenticated` | `inventory_items/create.blade.php` |
| `pro_authenticated` | `inventory_items/edit.blade.php` |
| `pro_authenticated` | `inventory_items/index.blade.php` |
| `pro_authenticated` | `invoices/create-quote.blade.php` |
| `pro_authenticated` | `invoices/create.blade.php` |
| `pro_authenticated` | `invoices/edit-quote.blade.php` |
| `pro_authenticated` | `invoices/edit.blade.php` |
| `pro_authenticated` | `invoices/index.blade.php` |
| `pro_authenticated` | `invoices/pdf.blade.php` |
| `pro_authenticated` | `invoices/pdf_quote.blade.php` |
| `pro_authenticated` | `invoices/show-quote.blade.php` |
| `pro_authenticated` | `invoices/show.blade.php` |
| `shared_ui` | `layouts/app.blade.php` |
| `shared_ui` | `layouts/blank.blade.php` |
| `shared_ui` | `layouts/footer.blade.php` |
| `shared_ui` | `layouts/guest.blade.php` |
| `shared_ui` | `layouts/navigation.blade.php` |
| `shared_ui` | `layouts/therapistnavigation.blade.php` |
| `public_or_marketing` | `license-tiers/pricing.blade.php` |
| `public_or_marketing` | `meetings/confirmation.blade.php` |
| `public_or_marketing` | `meetings/create.blade.php` |
| `public_or_marketing` | `metiers/naturopathe.blade.php` |
| `public_or_marketing` | `metiers/sophrologue.blade.php` |
| `public_or_marketing` | `metric_entries/create.blade.php` |
| `public_or_marketing` | `metric_entries/edit.blade.php` |
| `pro_authenticated` | `metrics/create.blade.php` |
| `pro_authenticated` | `metrics/edit.blade.php` |
| `pro_authenticated` | `metrics/index.blade.php` |
| `pro_authenticated` | `metrics/show.blade.php` |
| `mobile` | `mobile/appointments/create.blade.php` |
| `mobile` | `mobile/appointments/form.blade.php` |
| `mobile` | `mobile/appointments/index.blade.php` |
| `mobile` | `mobile/appointments/partials/card.blade.php` |
| `mobile` | `mobile/appointments/show.blade.php` |
| `mobile` | `mobile/appointments/show1.blade.php` |
| `mobile` | `mobile/audiences/form.blade.php` |
| `mobile` | `mobile/audiences/index.blade.php` |
| `mobile` | `mobile/audiences/show.blade.php` |
| `mobile` | `mobile/auth/login.blade.php` |
| `mobile` | `mobile/availabilities/form.blade.php` |
| `mobile` | `mobile/availabilities/index.blade.php` |
| `mobile` | `mobile/client/auth/login.blade.php` |
| `mobile` | `mobile/client/communities/index.blade.php` |
| `mobile` | `mobile/client/communities/show.blade.php` |
| `mobile` | `mobile/client/home.blade.php` |
| `mobile` | `mobile/client/messages/index.blade.php` |
| `mobile` | `mobile/clients/form.blade.php` |
| `mobile` | `mobile/clients/index.blade.php` |
| `mobile` | `mobile/clients/show.blade.php` |
| `mobile` | `mobile/communities/form.blade.php` |
| `mobile` | `mobile/communities/index.blade.php` |
| `mobile` | `mobile/communities/show.blade.php` |
| `mobile` | `mobile/corporate-clients/form.blade.php` |
| `mobile` | `mobile/corporate-clients/index.blade.php` |
| `mobile` | `mobile/corporate-clients/show.blade.php` |
| `mobile` | `mobile/dashboard-pro.blade.php` |
| `mobile` | `mobile/digital-trainings/form.blade.php` |
| `mobile` | `mobile/digital-trainings/index.blade.php` |
| `mobile` | `mobile/digital-trainings/show.blade.php` |
| `mobile` | `mobile/documents/index.blade.php` |
| `mobile` | `mobile/documents/show.blade.php` |
| `mobile` | `mobile/emargements/index.blade.php` |
| `mobile` | `mobile/entry.blade.php` |
| `mobile` | `mobile/events/form.blade.php` |
| `mobile` | `mobile/events/index.blade.php` |
| `mobile` | `mobile/events/show.blade.php` |
| `mobile` | `mobile/gift-vouchers/form.blade.php` |
| `mobile` | `mobile/gift-vouchers/index.blade.php` |
| `mobile` | `mobile/gift-vouchers/show.blade.php` |
| `mobile` | `mobile/google-reviews/index.blade.php` |
| `mobile` | `mobile/inventory/form.blade.php` |
| `mobile` | `mobile/inventory/index.blade.php` |
| `mobile` | `mobile/invoices/index.blade.php` |
| `mobile` | `mobile/invoices/show.blade.php` |
| `mobile` | `mobile/menu.blade.php` |
| `mobile` | `mobile/metrics/entry-form.blade.php` |
| `mobile` | `mobile/metrics/form.blade.php` |
| `mobile` | `mobile/metrics/index.blade.php` |
| `mobile` | `mobile/metrics/show.blade.php` |
| `mobile` | `mobile/modules/index.blade.php` |
| `mobile` | `mobile/newsletters/form.blade.php` |
| `mobile` | `mobile/newsletters/index.blade.php` |
| `mobile` | `mobile/newsletters/show.blade.php` |
| `mobile` | `mobile/packs/form.blade.php` |
| `mobile` | `mobile/packs/index.blade.php` |
| `mobile` | `mobile/packs/show.blade.php` |
| `mobile` | `mobile/practice-locations/form.blade.php` |
| `mobile` | `mobile/practice-locations/index.blade.php` |
| `mobile` | `mobile/products/form.blade.php` |
| `mobile` | `mobile/products/index.blade.php` |
| `mobile` | `mobile/products/show.blade.php` |
| `mobile` | `mobile/profile/form.blade.php` |
| `mobile` | `mobile/profile/index.blade.php` |
| `mobile` | `mobile/questionnaires/form.blade.php` |
| `mobile` | `mobile/questionnaires/index.blade.php` |
| `mobile` | `mobile/questionnaires/show.blade.php` |
| `mobile` | `mobile/receipts/form.blade.php` |
| `mobile` | `mobile/receipts/index.blade.php` |
| `mobile` | `mobile/receipts/monthly.blade.php` |
| `mobile` | `mobile/received-invoices/index.blade.php` |
| `mobile` | `mobile/received-invoices/show.blade.php` |
| `mobile` | `mobile/referrals/index.blade.php` |
| `mobile` | `mobile/session-notes/form.blade.php` |
| `mobile` | `mobile/session-notes/index.blade.php` |
| `mobile` | `mobile/session-notes/show.blade.php` |
| `mobile` | `mobile/subscription/index.blade.php` |
| `mobile` | `mobile/therapists/index.blade.php` |
| `mobile` | `mobile/therapists/results.blade.php` |
| `mobile` | `mobile/therapists/show.blade.php` |
| `pro_authenticated` | `newsletters/_form.blade.php` |
| `pro_authenticated` | `newsletters/create.blade.php` |
| `pro_authenticated` | `newsletters/edit.blade.php` |
| `pro_authenticated` | `newsletters/index.blade.php` |
| `pro_authenticated` | `newsletters/show.blade.php` |
| `pro_authenticated` | `newsletters/unsubscribe_already.blade.php` |
| `pro_authenticated` | `newsletters/unsubscribe_confirm.blade.php` |
| `pro_authenticated` | `newsletters/unsubscribe_invalid.blade.php` |
| `public_or_marketing` | `nos-practiciens.blade.php` |
| `public_or_marketing` | `notifications/index.blade.php` |
| `public_or_marketing` | `onboarding.blade.php` |
| `pro_authenticated` | `pack_products/create.blade.php` |
| `pro_authenticated` | `pack_products/edit.blade.php` |
| `pro_authenticated` | `pack_products/index.blade.php` |
| `pro_authenticated` | `pack_products/show.blade.php` |
| `public_or_marketing` | `packs/checkout.blade.php` |
| `shared_ui` | `partials/app-tabbar.blade.php` |
| `shared_ui` | `partials/e-invoicing-banner.blade.php` |
| `shared_ui` | `partials/onboarding/step1-big.blade.php` |
| `shared_ui` | `partials/onboarding/step2-big.blade.php` |
| `shared_ui` | `partials/onboarding/step3-big.blade.php` |
| `shared_ui` | `partials/onboarding/step4-big.blade.php` |
| `public_or_marketing` | `pdf/emargement-proof.blade.php` |
| `public_or_marketing` | `pdf/gift-voucher.blade.php` |
| `pro_authenticated` | `practice_locations/_form.blade.php` |
| `pro_authenticated` | `practice_locations/create.blade.php` |
| `pro_authenticated` | `practice_locations/edit.blade.php` |
| `pro_authenticated` | `practice_locations/index.blade.php` |
| `pro_authenticated` | `practice_locations/invites/show.blade.php` |
| `public_or_marketing` | `privacypolicy.blade.php` |
| `public_or_marketing` | `pro-training.blade.php` |
| `pro_authenticated` | `pro/articles/index.blade.php` |
| `pro_authenticated` | `pro/articles/show.blade.php` |
| `pro_authenticated` | `pro/google-reviews.blade.php` |
| `pro_authenticated` | `pro/referrals/index.blade.php` |
| `pro_authenticated` | `products/create.blade.php` |
| `pro_authenticated` | `products/duplicate.blade.php` |
| `pro_authenticated` | `products/edit.blade.php` |
| `pro_authenticated` | `products/index.blade.php` |
| `pro_authenticated` | `products/show.blade.php` |
| `public_or_marketing` | `profile/edit-company-info.blade.php` |
| `public_or_marketing` | `profile/edit.blade.php` |
| `public_or_marketing` | `profile/license.blade.php` |
| `public_or_marketing` | `profile/partials/delete-user-form.blade.php` |
| `public_or_marketing` | `profile/partials/super-pdp-card.blade.php` |
| `public_or_marketing` | `profile/partials/update-password-form.blade.php` |
| `public_or_marketing` | `profile/partials/update-profile-information-form.blade.php` |
| `public_or_marketing` | `prolanding.blade.php` |
| `public_or_marketing` | `public/therapist/show.blade.php` |
| `public_or_marketing` | `public_conseil.blade.php` |
| `pro_authenticated` | `questionnaires/create.blade.php` |
| `pro_authenticated` | `questionnaires/edit.blade.php` |
| `pro_authenticated` | `questionnaires/fill.blade.php` |
| `pro_authenticated` | `questionnaires/index.blade.php` |
| `pro_authenticated` | `questionnaires/send.blade.php` |
| `pro_authenticated` | `questionnaires/show.blade.php` |
| `pro_authenticated` | `questionnaires/show_response.blade.php` |
| `pro_authenticated` | `receipts/ca-monthly.blade.php` |
| `pro_authenticated` | `receipts/create.blade.php` |
| `pro_authenticated` | `receipts/index.blade.php` |
| `public_or_marketing` | `recette/index.blade.php` |
| `public_or_marketing` | `recette/show.blade.php` |
| `public_or_marketing` | `reservations/create.blade.php` |
| `public_or_marketing` | `reservations/success.blade.php` |
| `public_or_marketing` | `results.blade.php` |
| `public_or_marketing` | `session_note_templates/create.blade.php` |
| `public_or_marketing` | `session_note_templates/edit.blade.php` |
| `public_or_marketing` | `session_note_templates/index.blade.php` |
| `public_or_marketing` | `session_note_templates/show.blade.php` |
| `pro_authenticated` | `session_notes/create.blade.php` |
| `pro_authenticated` | `session_notes/edit.blade.php` |
| `pro_authenticated` | `session_notes/index.blade.php` |
| `pro_authenticated` | `session_notes/show.blade.php` |
| `public_or_marketing` | `sitemap-test.blade.php` |
| `public_or_marketing` | `special_availabilities/create.blade.php` |
| `public_or_marketing` | `special_availabilities/edit.blade.php` |
| `public_or_marketing` | `special_availabilities/index.blade.php` |
| `public_or_marketing` | `super-pdp/received-invoices.blade.php` |
| `public_or_marketing` | `test/certificate.blade.php` |
| `public_or_marketing` | `testimonials/submit.blade.php` |
| `public_or_marketing` | `testimonials/thankyou.blade.php` |
| `public_or_marketing` | `thank_you.blade.php` |
| `public_or_marketing` | `therapist/stripe.blade.php` |
| `public_or_marketing` | `tisanes/index.blade.php` |
| `public_or_marketing` | `tisanes/show.blade.php` |
| `public_or_marketing` | `tools/brand-assistant.blade.php` |
| `public_or_marketing` | `tools/konva-editor.blade.php` |
| `public_or_marketing` | `tools/konva/partials/canvas.blade.php` |
| `public_or_marketing` | `tools/konva/partials/left-sidebar.blade.php` |
| `public_or_marketing` | `tools/konva/partials/right-sidebar.blade.php` |
| `public_or_marketing` | `tools/konva/partials/scripts.blade.php` |
| `public_or_marketing` | `tools/konva/partials/styles.blade.php` |
| `public_or_marketing` | `trainings/index.blade.php` |
| `public_or_marketing` | `trainings/show-chapter.blade.php` |
| `public_or_marketing` | `trainings/show-lesson.blade.php` |
| `public_or_marketing` | `trainings/show.blade.php` |
| `public_or_marketing` | `unavailabilities/create.blade.php` |
| `public_or_marketing` | `unavailabilities/edit.blade.php` |
| `public_or_marketing` | `unavailabilities/index.blade.php` |
| `public_or_marketing` | `upgrade/license.blade.php` |
| `system_templates` | `vendor/mail/html/button.blade.php` |
| `system_templates` | `vendor/mail/html/footer.blade.php` |
| `system_templates` | `vendor/mail/html/header.blade.php` |
| `system_templates` | `vendor/mail/html/layout.blade.php` |
| `system_templates` | `vendor/mail/html/message.blade.php` |
| `system_templates` | `vendor/mail/html/panel.blade.php` |
| `system_templates` | `vendor/mail/html/subcopy.blade.php` |
| `system_templates` | `vendor/mail/html/table.blade.php` |
| `system_templates` | `vendor/mail/text/button.blade.php` |
| `system_templates` | `vendor/mail/text/footer.blade.php` |
| `system_templates` | `vendor/mail/text/header.blade.php` |
| `system_templates` | `vendor/mail/text/layout.blade.php` |
| `system_templates` | `vendor/mail/text/message.blade.php` |
| `system_templates` | `vendor/mail/text/panel.blade.php` |
| `system_templates` | `vendor/mail/text/subcopy.blade.php` |
| `system_templates` | `vendor/mail/text/table.blade.php` |
| `system_templates` | `vendor/pagination/bootstrap-4.blade.php` |
| `system_templates` | `vendor/pagination/bootstrap-5.blade.php` |
| `system_templates` | `vendor/pagination/default.blade.php` |
| `system_templates` | `vendor/pagination/semantic-ui.blade.php` |
| `system_templates` | `vendor/pagination/simple-bootstrap-4.blade.php` |
| `system_templates` | `vendor/pagination/simple-bootstrap-5.blade.php` |
| `system_templates` | `vendor/pagination/simple-default.blade.php` |
| `system_templates` | `vendor/pagination/simple-tailwind.blade.php` |
| `system_templates` | `vendor/pagination/tailwind.blade.php` |
| `public_or_marketing` | `webrtc/demo.blade.php` |
| `public_or_marketing` | `webrtc/test.blade.php` |
| `public_or_marketing` | `webrtc/webrtc.blade.php` |
| `public_or_marketing` | `welcome.blade.php` |

## Route Inventory

### admin

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `admin` | `admin.welcome` | `App\Http\Controllers\AdminController@welcome` | `web` |
| `GET|HEAD` | `admin/crm` | `admin.crm.index` | `App\Http\Controllers\Admin\CrmController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/crm/export` | `admin.crm.export` | `App\Http\Controllers\Admin\CrmController@export` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/crm/import` | `admin.crm.import` | `App\Http\Controllers\Admin\CrmController@import` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/crm/import-template` | `admin.crm.import-template` | `App\Http\Controllers\Admin\CrmController@importTemplate` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/crm/leads` | `admin.crm.leads.store` | `App\Http\Controllers\Admin\CrmController@storeLead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/crm/leads/{lead}` | `admin.crm.leads.show` | `App\Http\Controllers\Admin\CrmController@showLead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PATCH` | `admin/crm/leads/{lead}` | `admin.crm.leads.update` | `App\Http\Controllers\Admin\CrmController@updateLead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `admin/crm/leads/{lead}` | `admin.crm.leads.destroy` | `App\Http\Controllers\Admin\CrmController@destroyLead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/crm/leads/{lead}/activities` | `admin.crm.activities.store` | `App\Http\Controllers\Admin\CrmController@storeActivity` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `admin/crm/leads/{lead}/activities/{activity}` | `admin.crm.activities.destroy` | `App\Http\Controllers\Admin\CrmController@destroyActivity` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PATCH` | `admin/crm/leads/{lead}/stage` | `admin.crm.leads.stage` | `App\Http\Controllers\Admin\CrmController@moveLead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/design-templates` | `admin.design-templates.index` | `App\Http\Controllers\Admin\DesignTemplateController@index` | `web` |
| `POST` | `admin/design-templates` | `admin.design-templates.store` | `App\Http\Controllers\Admin\DesignTemplateController@store` | `web` |
| `GET|HEAD` | `admin/design-templates/create` | `admin.design-templates.create` | `App\Http\Controllers\Admin\DesignTemplateController@create` | `web` |
| `POST` | `admin/design-templates/reorder` | `admin.design-templates.reorder` | `App\Http\Controllers\Admin\DesignTemplateController@reorder` | `web` |
| `PUT` | `admin/design-templates/{template}` | `admin.design-templates.update` | `App\Http\Controllers\Admin\DesignTemplateController@update` | `web` |
| `DELETE` | `admin/design-templates/{template}` | `admin.design-templates.destroy` | `App\Http\Controllers\Admin\DesignTemplateController@destroy` | `web` |
| `GET|HEAD` | `admin/design-templates/{template}/edit` | `admin.design-templates.edit` | `App\Http\Controllers\Admin\DesignTemplateController@edit` | `web` |
| `POST` | `admin/design-templates/{template}/toggle` | `admin.design-templates.toggle` | `App\Http\Controllers\Admin\DesignTemplateController@toggle` | `web` |
| `GET|HEAD` | `admin/finance` | `admin.finance.overview` | `App\Http\Controllers\Admin\StripeFinanceController@overview` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/finance/clients` | `admin.finance.customers` | `App\Http\Controllers\Admin\StripeFinanceController@customers` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/finance/clients/{customer}` | `admin.finance.customers.show` | `App\Http\Controllers\Admin\StripeFinanceController@showCustomer` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/finance/clients/{customer}/notes` | `admin.finance.customers.notes.store` | `App\Http\Controllers\Admin\StripeFinanceController@storeCustomerNote` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `admin/finance/clients/{customer}/notes/{note}` | `admin.finance.customers.notes.destroy` | `App\Http\Controllers\Admin\StripeFinanceController@destroyCustomerNote` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/finance/forecast` | `admin.finance.forecast` | `App\Http\Controllers\Admin\StripeFinanceController@forecast` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/finance/forecast/assumptions` | `admin.finance.forecast.assumptions.update` | `App\Http\Controllers\Admin\StripeFinanceController@updateForecastAssumptions` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/finance/paiements-echoues` | `admin.finance.failures` | `App\Http\Controllers\Admin\StripeFinanceController@failures` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/finance/payouts` | `admin.finance.payouts` | `App\Http\Controllers\Admin\StripeFinanceController@payouts` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/finance/sync` | `admin.finance.sync` | `App\Http\Controllers\Admin\StripeFinanceController@sync` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/impersonate/stop` | `admin.impersonate.stop` | `App\Http\Controllers\AdminImpersonationController@stop` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `admin/impersonate/{user}` | `admin.impersonate.start` | `App\Http\Controllers\AdminImpersonationController@start` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `admin/lesson/{id}` | `admin.lesson.update` | `App\Http\Controllers\AdminController@updateLesson` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/lesson/{id}/edit` | `admin.lesson.edit` | `App\Http\Controllers\AdminController@editLesson` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/license` | `admin.license` | `App\Http\Controllers\AdminController@showLicenseManagement` | `web` |
| `POST` | `admin/license/{therapist}` | `admin.license.assign` | `App\Http\Controllers\AdminController@assignLicense` | `web` |
| `GET|HEAD` | `admin/marketing/emails` | `admin.marketing.emails` | `App\Http\Controllers\MarketingController@viewEmails` | `web` |
| `GET|HEAD` | `admin/marketing/templates` | `admin.marketing.templates` | `App\Http\Controllers\EmailTemplateController@index` | `web` |
| `POST` | `admin/marketing/templates` | `admin.marketing.templates.store` | `App\Http\Controllers\EmailTemplateController@store` | `web` |
| `POST` | `admin/marketing/templates/send-test-mail` | `send.test.mail` | `App\Http\Controllers\EmailTemplateController@sendTestMail` | `web` |
| `GET|HEAD` | `admin/marketing/templates/{id}` | `admin.marketing.templates.edit` | `App\Http\Controllers\EmailTemplateController@edit` | `web` |
| `PUT` | `admin/marketing/templates/{id}` | `admin.marketing.templates.update` | `App\Http\Controllers\EmailTemplateController@update` | `web` |
| `GET|HEAD` | `admin/marketing/upload` | `admin.marketing.upload.form` | `App\Http\Controllers\MarketingController@showUploadForm` | `web` |
| `POST` | `admin/marketing/upload` | `admin.marketing.upload` | `App\Http\Controllers\MarketingController@uploadCsv` | `web` |
| `GET|HEAD` | `admin/therapists` | `admin.therapists.index` | `App\Http\Controllers\AdminController@indexTherapists` | `web` |
| `GET|HEAD` | `admin/therapists/{id}` | `admin.therapists.show` | `App\Http\Controllers\AdminController@showTherapist` | `web` |
| `PUT` | `admin/therapists/{id}/address` | `admin.therapists.updateAddress` | `App\Http\Controllers\AdminController@updateTherapistAddress` | `web` |
| `PUT` | `admin/therapists/{id}/settings` | `admin.therapists.updateSettings` | `App\Http\Controllers\AdminController@updateTherapistSettings` | `web` |
| `PUT` | `admin/therapists/{therapist}/featured` | `admin.therapists.updateFeatured` | `App\Http\Controllers\AdminController@updateFeatured` | `web` |
| `PUT` | `admin/therapists/{therapist}/picture` | `admin.therapists.updatePicture` | `App\Http\Controllers\AdminController@updateTherapistPicture` | `web` |
| `PUT` | `admin/therapists/{therapist}/toggle-license` | `admin.therapists.toggleLicense` | `App\Http\Controllers\AdminController@toggleLicense` | `web` |
| `PUT` | `admin/therapists/{therapist}/update-license-product` | `admin.therapists.updateLicenseProduct` | `App\Http\Controllers\AdminController@updateLicenseProduct` | `web` |
| `GET|HEAD` | `admin/usage/weekly` | `admin.usage.weekly` | `App\Http\Controllers\AdminController@weeklyUsage` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `admin/users` | `admin.index` | `App\Http\Controllers\AdminController@index` | `web` |

### api

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `api/design-templates` | `` | `App\Http\Controllers\Api\DesignTemplateController@index` | `api` |
| `GET|HEAD` | `api/design-templates/{template}` | `` | `App\Http\Controllers\Api\DesignTemplateController@show` | `api` |
| `POST` | `api/signaling` | `` | `App\Http\Controllers\SignalingController@signaling` | `api` |
| `POST` | `api/subscription` | `` | `App\Http\Controllers\StripeWebhookController@handleWebhook` | `api` |
| `GET|HEAD` | `api/user` | `` | `Closure` | `api, Illuminate\Auth\Middleware\Authenticate:sanctum` |

### client_portal

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `client/communautes` | `client.communities.index` | `App\Http\Controllers\ClientCommunityController@index` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/communautes/fichiers/{attachment}` | `client.communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForClient` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/communautes/{community}` | `client.communities.show` | `App\Http\Controllers\ClientCommunityController@show` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `client/communautes/{community}/messages` | `client.communities.messages.store` | `App\Http\Controllers\ClientCommunityController@storeMessage` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `client/communautes/{community}/rejoindre` | `client.communities.accept` | `App\Http\Controllers\ClientCommunityController@accept` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `client/documents/upload` | `client.documents.upload` | `App\Http\Controllers\ClientProfileController@uploadDocument` | `web` |
| `POST` | `client/files/upload` | `client.files.upload` | `App\Http\Controllers\ClientFileController@clientUpload` | `web` |
| `GET|HEAD` | `client/files/{file}/download` | `client_files.download` | `App\Http\Controllers\ClientFileController@downloadClient` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/forgot-password` | `client.password.request` | `App\Http\Controllers\ClientPasswordSetupController@forgotForm` | `web` |
| `POST` | `client/forgot-password` | `client.password.email` | `App\Http\Controllers\ClientPasswordSetupController@sendResetLink` | `web` |
| `GET|HEAD` | `client/home` | `client.home` | `App\Http\Controllers\ClientProfileController@home` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/invoices/{invoice}/pdf` | `client.invoices.pdf` | `App\Http\Controllers\InvoiceController@clientPdf` | `web` |
| `GET|HEAD` | `client/login` | `client.login` | `App\Http\Controllers\ClientAuthController@showLogin` | `web` |
| `POST` | `client/login` | `client.login.post` | `App\Http\Controllers\ClientAuthController@login` | `web` |
| `POST` | `client/logout` | `client.logout` | `App\Http\Controllers\ClientAuthController@logout` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/messages` | `client.messages.index` | `App\Http\Controllers\ClientMessageController@index` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `client/messages` | `client.messages.store` | `App\Http\Controllers\ClientMessageController@store` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `client/messages/fetch` | `client.messages.fetch` | `App\Http\Controllers\ClientMessageController@fetchLatest` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `client/reset-password` | `client.password.update` | `App\Http\Controllers\ClientPasswordSetupController@resetStore` | `web` |
| `GET|HEAD` | `client/reset-password/{token}` | `client.password.reset` | `App\Http\Controllers\ClientPasswordSetupController@resetForm` | `web` |
| `GET|HEAD` | `client/setup/{token}` | `client.setup.show` | `App\Http\Controllers\ClientPasswordSetupController@show` | `web` |
| `POST` | `client/setup/{token}` | `client.setup.store` | `App\Http\Controllers\ClientPasswordSetupController@store` | `web` |

### mobile

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `mobile` | `mobile.entry` | `Closure` | `web` |
| `GET|HEAD` | `mobile/abonnement` | `mobile.subscription.index` | `App\Http\Controllers\Mobile\MobileSubscriptionController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/api/dates-concretes` | `mobile.appointments.concrete_dates` | `App\Http\Controllers\Mobile\MobileAppointmentController@availableConcreteDatesPatient` | `web` |
| `GET|HEAD` | `mobile/api/slots` | `mobile.appointments.slots` | `App\Http\Controllers\Mobile\MobileAppointmentController@getAvailableSlotsForPatient` | `web` |
| `GET|HEAD` | `mobile/audiences` | `mobile.audiences.index` | `App\Http\Controllers\Mobile\MobileAudienceController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/audiences` | `mobile.audiences.store` | `App\Http\Controllers\Mobile\MobileAudienceController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/audiences/create` | `mobile.audiences.create` | `App\Http\Controllers\Mobile\MobileAudienceController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/audiences/{audience}` | `mobile.audiences.show` | `App\Http\Controllers\Mobile\MobileAudienceController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/audiences/{audience}` | `mobile.audiences.update` | `App\Http\Controllers\Mobile\MobileAudienceController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/audiences/{audience}` | `mobile.audiences.destroy` | `App\Http\Controllers\Mobile\MobileAudienceController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/audiences/{audience}/edit` | `mobile.audiences.edit` | `App\Http\Controllers\Mobile\MobileAudienceController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/avis-google` | `mobile.google-reviews.index` | `App\Http\Controllers\Mobile\MobileGoogleReviewController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/avis-google/connect` | `mobile.google-reviews.connect` | `App\Http\Controllers\Mobile\MobileGoogleReviewController@redirectToGoogle` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/avis-google/disconnect` | `mobile.google-reviews.disconnect` | `App\Http\Controllers\Mobile\MobileGoogleReviewController@disconnect` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/avis-google/sync` | `mobile.google-reviews.sync` | `App\Http\Controllers\Mobile\MobileGoogleReviewController@syncReviews` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/bons-cadeaux` | `mobile.gift-vouchers.index` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/bons-cadeaux` | `mobile.gift-vouchers.store` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/bons-cadeaux/create` | `mobile.gift-vouchers.create` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/bons-cadeaux/{voucher}` | `mobile.gift-vouchers.show` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/bons-cadeaux/{voucher}/disable` | `mobile.gift-vouchers.disable` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@disable` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/bons-cadeaux/{voucher}/pdf` | `mobile.gift-vouchers.pdf` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@downloadPdf` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/bons-cadeaux/{voucher}/redeem` | `mobile.gift-vouchers.redeem` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@redeem` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/bons-cadeaux/{voucher}/resend` | `mobile.gift-vouchers.resend` | `App\Http\Controllers\Mobile\MobileGiftVoucherController@resendEmails` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/client/communautes` | `mobile.client.communities.index` | `App\Http\Controllers\Mobile\MobileClientPortalController@communities` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/communautes/fichiers/{attachment}` | `mobile.client.communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForClient` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/communautes/{community}` | `mobile.client.communities.show` | `App\Http\Controllers\Mobile\MobileClientPortalController@showCommunity` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `mobile/client/communautes/{community}/messages` | `mobile.client.communities.messages.store` | `App\Http\Controllers\Mobile\MobileClientPortalController@storeCommunityMessage` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `mobile/client/communautes/{community}/rejoindre` | `mobile.client.communities.accept` | `App\Http\Controllers\Mobile\MobileClientPortalController@acceptCommunity` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `mobile/client/documents` | `mobile.client.files.store` | `App\Http\Controllers\Mobile\MobileClientPortalController@storeFile` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/documents/{file}/download` | `mobile.client.files.download` | `App\Http\Controllers\ClientFileController@downloadClient` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/factures/{invoice}/pdf` | `mobile.client.invoices.pdf` | `App\Http\Controllers\InvoiceController@clientPdf` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/home` | `mobile.client.home` | `App\Http\Controllers\Mobile\MobileClientPortalController@home` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/login` | `mobile.client.login` | `App\Http\Controllers\Mobile\MobileClientPortalController@showLogin` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated:client` |
| `POST` | `mobile/client/login` | `mobile.client.login.store` | `App\Http\Controllers\Mobile\MobileClientPortalController@login` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated:client` |
| `POST` | `mobile/client/logout` | `mobile.client.logout` | `App\Http\Controllers\Mobile\MobileClientPortalController@logout` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/client/messages` | `mobile.client.messages.index` | `App\Http\Controllers\Mobile\MobileClientPortalController@messages` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `POST` | `mobile/client/messages` | `mobile.client.messages.store` | `App\Http\Controllers\Mobile\MobileClientPortalController@storeMessage` | `web, Illuminate\Auth\Middleware\Authenticate:client` |
| `GET|HEAD` | `mobile/clients` | `mobile.clients.index` | `App\Http\Controllers\ClientProfileController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/clients` | `mobile.clients.store` | `App\Http\Controllers\Mobile\MobileClientController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/create` | `mobile.clients.create` | `App\Http\Controllers\Mobile\MobileClientController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/clients/{clientProfile}` | `mobile.clients.update` | `App\Http\Controllers\Mobile\MobileClientController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}` | `mobile.clients.show` | `App\Http\Controllers\ClientProfileController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/edit` | `mobile.clients.edit` | `App\Http\Controllers\Mobile\MobileClientController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/notes-seance` | `mobile.session-notes.index` | `App\Http\Controllers\Mobile\MobileSessionNoteController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/clients/{clientProfile}/notes-seance` | `mobile.session-notes.store` | `App\Http\Controllers\Mobile\MobileSessionNoteController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/notes-seance/create` | `mobile.session-notes.create` | `App\Http\Controllers\Mobile\MobileSessionNoteController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures` | `mobile.metrics.index` | `App\Http\Controllers\Mobile\MobileMetricController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/clients/{clientProfile}/suivi-mesures` | `mobile.metrics.store` | `App\Http\Controllers\Mobile\MobileMetricController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures/create` | `mobile.metrics.create` | `App\Http\Controllers\Mobile\MobileMetricController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}` | `mobile.metrics.show` | `App\Http\Controllers\Mobile\MobileMetricController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}` | `mobile.metrics.update` | `App\Http\Controllers\Mobile\MobileMetricController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}` | `mobile.metrics.destroy` | `App\Http\Controllers\Mobile\MobileMetricController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/edit` | `mobile.metrics.edit` | `App\Http\Controllers\Mobile\MobileMetricController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/valeurs` | `mobile.metrics.entries.store` | `App\Http\Controllers\Mobile\MobileMetricController@storeEntry` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/create` | `mobile.metrics.entries.create` | `App\Http\Controllers\Mobile\MobileMetricController@createEntry` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}` | `mobile.metrics.entries.update` | `App\Http\Controllers\Mobile\MobileMetricController@updateEntry` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}` | `mobile.metrics.entries.destroy` | `App\Http\Controllers\Mobile\MobileMetricController@destroyEntry` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}/edit` | `mobile.metrics.entries.edit` | `App\Http\Controllers\Mobile\MobileMetricController@editEntry` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/communautes` | `mobile.communities.index` | `App\Http\Controllers\Mobile\MobileCommunityController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/communautes` | `mobile.communities.store` | `App\Http\Controllers\Mobile\MobileCommunityController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/communautes/create` | `mobile.communities.create` | `App\Http\Controllers\Mobile\MobileCommunityController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/communautes/fichiers/{attachment}` | `mobile.communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForPractitioner` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/communautes/{community}` | `mobile.communities.show` | `App\Http\Controllers\Mobile\MobileCommunityController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/communautes/{community}` | `mobile.communities.update` | `App\Http\Controllers\Mobile\MobileCommunityController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/communautes/{community}` | `mobile.communities.destroy` | `App\Http\Controllers\Mobile\MobileCommunityController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/communautes/{community}/edit` | `mobile.communities.edit` | `App\Http\Controllers\Mobile\MobileCommunityController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/communautes/{community}/membres` | `mobile.communities.members.store` | `App\Http\Controllers\Mobile\MobileCommunityController@storeMember` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/communautes/{community}/membres/{member}` | `mobile.communities.members.destroy` | `App\Http\Controllers\Mobile\MobileCommunityController@destroyMember` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/communautes/{community}/messages` | `mobile.communities.messages.store` | `App\Http\Controllers\Mobile\MobileCommunityController@storeMessage` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/dashboard` | `mobile.dashboard` | `App\Http\Controllers\DashboardController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/devis/{invoice}` | `mobile.quotes.show` | `App\Http\Controllers\Mobile\MobileInvoiceController@showQuote` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/disponibilites` | `mobile.availabilities.index` | `App\Http\Controllers\AvailabilityController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/disponibilites` | `mobile.availabilities.store` | `App\Http\Controllers\AvailabilityController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/disponibilites/create` | `mobile.availabilities.create` | `App\Http\Controllers\AvailabilityController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/disponibilites/{availability}` | `mobile.availabilities.update` | `App\Http\Controllers\AvailabilityController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/disponibilites/{availability}` | `mobile.availabilities.destroy` | `App\Http\Controllers\AvailabilityController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/disponibilites/{availability}/edit` | `mobile.availabilities.edit` | `App\Http\Controllers\AvailabilityController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/documents` | `mobile.documents.index` | `App\Http\Controllers\Mobile\MobileDocumentController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/documents/clients/{clientProfile}` | `mobile.documents.client` | `App\Http\Controllers\Mobile\MobileDocumentController@showClient` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/documents/clients/{clientProfile}/fichiers` | `mobile.documents.files.store` | `App\Http\Controllers\Mobile\MobileDocumentController@storeFile` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/documents/clients/{clientProfile}/fichiers/{file}` | `mobile.documents.files.destroy` | `App\Http\Controllers\Mobile\MobileDocumentController@destroyFile` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/documents/clients/{clientProfile}/fichiers/{file}/download` | `mobile.documents.files.download` | `App\Http\Controllers\Mobile\MobileDocumentController@downloadFile` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/documents/clients/{clientProfile}/signatures` | `mobile.documents.signatures.store` | `App\Http\Controllers\Mobile\MobileDocumentController@storeSignatureDocument` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/documents/signatures/{document}/final` | `mobile.documents.signatures.final` | `App\Http\Controllers\Mobile\MobileDocumentController@downloadFinal` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/documents/signatures/{document}/original` | `mobile.documents.signatures.original` | `App\Http\Controllers\Mobile\MobileDocumentController@downloadOriginal` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/documents/signatures/{document}/send` | `mobile.documents.signatures.send` | `App\Http\Controllers\Mobile\MobileDocumentController@sendSignatureDocument` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/documents/signatures/{signing}/resend` | `mobile.documents.signatures.resend` | `App\Http\Controllers\Mobile\MobileDocumentController@resendSignature` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/emargements` | `mobile.emargements.index` | `App\Http\Controllers\Mobile\MobileEmargementController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/emargements/rendez-vous/{appointment}/envoyer` | `mobile.emargements.send` | `App\Http\Controllers\Mobile\MobileEmargementController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/emargements/{emargement}/download` | `mobile.emargements.download` | `App\Http\Controllers\Mobile\MobileEmargementController@download` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/emargements/{emargement}/renvoyer` | `mobile.emargements.resend` | `App\Http\Controllers\Mobile\MobileEmargementController@resend` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/entreprises` | `mobile.corporate-clients.index` | `App\Http\Controllers\Mobile\MobileCorporateClientController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/entreprises` | `mobile.corporate-clients.store` | `App\Http\Controllers\Mobile\MobileCorporateClientController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/entreprises/create` | `mobile.corporate-clients.create` | `App\Http\Controllers\Mobile\MobileCorporateClientController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/entreprises/{corporateClient}` | `mobile.corporate-clients.show` | `App\Http\Controllers\Mobile\MobileCorporateClientController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/entreprises/{corporateClient}` | `mobile.corporate-clients.update` | `App\Http\Controllers\Mobile\MobileCorporateClientController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/entreprises/{corporateClient}` | `mobile.corporate-clients.destroy` | `App\Http\Controllers\Mobile\MobileCorporateClientController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/entreprises/{corporateClient}/edit` | `mobile.corporate-clients.edit` | `App\Http\Controllers\Mobile\MobileCorporateClientController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/evenements` | `mobile.events.index` | `App\Http\Controllers\Mobile\MobileEventController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/evenements` | `mobile.events.store` | `App\Http\Controllers\Mobile\MobileEventController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/evenements/create` | `mobile.events.create` | `App\Http\Controllers\Mobile\MobileEventController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/evenements/{event}` | `mobile.events.show` | `App\Http\Controllers\Mobile\MobileEventController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/evenements/{event}` | `mobile.events.update` | `App\Http\Controllers\Mobile\MobileEventController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/evenements/{event}` | `mobile.events.destroy` | `App\Http\Controllers\Mobile\MobileEventController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/evenements/{event}/edit` | `mobile.events.edit` | `App\Http\Controllers\Mobile\MobileEventController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/evenements/{event}/participants` | `mobile.events.participants.add-client` | `App\Http\Controllers\Mobile\MobileEventController@addClient` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/factures-recues` | `mobile.received-invoices.index` | `App\Http\Controllers\Mobile\MobileReceivedInvoiceController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/factures-recues/{receivedInvoice}` | `mobile.received-invoices.show` | `App\Http\Controllers\Mobile\MobileReceivedInvoiceController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/factures-recues/{receivedInvoice}/download` | `mobile.received-invoices.download` | `App\Http\Controllers\Mobile\MobileReceivedInvoiceController@download` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/formations-digitales` | `mobile.digital-trainings.index` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/formations-digitales` | `mobile.digital-trainings.store` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/formations-digitales/create` | `mobile.digital-trainings.create` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/formations-digitales/{digitalTraining}` | `mobile.digital-trainings.show` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/formations-digitales/{digitalTraining}` | `mobile.digital-trainings.update` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/formations-digitales/{digitalTraining}` | `mobile.digital-trainings.destroy` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/formations-digitales/{digitalTraining}/edit` | `mobile.digital-trainings.edit` | `App\Http\Controllers\Mobile\MobileDigitalTrainingController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/invoices` | `mobile.invoices.index` | `App\Http\Controllers\InvoiceController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/invoices/{invoice}` | `mobile.invoices.show` | `App\Http\Controllers\Mobile\MobileInvoiceController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/lieux` | `mobile.practice-locations.index` | `App\Http\Controllers\PracticeLocationController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/lieux` | `mobile.practice-locations.store` | `App\Http\Controllers\PracticeLocationController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/lieux/create` | `mobile.practice-locations.create` | `App\Http\Controllers\PracticeLocationController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/lieux/{practice_location}` | `mobile.practice-locations.update` | `App\Http\Controllers\PracticeLocationController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/lieux/{practice_location}` | `mobile.practice-locations.destroy` | `App\Http\Controllers\PracticeLocationController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/lieux/{practice_location}/edit` | `mobile.practice-locations.edit` | `App\Http\Controllers\PracticeLocationController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/login` | `mobile.login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@createMobile` | `web` |
| `POST` | `mobile/login` | `mobile.login.store` | `App\Http\Controllers\Auth\AuthenticatedSessionController@storeMobile` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated` |
| `POST` | `mobile/logout` | `mobile.logout` | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/menu` | `mobile.menu` | `Closure` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/newsletters` | `mobile.newsletters.index` | `App\Http\Controllers\Mobile\MobileNewsletterController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/newsletters` | `mobile.newsletters.store` | `App\Http\Controllers\Mobile\MobileNewsletterController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/newsletters/create` | `mobile.newsletters.create` | `App\Http\Controllers\Mobile\MobileNewsletterController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/newsletters/{newsletter}` | `mobile.newsletters.show` | `App\Http\Controllers\Mobile\MobileNewsletterController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/newsletters/{newsletter}` | `mobile.newsletters.update` | `App\Http\Controllers\Mobile\MobileNewsletterController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/newsletters/{newsletter}` | `mobile.newsletters.destroy` | `App\Http\Controllers\Mobile\MobileNewsletterController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/newsletters/{newsletter}/edit` | `mobile.newsletters.edit` | `App\Http\Controllers\Mobile\MobileNewsletterController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/newsletters/{newsletter}/send-now` | `mobile.newsletters.send-now` | `App\Http\Controllers\Mobile\MobileNewsletterController@sendNow` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/newsletters/{newsletter}/send-test` | `mobile.newsletters.send-test` | `App\Http\Controllers\Mobile\MobileNewsletterController@sendTest` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/notes-seance/{sessionNote}` | `mobile.session-notes.show` | `App\Http\Controllers\Mobile\MobileSessionNoteController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/notes-seance/{sessionNote}` | `mobile.session-notes.update` | `App\Http\Controllers\Mobile\MobileSessionNoteController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/notes-seance/{sessionNote}` | `mobile.session-notes.destroy` | `App\Http\Controllers\Mobile\MobileSessionNoteController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/notes-seance/{sessionNote}/edit` | `mobile.session-notes.edit` | `App\Http\Controllers\Mobile\MobileSessionNoteController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/pack-purchases/{packPurchase}/revoke` | `mobile.packs.purchases.revoke` | `App\Http\Controllers\Mobile\MobilePackProductController@revokePurchase` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/packs` | `mobile.packs.index` | `App\Http\Controllers\Mobile\MobilePackProductController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/packs` | `mobile.packs.store` | `App\Http\Controllers\Mobile\MobilePackProductController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/packs/create` | `mobile.packs.create` | `App\Http\Controllers\Mobile\MobilePackProductController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/packs/{packProduct}` | `mobile.packs.show` | `App\Http\Controllers\Mobile\MobilePackProductController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/packs/{packProduct}` | `mobile.packs.update` | `App\Http\Controllers\Mobile\MobilePackProductController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/packs/{packProduct}` | `mobile.packs.destroy` | `App\Http\Controllers\Mobile\MobilePackProductController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/packs/{packProduct}/assign` | `mobile.packs.assign` | `App\Http\Controllers\Mobile\MobilePackProductController@assign` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/packs/{packProduct}/edit` | `mobile.packs.edit` | `App\Http\Controllers\Mobile\MobilePackProductController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/parrainage` | `mobile.referrals.index` | `App\Http\Controllers\Mobile\MobileReferralController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/parrainage/invitations` | `mobile.referrals.invite` | `App\Http\Controllers\Mobile\MobileReferralController@invite` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/parrainage/invitations/{invite}/renvoyer` | `mobile.referrals.resend` | `App\Http\Controllers\Mobile\MobileReferralController@resend` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/prestations` | `mobile.products.index` | `App\Http\Controllers\Mobile\MobileProductController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/prestations` | `mobile.products.store` | `App\Http\Controllers\Mobile\MobileProductController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/prestations/create` | `mobile.products.create` | `App\Http\Controllers\Mobile\MobileProductController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/prestations/{product}` | `mobile.products.show` | `App\Http\Controllers\Mobile\MobileProductController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/prestations/{product}` | `mobile.products.update` | `App\Http\Controllers\Mobile\MobileProductController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/prestations/{product}` | `mobile.products.destroy` | `App\Http\Controllers\Mobile\MobileProductController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/prestations/{product}/edit` | `mobile.products.edit` | `App\Http\Controllers\Mobile\MobileProductController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS` | `mobile/pro/more` | `mobile.pro.more.redirect` | `Illuminate\Routing\RedirectController` | `web` |
| `GET|HEAD` | `mobile/profil` | `mobile.profile.index` | `App\Http\Controllers\Mobile\MobileProfileController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/profil` | `mobile.profile.update` | `App\Http\Controllers\Mobile\MobileProfileController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/profil/edit` | `mobile.profile.edit` | `App\Http\Controllers\Mobile\MobileProfileController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/questionnaires` | `mobile.questionnaires.index` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/questionnaires` | `mobile.questionnaires.store` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/questionnaires/create` | `mobile.questionnaires.create` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/questionnaires/{questionnaire}` | `mobile.questionnaires.show` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/questionnaires/{questionnaire}` | `mobile.questionnaires.update` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/questionnaires/{questionnaire}` | `mobile.questionnaires.destroy` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/questionnaires/{questionnaire}/edit` | `mobile.questionnaires.edit` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/questionnaires/{questionnaire}/questions/{question}` | `mobile.questionnaires.questions.destroy` | `App\Http\Controllers\Mobile\MobileQuestionnaireController@destroyQuestion` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/rdv` | `mobile.appointments.store` | `App\Http\Controllers\Mobile\MobileAppointmentController@store` | `web` |
| `GET|HEAD` | `mobile/rdv/{token}` | `mobile.rdv.show` | `App\Http\Controllers\Mobile\MobileAppointmentController@show` | `web` |
| `GET|HEAD` | `mobile/rdv/{token}/ics` | `mobile.appointments.ics` | `App\Http\Controllers\Mobile\MobileAppointmentController@downloadICS` | `web` |
| `GET|HEAD` | `mobile/recettes` | `mobile.receipts.index` | `App\Http\Controllers\Mobile\MobileReceiptController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/recettes` | `mobile.receipts.store` | `App\Http\Controllers\Mobile\MobileReceiptController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/recettes/ca-mensuel` | `mobile.receipts.monthly` | `App\Http\Controllers\Mobile\MobileReceiptController@monthly` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/recettes/create` | `mobile.receipts.create` | `App\Http\Controllers\Mobile\MobileReceiptController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/recettes/{receipt}/contre-passer` | `mobile.receipts.reverse` | `App\Http\Controllers\Mobile\MobileReceiptController@reverse` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/recherche-praticien` | `mobile.search.index` | `App\Http\Controllers\Mobile\TherapistSearchController@index` | `web` |
| `POST` | `mobile/recherche-praticien` | `mobile.search.submit` | `App\Http\Controllers\Mobile\TherapistSearchController@search` | `web` |
| `GET|HEAD` | `mobile/rendez-vous` | `mobile.appointments.index` | `App\Http\Controllers\AppointmentController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/rendez-vous` | `mobile.appointments.store_practitioner` | `App\Http\Controllers\AppointmentController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/rendez-vous/create` | `mobile.appointments.create` | `App\Http\Controllers\AppointmentController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/rendez-vous/{appointment}` | `mobile.appointments.update` | `App\Http\Controllers\AppointmentController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/rendez-vous/{appointment}` | `mobile.appointments.show` | `App\Http\Controllers\AppointmentController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/rendez-vous/{appointment}/edit` | `mobile.appointments.edit` | `App\Http\Controllers\AppointmentController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/stock` | `mobile.inventory.index` | `App\Http\Controllers\InventoryItemController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/stock` | `mobile.inventory.store` | `App\Http\Controllers\InventoryItemController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/stock/create` | `mobile.inventory.create` | `App\Http\Controllers\InventoryItemController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `mobile/stock/{id}` | `mobile.inventory.destroy` | `App\Http\Controllers\InventoryItemController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `mobile/stock/{inventoryItem}` | `mobile.inventory.update` | `App\Http\Controllers\InventoryItemController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/stock/{inventoryItem}/consume` | `mobile.inventory.consume` | `App\Http\Controllers\InventoryItemController@consume` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `mobile/stock/{inventoryItem}/consume-unit` | `mobile.inventory.consume.unit` | `App\Http\Controllers\InventoryItemController@consumeUnit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/stock/{inventoryItem}/edit` | `mobile.inventory.edit` | `App\Http\Controllers\InventoryItemController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `mobile/therapeute/{slug}` | `mobile.therapists.show` | `App\Http\Controllers\Mobile\MobileTherapistController@show` | `web` |
| `POST` | `mobile/therapeute/{slug}/information` | `mobile.therapists.information` | `App\Http\Controllers\Mobile\MobileTherapistController@sendInformationRequest` | `web` |
| `GET|HEAD` | `mobile/therapeute/{slug}/prendre-rdv` | `mobile.appointments.create_from_therapist` | `App\Http\Controllers\Mobile\MobileAppointmentController@createFromTherapistSlug` | `web` |

### pro_authenticated

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `appointments` | `appointments.index` | `App\Http\Controllers\AppointmentController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `appointments` | `appointments.store` | `App\Http\Controllers\AppointmentController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `appointments/available-dates` | `appointments.available-dates` | `App\Http\Controllers\AppointmentController@getAvailableDates` | `web` |
| `POST` | `appointments/available-dates-concrete-patient` | `appointments.available-dates-concrete-patient` | `App\Http\Controllers\AppointmentController@availableConcreteDatesPatient` | `web` |
| `POST` | `appointments/available-dates-concrete-therapist` | `appointments.available-dates-concrete-therapist` | `App\Http\Controllers\AppointmentController@availableConcreteDatesTherapist` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `appointments/available-dates-patient` | `appointments.available-dates-patient` | `App\Http\Controllers\AppointmentController@availableDatesPatient` | `web` |
| `POST` | `appointments/available-slots` | `appointments.available-slots` | `App\Http\Controllers\AppointmentController@getAvailableSlots` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `appointments/available-slots-patient` | `appointments.available-slots-patient` | `App\Http\Controllers\AppointmentController@getAvailableSlotsForPatient` | `web` |
| `POST` | `appointments/available-slots-therapist` | `appointments.available-slots-therapist` | `App\Http\Controllers\AppointmentController@getAvailableSlotsForTherapist` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `appointments/cancel` | `appointments.cancel` | `App\Http\Controllers\AppointmentController@cancel` | `web` |
| `GET|HEAD` | `appointments/create` | `appointments.create` | `App\Http\Controllers\AppointmentController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `appointments/success` | `appointments.success` | `App\Http\Controllers\AppointmentController@success` | `web` |
| `GET|HEAD` | `appointments/{appointment}` | `appointments.show` | `App\Http\Controllers\AppointmentController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT` | `appointments/{appointment}` | `appointments.update` | `App\Http\Controllers\AppointmentController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `appointments/{appointment}` | `appointments.destroy` | `App\Http\Controllers\AppointmentController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT` | `appointments/{appointment}/complete` | `appointments.complete` | `App\Http\Controllers\AppointmentController@markAsCompleted` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT` | `appointments/{appointment}/completeindex` | `appointments.completeindex` | `App\Http\Controllers\AppointmentController@markAsCompletedIndex` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `appointments/{appointment}/edit` | `appointments.edit` | `App\Http\Controllers\AppointmentController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `appointments/{appointment}/emargement/send` | `emargement.send` | `App\Http\Controllers\EmargementController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `audiences` | `audiences.index` | `App\Http\Controllers\AudienceController@index` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `POST` | `audiences` | `audiences.store` | `App\Http\Controllers\AudienceController@store` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `audiences/create` | `audiences.create` | `App\Http\Controllers\AudienceController@create` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `audiences/{audience}` | `audiences.show` | `App\Http\Controllers\AudienceController@show` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `PUT|PATCH` | `audiences/{audience}` | `audiences.update` | `App\Http\Controllers\AudienceController@update` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `DELETE` | `audiences/{audience}` | `audiences.destroy` | `App\Http\Controllers\AudienceController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `audiences/{audience}/edit` | `audiences.edit` | `App\Http\Controllers\AudienceController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `availabilities` | `availabilities.index` | `App\Http\Controllers\AvailabilityController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `availabilities` | `availabilities.store` | `App\Http\Controllers\AvailabilityController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `availabilities/create` | `availabilities.create` | `App\Http\Controllers\AvailabilityController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `PUT` | `availabilities/{availability}` | `availabilities.update` | `App\Http\Controllers\AvailabilityController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:update,availability` |
| `DELETE` | `availabilities/{availability}` | `availabilities.destroy` | `App\Http\Controllers\AvailabilityController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:delete,availability` |
| `GET|HEAD` | `availabilities/{availability}/edit` | `availabilities.edit` | `App\Http\Controllers\AvailabilityController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:update,availability` |
| `POST` | `client-profiles/{clientProfile}/request-testimonial` | `testimonial.request` | `App\Http\Controllers\TestimonialRequestController@sendRequest` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `clients/{clientProfile}/documents` | `documents.store` | `App\Http\Controllers\DocumentController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro` | `dashboard-pro` | `App\Http\Controllers\DashboardController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `dashboard-pro/articles` | `dashboardpro.articles.index` | `App\Http\Controllers\TherapistArticleController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/articles` | `dashboardpro.articles.store` | `App\Http\Controllers\TherapistArticleController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/articles/create` | `dashboardpro.articles.create` | `App\Http\Controllers\TherapistArticleController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/articles/upload-image` | `dashboardpro.articles.upload_image` | `App\Http\Controllers\TherapistArticleController@uploadImage` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/articles/{article}` | `dashboardpro.articles.show` | `App\Http\Controllers\TherapistArticleController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `dashboard-pro/articles/{article}` | `dashboardpro.articles.update` | `App\Http\Controllers\TherapistArticleController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `dashboard-pro/articles/{article}` | `dashboardpro.articles.destroy` | `App\Http\Controllers\TherapistArticleController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/articles/{article}/edit` | `dashboardpro.articles.edit` | `App\Http\Controllers\TherapistArticleController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/bons-cadeaux` | `pro.gift-vouchers.index` | `App\Http\Controllers\GiftVoucherController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/bons-cadeaux` | `pro.gift-vouchers.store` | `App\Http\Controllers\GiftVoucherController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/bons-cadeaux/create` | `pro.gift-vouchers.create` | `App\Http\Controllers\GiftVoucherController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/bons-cadeaux/settings` | `pro.gift-vouchers.settings.update` | `App\Http\Controllers\GiftVoucherController@updateSettings` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/bons-cadeaux/{voucher}` | `pro.gift-vouchers.show` | `App\Http\Controllers\GiftVoucherController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/bons-cadeaux/{voucher}/disable` | `pro.gift-vouchers.disable` | `App\Http\Controllers\GiftVoucherController@disable` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/bons-cadeaux/{voucher}/pdf` | `pro.gift-vouchers.pdf` | `App\Http\Controllers\GiftVoucherController@downloadPdf` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/bons-cadeaux/{voucher}/redeem` | `pro.gift-vouchers.redeem` | `App\Http\Controllers\GiftVoucherController@redeem` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `dashboard-pro/bons-cadeaux/{voucher}/resend` | `pro.gift-vouchers.resend` | `App\Http\Controllers\GiftVoucherController@resendEmails` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `dashboard-pro/qrcode` | `dashboard-pro.qrcode` | `App\Http\Controllers\DashboardController@generateQrCode` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `digital-trainings` | `digital-trainings.index` | `App\Http\Controllers\DigitalTrainingController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings` | `digital-trainings.store` | `App\Http\Controllers\DigitalTrainingController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/create` | `digital-trainings.create` | `App\Http\Controllers\DigitalTrainingController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `digital-trainings/{digitalTraining}` | `digital-trainings.update` | `App\Http\Controllers\DigitalTrainingController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `digital-trainings/{digitalTraining}` | `digital-trainings.destroy` | `App\Http\Controllers\DigitalTrainingController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/{digitalTraining}/builder` | `digital-trainings.builder` | `App\Http\Controllers\DigitalTrainingController@builder` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/{digitalTraining}/comments` | `digital-trainings.comments.index` | `App\Http\Controllers\DigitalTrainingCommentController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/comments/{comment}/reply` | `digital-trainings.comments.reply.store` | `App\Http\Controllers\TherapistTrainingCommentReplyController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/{digitalTraining}/edit` | `digital-trainings.edit` | `App\Http\Controllers\DigitalTrainingController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/{digitalTraining}/enrollments` | `digital-trainings.enrollments.index` | `App\Http\Controllers\DigitalTrainingEnrollmentController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/enrollments` | `digital-trainings.enrollments.store` | `App\Http\Controllers\DigitalTrainingEnrollmentController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `digital-trainings/{digitalTraining}/enrollments/{enrollment}` | `digital-trainings.enrollments.destroy` | `App\Http\Controllers\DigitalTrainingEnrollmentController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/modules` | `digital-trainings.modules.store` | `App\Http\Controllers\DigitalTrainingController@storeModule` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `digital-trainings/{digitalTraining}/modules/{module}` | `digital-trainings.modules.update` | `App\Http\Controllers\DigitalTrainingController@updateModule` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `digital-trainings/{digitalTraining}/modules/{module}` | `digital-trainings.modules.destroy` | `App\Http\Controllers\DigitalTrainingController@destroyModule` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/modules/{module}/blocks` | `digital-trainings.blocks.store` | `App\Http\Controllers\DigitalTrainingController@storeBlock` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `digital-trainings/{digitalTraining}/modules/{module}/blocks/{block}` | `digital-trainings.blocks.update` | `App\Http\Controllers\DigitalTrainingController@updateBlock` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `digital-trainings/{digitalTraining}/modules/{module}/blocks/{block}` | `digital-trainings.blocks.destroy` | `App\Http\Controllers\DigitalTrainingController@destroyBlock` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/modules/{module}/blocks/{block}/move` | `digital-trainings.blocks.move` | `App\Http\Controllers\DigitalTrainingController@moveBlock` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `digital-trainings/{digitalTraining}/modules/{module}/move` | `digital-trainings.modules.move` | `App\Http\Controllers\DigitalTrainingController@moveModule` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `digital-trainings/{digitalTraining}/preview` | `digital-trainings.preview` | `App\Http\Controllers\DigitalTrainingController@preview` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `documents/{doc}/download-final` | `documents.download.final` | `App\Http\Controllers\DocumentController@downloadFinal` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `documents/{doc}/send` | `documents.send` | `App\Http\Controllers\DocumentSigningController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events` | `events.index` | `App\Http\Controllers\EventController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `events` | `events.store` | `App\Http\Controllers\EventController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/create` | `events.create` | `App\Http\Controllers\EventController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/{event}` | `events.show` | `App\Http\Controllers\EventController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `events/{event}` | `events.update` | `App\Http\Controllers\EventController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `events/{event}` | `events.destroy` | `App\Http\Controllers\EventController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/{event}/duplicate` | `events.duplicate` | `App\Http\Controllers\EventController@duplicate` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `events/{event}/duplicate` | `events.duplicate.store` | `App\Http\Controllers\EventController@storeDuplicate` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/{event}/edit` | `events.edit` | `App\Http\Controllers\EventController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/{event}/infos` | `events.public.show` | `App\Http\Controllers\EventController@publicShow` | `web` |
| `GET|HEAD` | `events/{event}/reservation-success` | `reservations.success` | `App\Http\Controllers\ReservationController@success` | `web` |
| `POST` | `events/{event}/reservations/add-from-client` | `events.reservations.addFromClient` | `App\Http\Controllers\EventController@addReservationFromClient` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `events/{event}/reservations/{reservation}/create-client` | `reservations.createClient` | `App\Http\Controllers\ClientProfileController@storeFromReservation` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `events/{event}/reserve` | `events.reserve.create` | `App\Http\Controllers\ReservationController@create` | `web` |
| `POST` | `events/{event}/reserve` | `events.reserve.store` | `App\Http\Controllers\ReservationController@store` | `web` |
| `POST` | `inventory-items/{inventoryItem}/consume-unit` | `inventory_items.consume.unit` | `App\Http\Controllers\InventoryItemController@consumeUnit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `invoices` | `invoices.index` | `App\Http\Controllers\InvoiceController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `invoices` | `invoices.store` | `App\Http\Controllers\InvoiceController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `invoices/create` | `invoices.create` | `App\Http\Controllers\InvoiceController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `invoices/create-quote` | `invoices.createQuote` | `App\Http\Controllers\InvoiceController@createQuote` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `invoices/from-pack/{packPurchase}` | `invoices.fromPackPurchase` | `App\Http\Controllers\InvoiceController@createFromPackPurchase` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `invoices/store-quote` | `invoices.storeQuote` | `App\Http\Controllers\InvoiceController@storeQuote` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `invoices/{invoice}` | `invoices.show` | `App\Http\Controllers\InvoiceController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice, Illuminate\Auth\Middleware\Authorize:view,invoice` |
| `PUT` | `invoices/{invoice}` | `invoices.update` | `App\Http\Controllers\InvoiceController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice, Illuminate\Auth\Middleware\Authorize:update,invoice` |
| `DELETE` | `invoices/{invoice}` | `invoices.destroy` | `App\Http\Controllers\InvoiceController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice, Illuminate\Auth\Middleware\Authorize:delete,invoice` |
| `POST` | `invoices/{invoice}/create-payment-link` | `invoices.createPaymentLink` | `App\Http\Controllers\InvoiceController@createPaymentLink` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `invoices/{invoice}/edit` | `invoices.edit` | `App\Http\Controllers\InvoiceController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice, Illuminate\Auth\Middleware\Authorize:update,invoice` |
| `PUT` | `invoices/{invoice}/mark-as-paid` | `invoices.markAsPaid` | `App\Http\Controllers\InvoiceController@markAsPaid` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `invoices/{invoice}/pdf` | `invoices.pdf` | `App\Http\Controllers\InvoiceController@generatePDF` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice, Illuminate\Auth\Middleware\Authorize:view,invoice` |
| `POST` | `invoices/{invoice}/send-email` | `invoices.sendEmail` | `App\Http\Controllers\InvoiceController@sendEmail` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `invoices/{invoice}/send-payment-reminder` | `invoices.sendPaymentReminder` | `App\Http\Controllers\InvoiceController@sendPaymentReminder` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `newsletters` | `newsletters.index` | `App\Http\Controllers\NewsletterController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `newsletters` | `newsletters.store` | `App\Http\Controllers\NewsletterController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `newsletters/create` | `newsletters.create` | `App\Http\Controllers\NewsletterController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `newsletters/unsubscribe/{token}` | `unsubscribe.newsletter` | `App\Http\Controllers\NewsletterUnsubscribeController@show` | `web` |
| `POST` | `newsletters/unsubscribe/{token}` | `unsubscribe.newsletter.confirm` | `App\Http\Controllers\NewsletterUnsubscribeController@confirm` | `web` |
| `POST` | `newsletters/upload-image` | `newsletters.upload-image` | `App\Http\Controllers\NewsletterController@uploadImage` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `newsletters/{newsletter}` | `newsletters.show` | `App\Http\Controllers\NewsletterController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `newsletters/{newsletter}` | `newsletters.update` | `App\Http\Controllers\NewsletterController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `newsletters/{newsletter}` | `newsletters.destroy` | `App\Http\Controllers\NewsletterController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `newsletters/{newsletter}/edit` | `newsletters.edit` | `App\Http\Controllers\NewsletterController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `newsletters/{newsletter}/send-now` | `newsletters.send-now` | `App\Http\Controllers\NewsletterController@sendNow` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `newsletters/{newsletter}/send-test` | `newsletters.send-test` | `App\Http\Controllers\NewsletterController@sendTest` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pack-products` | `pack-products.index` | `App\Http\Controllers\PackProductController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pack-products` | `pack-products.store` | `App\Http\Controllers\PackProductController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pack-products/create` | `pack-products.create` | `App\Http\Controllers\PackProductController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pack-products/{packProduct}/assign` | `pack-products.assign` | `App\Http\Controllers\PackProductController@assignToClient` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pack-products/{pack_product}` | `pack-products.show` | `App\Http\Controllers\PackProductController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `pack-products/{pack_product}` | `pack-products.update` | `App\Http\Controllers\PackProductController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `pack-products/{pack_product}` | `pack-products.destroy` | `App\Http\Controllers\PackProductController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pack-products/{pack_product}/edit` | `pack-products.edit` | `App\Http\Controllers\PackProductController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `practice-locations` | `practice-locations.index` | `App\Http\Controllers\PracticeLocationController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `practice-locations` | `practice-locations.store` | `App\Http\Controllers\PracticeLocationController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `practice-locations/create` | `practice-locations.create` | `App\Http\Controllers\PracticeLocationController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `practice-locations/{practice_location}` | `practice-locations.update` | `App\Http\Controllers\PracticeLocationController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `practice-locations/{practice_location}` | `practice-locations.destroy` | `App\Http\Controllers\PracticeLocationController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `practice-locations/{practice_location}/edit` | `practice-locations.edit` | `App\Http\Controllers\PracticeLocationController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `practice-locations/{practice_location}/invites` | `practice-locations.invites.store` | `App\Http\Controllers\PracticeLocationInviteController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `practice-locations/{practice_location}/leave` | `practice-locations.leave` | `App\Http\Controllers\PracticeLocationMemberController@leave` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `practice-locations/{practice_location}/members/{member}/remove` | `practice-locations.members.remove` | `App\Http\Controllers\PracticeLocationMemberController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro` | `prolanding` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `pro/facturation-therapeute` | `facturationtherapeute` | `Closure` | `web` |
| `GET|HEAD` | `pro/google-reviews` | `pro.google-reviews.index` | `App\Http\Controllers\GoogleReviewController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro/google-reviews/callback` | `pro.google-reviews.callback` | `App\Http\Controllers\GoogleReviewController@handleCallback` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro/google-reviews/connect` | `pro.google-reviews.connect` | `App\Http\Controllers\GoogleReviewController@redirectToGoogle` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pro/google-reviews/disconnect` | `pro.google-reviews.disconnect` | `App\Http\Controllers\GoogleReviewController@disconnect` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pro/google-reviews/sync` | `pro.google-reviews.sync` | `App\Http\Controllers\GoogleReviewController@syncReviews` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro/referrals` | `pro.referrals.index` | `App\Http\Controllers\Pro\ReferralController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro/referrals/accept/{token}` | `pro.referrals.accept` | `App\Http\Controllers\Pro\ReferralController@accept` | `web` |
| `POST` | `pro/referrals/invite` | `pro.referrals.invite` | `App\Http\Controllers\Pro\ReferralController@invite` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pro/referrals/invite/{invite}/resend` | `pro.referrals.resend` | `App\Http\Controllers\Pro\ReferralController@resend` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `pro/{slug}` | `therapist.show` | `App\Http\Controllers\PublicTherapistController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `pro/{slug}/bons-cadeaux/checkout` | `gift-vouchers.checkout.show` | `App\Http\Controllers\PublicGiftVoucherCheckoutController@show` | `web` |
| `POST` | `pro/{slug}/bons-cadeaux/checkout` | `gift-vouchers.checkout.store` | `App\Http\Controllers\PublicGiftVoucherCheckoutController@store` | `web` |
| `GET|HEAD` | `pro/{slug}/checkout` | `public.checkout.show` | `App\Http\Controllers\PublicCheckoutController@show` | `web` |
| `POST` | `pro/{slug}/checkout` | `public.checkout.store` | `App\Http\Controllers\PublicCheckoutController@store` | `web` |
| `GET|HEAD` | `pro/{slug}/packs/{pack}/checkout` | `packs.checkout.show` | `Closure` | `web` |
| `POST` | `pro/{slug}/packs/{pack}/checkout` | `packs.checkout.store` | `App\Http\Controllers\PublicPackCheckoutController@store` | `web` |
| `GET|HEAD` | `pro/{therapist}/article/{articleSlug}` | `pro.articles.show` | `App\Http\Controllers\TherapistArticleController@publicShow` | `web` |
| `GET|HEAD` | `pro/{therapist}/articles` | `pro.articles.index` | `App\Http\Controllers\TherapistArticleController@publicIndex` | `web` |
| `GET|HEAD` | `products` | `products.index` | `App\Http\Controllers\ProductController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `products` | `products.store` | `App\Http\Controllers\ProductController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `products/create` | `products.create` | `App\Http\Controllers\ProductController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `products/{product}` | `products.show` | `App\Http\Controllers\ProductController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:view,product` |
| `PUT` | `products/{product}` | `products.update` | `App\Http\Controllers\ProductController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:update,product` |
| `DELETE` | `products/{product}` | `products.destroy` | `App\Http\Controllers\ProductController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:delete,product` |
| `GET|HEAD` | `products/{product}/duplicate` | `products.duplicate` | `App\Http\Controllers\ProductController@duplicate` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `products/{product}/duplicate` | `products.storeDuplicate` | `App\Http\Controllers\ProductController@storeDuplicate` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `products/{product}/edit` | `products.edit` | `App\Http\Controllers\ProductController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:update,product` |
| `GET|HEAD` | `questionnaires` | `questionnaires.index` | `App\Http\Controllers\QuestionnaireController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `questionnaires` | `questionnaires.store` | `App\Http\Controllers\QuestionnaireController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `questionnaires/create` | `questionnaires.create` | `App\Http\Controllers\QuestionnaireController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `questionnaires/remplir/{token}` | `questionnaires.fill` | `App\Http\Controllers\QuestionnaireController@fill` | `web` |
| `POST` | `questionnaires/remplir/{token}/storeResponses` | `questionnaires.storeResponses` | `App\Http\Controllers\QuestionnaireController@storeResponses` | `web` |
| `GET|HEAD` | `questionnaires/responses/{id}` | `questionnaires.responses.show` | `App\Http\Controllers\QuestionnaireController@showResponse` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `questionnaires/send` | `questionnaires.send.show` | `App\Http\Controllers\QuestionnaireController@showSendQuestionnaire` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `questionnaires/{questionnaire}` | `questionnaires.show` | `App\Http\Controllers\QuestionnaireController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `questionnaires/{questionnaire}` | `questionnaires.update` | `App\Http\Controllers\QuestionnaireController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `questionnaires/{questionnaire}` | `questionnaires.destroy` | `App\Http\Controllers\QuestionnaireController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `questionnaires/{questionnaire}/edit` | `questionnaires.edit` | `App\Http\Controllers\QuestionnaireController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `questionnaires/{questionnaire}/questions/{question}` | `question.destroy` | `App\Http\Controllers\QuestionnaireController@destroyQuestion` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `questionnaires/{questionnaire}/send` | `questionnaires.send` | `App\Http\Controllers\QuestionnaireController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `receipts` | `receipts.store` | `App\Http\Controllers\ReceiptController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `receipts/create` | `receipts.create` | `App\Http\Controllers\ReceiptController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `receipts/{receipt}/reverse` | `receipts.reverse` | `App\Http\Controllers\ReceiptController@reverse` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |

### public_or_marketing

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET|HEAD` | `/` | `welcome` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `Formation-Pro` | `formation3` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `IntroductionAromatherapie` | `formation1` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `IntroductionSales` | `formation2` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `accounting/ca-monthly` | `receipts.caMonthly` | `App\Http\Controllers\ReceiptController@caMonthly` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `accounting/receipts` | `receipts.index` | `App\Http\Controllers\ReceiptController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `accounting/receipts/export` | `receipts.export` | `App\Http\Controllers\ReceiptController@exportCsv` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `aide/agenda/configurer-disponibilites` | `aide.agenda.configurer-disponibilites` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `aide/agenda/creer-un-atelier-ou-evenement` | `aide.agenda.creer-atelier-evenement` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `aide/agenda/creer-un-rendez-vous-en-ligne` | `aide.agenda.creer-rendez-vous` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `aide/agenda/duree-prestation-temps-de-pause` | `aide.agenda.duree-prestation-temps-de-pause` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `aide/agenda/gerer-indisponibilites` | `aide.agenda.gerer-indisponibilites` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `aide/agenda/synchroniser-calendrier` | `aide.agenda.synchroniser-calendrier` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `appointment-confirmation/{token}` | `appointments.showPatient` | `App\Http\Controllers\AppointmentController@showPatient` | `web, App\Http\Middleware\TrackPageViews` |
| `POST` | `appointment-confirmation/{token}/cancel` | `appointment.confirmation.cancel` | `App\Http\Controllers\AppointmentController@cancelFromMagicLink` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `appointment-ics/{token}` | `appointments.downloadICS` | `App\Http\Controllers\AppointmentController@downloadICS` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `article` | `blog.index` | `App\Http\Controllers\BlogPostController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `article/{slug}` | `blog.show` | `App\Http\Controllers\BlogPostController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `assistant` | `assistant.view` | `App\Http\Controllers\AssistantController@view` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `assistant/message` | `assistant.message` | `App\Http\Controllers\AssistantController@message` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `autocomplete/regions` | `autocomplete.regions` | `Closure` | `web` |
| `GET|HEAD` | `autocomplete/specialties` | `autocomplete.specialties` | `Closure` | `web` |
| `GET|HEAD` | `b/{token}` | `bookingLinks.create` | `App\Http\Controllers\AppointmentController@createByToken` | `web` |
| `POST` | `b/{token}` | `bookingLinks.store` | `App\Http\Controllers\AppointmentController@storeByToken` | `web` |
| `GET|HEAD` | `beta/brand` | `beta.brand` | `Closure` | `web` |
| `GET|HEAD` | `beta/editor` | `konva.editor` | `App\Http\Controllers\KonvaEditorController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `beta/editor/branding` | `konva.branding.update` | `App\Http\Controllers\KonvaEditorController@updateBranding` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `bons-cadeaux/checkout/cancel` | `gift-vouchers.checkout.cancel` | `App\Http\Controllers\PublicGiftVoucherCheckoutController@cancel` | `web` |
| `GET|HEAD` | `bons-cadeaux/checkout/success` | `gift-vouchers.checkout.success` | `App\Http\Controllers\PublicGiftVoucherCheckoutController@success` | `web` |
| `POST` | `book-appointment` | `appointments.storePatient` | `App\Http\Controllers\AppointmentController@storePatient` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `book-appointment/{therapist}` | `appointments.createPatient` | `App\Http\Controllers\AppointmentController@createPatient` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|POST|HEAD` | `broadcasting/auth` | `` | `Illuminate\Broadcasting\BroadcastController@authenticate` | `web` |
| `GET|HEAD` | `cgu` | `cgu` | `Closure` | `web` |
| `GET|HEAD` | `cgv` | `cgv` | `Closure` | `web` |
| `GET|HEAD` | `checkout/cancel` | `checkout.cancel` | `App\Http\Controllers\StripeController@cancel` | `web` |
| `GET|HEAD` | `checkout/success` | `checkout.success` | `App\Http\Controllers\StripeController@success` | `web` |
| `GET|HEAD` | `client_profiles` | `client_profiles.index` | `App\Http\Controllers\ClientProfileController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `client_profiles` | `client_profiles.store` | `App\Http\Controllers\ClientProfileController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/create` | `client_profiles.create` | `App\Http\Controllers\ClientProfileController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{clientProfile}` | `client_profiles.show` | `App\Http\Controllers\ClientProfileController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT` | `client_profiles/{clientProfile}` | `client_profiles.update` | `App\Http\Controllers\ClientProfileController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `client_profiles/{clientProfile}` | `client_profiles.destroy` | `App\Http\Controllers\ClientProfileController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{clientProfile}/conseils/send` | `client_profiles.conseils.sendform` | `App\Http\Controllers\ClientConseilController@sendForm` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `client_profiles/{clientProfile}/conseils/send` | `client_profiles.conseils.send` | `App\Http\Controllers\ClientConseilController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `client_profiles/{clientProfile}/edit` | `client_profiles.edit` | `App\Http\Controllers\ClientProfileController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{clientProfile}/files/{file}/download` | `client_profiles.files.download` | `App\Http\Controllers\ClientFileController@downloadForTherapist` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `client_profiles/{clientProfile}/invite` | `client.invite` | `App\Http\Controllers\ClientInviteController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `client_profiles/{clientProfile}/invoices` | `invoices.client` | `App\Http\Controllers\InvoiceController@clientInvoices` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `client_profiles/{clientProfile}/packs/assign` | `client_profiles.packs.assign` | `App\Http\Controllers\PackProductController@assignFromClientProfile` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{clientProfile}/session_notes` | `session_notes.index` | `App\Http\Controllers\SessionNoteController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `client_profiles/{clientProfile}/session_notes` | `session_notes.store` | `App\Http\Controllers\SessionNoteController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{clientProfile}/session_notes/create` | `session_notes.create` | `App\Http\Controllers\SessionNoteController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/files` | `client_profiles.files.index` | `App\Http\Controllers\ClientFileController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `client_profiles/{client_profile}/files` | `client_profiles.files.store` | `App\Http\Controllers\ClientFileController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/files/create` | `client_profiles.files.create` | `App\Http\Controllers\ClientFileController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/files/{file}` | `client_profiles.files.show` | `App\Http\Controllers\ClientFileController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT|PATCH` | `client_profiles/{client_profile}/files/{file}` | `client_profiles.files.update` | `App\Http\Controllers\ClientFileController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `client_profiles/{client_profile}/files/{file}` | `client_profiles.files.destroy` | `App\Http\Controllers\ClientFileController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/files/{file}/download` | `client_profiles.files.download` | `App\Http\Controllers\ClientFileController@download` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/files/{file}/edit` | `client_profiles.files.edit` | `App\Http\Controllers\ClientFileController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics` | `client_profiles.metrics.index` | `App\Http\Controllers\MetricController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `client_profiles/{client_profile}/metrics` | `client_profiles.metrics.store` | `App\Http\Controllers\MetricController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/create` | `client_profiles.metrics.create` | `App\Http\Controllers\MetricController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}` | `client_profiles.metrics.show` | `App\Http\Controllers\MetricController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT|PATCH` | `client_profiles/{client_profile}/metrics/{metric}` | `client_profiles.metrics.update` | `App\Http\Controllers\MetricController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `client_profiles/{client_profile}/metrics/{metric}` | `client_profiles.metrics.destroy` | `App\Http\Controllers\MetricController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}/edit` | `client_profiles.metrics.edit` | `App\Http\Controllers\MetricController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}/entries` | `client_profiles.metrics.entries.index` | `App\Http\Controllers\MetricEntryController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `client_profiles/{client_profile}/metrics/{metric}/entries` | `client_profiles.metrics.entries.store` | `App\Http\Controllers\MetricEntryController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}/entries/create` | `client_profiles.metrics.entries.create` | `App\Http\Controllers\MetricEntryController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}/entries/{metricEntry}` | `client_profiles.metrics.entries.show` | `App\Http\Controllers\MetricEntryController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT|PATCH` | `client_profiles/{client_profile}/metrics/{metric}/entries/{metricEntry}` | `client_profiles.metrics.entries.update` | `App\Http\Controllers\MetricEntryController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `client_profiles/{client_profile}/metrics/{metric}/entries/{metricEntry}` | `client_profiles.metrics.entries.destroy` | `App\Http\Controllers\MetricEntryController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `client_profiles/{client_profile}/metrics/{metric}/entries/{metricEntry}/edit` | `client_profiles.metrics.entries.edit` | `App\Http\Controllers\MetricEntryController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `communautes` | `communities.index` | `App\Http\Controllers\CommunityController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes` | `communities.store` | `App\Http\Controllers\CommunityController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `communautes/create` | `communities.create` | `App\Http\Controllers\CommunityController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `communautes/fichiers/{attachment}` | `communities.attachments.download` | `App\Http\Controllers\CommunityAttachmentController@downloadForPractitioner` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `communautes/{community}` | `communities.show` | `App\Http\Controllers\CommunityController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `communautes/{community}` | `communities.update` | `App\Http\Controllers\CommunityController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `communautes/{community}/edit` | `communities.edit` | `App\Http\Controllers\CommunityController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `communautes/{community}/gestion` | `communities.manage` | `App\Http\Controllers\CommunityController@manage` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes/{community}/membres` | `communities.members.store` | `App\Http\Controllers\CommunityMemberController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `communautes/{community}/membres/{member}` | `communities.members.destroy` | `App\Http\Controllers\CommunityMemberController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes/{community}/membres/{member}/relancer` | `communities.members.resend` | `App\Http\Controllers\CommunityMemberController@resendInvitation` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes/{community}/messages` | `communities.messages.store` | `App\Http\Controllers\CommunityMessageController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes/{community}/messages/{message}/epingler` | `communities.messages.pin` | `App\Http\Controllers\CommunityMessageController@pin` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `communautes/{community}/salons` | `communities.channels.store` | `App\Http\Controllers\CommunityChannelController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `communautes/{community}/salons/{channel}/epingler` | `communities.channels.unpin` | `App\Http\Controllers\CommunityMessageController@unpin` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `confirm-password` | `password.confirm` | `App\Http\Controllers\Auth\ConfirmablePasswordController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `confirm-password` | `` | `App\Http\Controllers\Auth\ConfirmablePasswordController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `connect/stripe` | `stripe.connect` | `App\Http\Controllers\StripeController@connect` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `connect/stripe/refresh` | `stripe.refresh` | `App\Http\Controllers\StripeController@refresh` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `connect/stripe/return` | `stripe.return` | `App\Http\Controllers\StripeController@return` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `conseil/view` | `public.conseil.view` | `App\Http\Controllers\ClientConseilController@viewConseil` | `web` |
| `GET|HEAD` | `conseils` | `conseils.index` | `App\Http\Controllers\ConseilController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `conseils` | `conseils.store` | `App\Http\Controllers\ConseilController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `conseils/create` | `conseils.create` | `App\Http\Controllers\ConseilController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `conseils/{conseil}` | `conseils.show` | `App\Http\Controllers\ConseilController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `conseils/{conseil}` | `conseils.update` | `App\Http\Controllers\ConseilController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `conseils/{conseil}` | `conseils.destroy` | `App\Http\Controllers\ConseilController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `conseils/{conseil}/edit` | `conseils.edit` | `App\Http\Controllers\ConseilController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `contact` | `contact.show` | `App\Http\Controllers\ContactController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `contact` | `contact.send` | `App\Http\Controllers\ContactController@send` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `contact/confirmation` | `contact.confirmation` | `App\Http\Controllers\ContactController@confirmation` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `corporate-clients` | `corporate-clients.index` | `App\Http\Controllers\CorporateClientController@index` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `POST` | `corporate-clients` | `corporate-clients.store` | `App\Http\Controllers\CorporateClientController@store` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `corporate-clients/create` | `corporate-clients.create` | `App\Http\Controllers\CorporateClientController@create` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `corporate-clients/{corporate_client}` | `corporate-clients.show` | `App\Http\Controllers\CorporateClientController@show` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `PUT|PATCH` | `corporate-clients/{corporate_client}` | `corporate-clients.update` | `App\Http\Controllers\CorporateClientController@update` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `DELETE` | `corporate-clients/{corporate_client}` | `corporate-clients.destroy` | `App\Http\Controllers\CorporateClientController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `corporate-clients/{corporate_client}/edit` | `corporate-clients.edit` | `App\Http\Controllers\CorporateClientController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `POST` | `create-checkout-session/{token}` | `checkout.create` | `App\Http\Controllers\StripeController@createCheckoutSession` | `web` |
| `GET|HEAD` | `dashboard` | `dashboard` | `Closure` | `web, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\EnsureEmailIsVerified` |
| `GET|HEAD` | `dashboard/client-profiles/{clientProfile}/messages/fetch` | `therapist.messages.fetch` | `App\Http\Controllers\ClientMessageController@fetchLatestTherapist` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `devis/{invoice}/pdf` | `invoices.quotePdf` | `App\Http\Controllers\InvoiceController@generateQuotePDF` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `docs/sign/{token}` | `documents.sign.form` | `App\Http\Controllers\DocumentSigningController@showForm` | `web` |
| `POST` | `docs/sign/{token}` | `documents.sign.submit` | `App\Http\Controllers\DocumentSigningController@submit` | `web` |
| `POST` | `email/verification-notification` | `verification.send` | `App\Http\Controllers\Auth\EmailVerificationNotificationController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Routing\Middleware\ThrottleRequests:6,1` |
| `GET|HEAD` | `emargements/{emargement}/download` | `emargement.download` | `App\Http\Controllers\EmargementController@download` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `emargements/{emargement}/resend` | `emargement.resend` | `App\Http\Controllers\EmargementController@resend` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `favorites/toggle/{type}/{id}` | `favorites.toggle` | `App\Http\Controllers\FavoriteController@toggle` | `web` |
| `GET|HEAD` | `fonctionnalites` | `features.index` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/agenda` | `features.agenda` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/dossiers-clients` | `features.dossiers` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/facturation` | `features.facturation` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/paiements` | `features.paiements` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/portail-pro` | `features.portailpro` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `fonctionnalites/questionnaires` | `features.questionnaires` | `Illuminate\Routing\ViewController` | `web` |
| `GET|HEAD` | `forgot-password` | `password.request` | `App\Http\Controllers\Auth\PasswordResetLinkController@create` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `forgot-password` | `password.email` | `App\Http\Controllers\Auth\PasswordResetLinkController@store` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `formation/Therapeute-Sales{numero}` | `formation.show1` | `App\Http\Controllers\FormationController@show1` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `formation/Utilisateur-Aromatherapie{numero}` | `formation.show` | `App\Http\Controllers\FormationController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `formations/{digitalTraining}` | `digital-trainings.public.show` | `App\Http\Controllers\DigitalTrainingController@publicShow` | `web` |
| `POST` | `formations/{digitalTraining}/acces-gratuit` | `digital-trainings.public.free-access.store` | `App\Http\Controllers\DigitalTrainingEnrollmentController@storeFreeAccess` | `web` |
| `POST` | `formations/{digitalTraining}/acces-libre` | `digital-trainings.public.open-access.store` | `App\Http\Controllers\DigitalTrainingEnrollmentController@storeOpenFreeAccess` | `web` |
| `GET|HEAD` | `google/connect` | `google.connect` | `App\Http\Controllers\GoogleCalendarController@redirect` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `google/disconnect` | `google.disconnect` | `App\Http\Controllers\GoogleCalendarController@disconnect` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `google/oauth2callback` | `google.callback` | `App\Http\Controllers\GoogleCalendarController@callback` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `huilehe/proprietes` | `huilehes.showhuilehepropriete` | `App\Http\Controllers\HuileHEController@showhuilehepropriete` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `huilehes` | `huilehes.index` | `App\Http\Controllers\HuileHEController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `huilehes/{slug}` | `huilehes.show` | `App\Http\Controllers\HuileHEController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `huilehvs` | `huilehvs.index` | `App\Http\Controllers\HuileHVController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `huilehvs/{slug}` | `huilehvs.show` | `App\Http\Controllers\HuileHVController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `inventory_items` | `inventory_items.index` | `App\Http\Controllers\InventoryItemController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `inventory_items` | `inventory_items.store` | `App\Http\Controllers\InventoryItemController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `inventory_items/create` | `inventory_items.create` | `App\Http\Controllers\InventoryItemController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `inventory_items/{inventoryItem}/consume` | `inventory_items.consume` | `App\Http\Controllers\InventoryItemController@consume` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `inventory_items/{inventory_item}` | `inventory_items.show` | `App\Http\Controllers\InventoryItemController@show` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `inventory_items/{inventory_item}` | `inventory_items.update` | `App\Http\Controllers\InventoryItemController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `inventory_items/{inventory_item}` | `inventory_items.destroy` | `App\Http\Controllers\InventoryItemController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `inventory_items/{inventory_item}/edit` | `inventory_items.edit` | `App\Http\Controllers\InventoryItemController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `license-tiers/pricing` | `license-tiers.pricing` | `App\Http\Controllers\LicenseTierController@pricing` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `livewire/livewire.js` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile` | `` |
| `GET|HEAD` | `livewire/livewire.min.js.map` | `` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@maps` | `` |
| `GET|HEAD` | `livewire/preview-file/{filename}` | `livewire.preview-file` | `Livewire\Features\SupportFileUploads\FilePreviewController@handle` | `web` |
| `POST` | `livewire/update` | `livewire.update` | `Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate` | `web` |
| `POST` | `livewire/upload-file` | `livewire.upload-file` | `Livewire\Features\SupportFileUploads\FileUploadController@handle` | `web, Illuminate\Routing\Middleware\ThrottleRequests:60,1` |
| `GET|HEAD` | `login` | `login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@choose` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `login` | `` | `App\Http\Controllers\Auth\AuthenticatedSessionController@store` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `login/praticien` | `login.practitioner` | `App\Http\Controllers\Auth\AuthenticatedSessionController@create` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `logout` | `logout` | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `meetings/confirmation` | `meetings.confirmation` | `App\Http\Controllers\MeetingController@confirmation` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `meetings/create` | `meetings.create` | `App\Http\Controllers\MeetingController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `meetings/store` | `meetings.store` | `App\Http\Controllers\MeetingController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `messages/{clientProfile}/from-therapist` | `messages.therapist.store` | `App\Http\Controllers\ClientMessageController@storeTherapist` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `metiers/naturopathe` | `metiers.naturopathe` | `Closure` | `web` |
| `GET|HEAD` | `metiers/sophrologue` | `metiers.sophrologue` | `Closure` | `web` |
| `GET|HEAD` | `nos-practiciens` | `nos-practiciens` | `Closure` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `notifications` | `notifications.index` | `App\Http\Controllers\NotificationController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `notifications/fetch` | `notifications.fetch` | `App\Http\Controllers\NotificationController@fetch` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `notifications/mark-all-as-read` | `notifications.markAllAsRead` | `App\Http\Controllers\NotificationController@markAllAsRead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `notifications/mark-as-read/{id}` | `notifications.markAsRead` | `App\Http\Controllers\NotificationController@markAsRead` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `onboarding/referral-done` | `onboarding.referralDone` | `App\Http\Controllers\DashboardController@markReferralOnboardingDone` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `onboarding/skip-step3` | `onboarding.skipStep3` | `App\Http\Controllers\DashboardController@skipStep3` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `onboarding/skip-step4` | `onboarding.skipStep4` | `App\Http\Controllers\DashboardController@skipStep4` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `pack-purchases/{packPurchase}/revoke` | `pack-purchases.revoke` | `App\Http\Controllers\PackProductController@revokePurchase` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `pack-purchases/{packPurchase}/subscription/cancel` | `pack-purchases.subscription.cancel` | `App\Http\Controllers\PackPurchaseSubscriptionController@cancel` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `packs/checkout/cancel` | `packs.checkout.cancel` | `App\Http\Controllers\PublicPackCheckoutController@cancel` | `web` |
| `GET|HEAD` | `packs/checkout/success` | `packs.checkout.success` | `App\Http\Controllers\PublicPackCheckoutController@success` | `web` |
| `PUT` | `password` | `password.update` | `App\Http\Controllers\Auth\PasswordController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `practice-location-invites/{invite}/cancel` | `practice-locations.invites.cancel` | `App\Http\Controllers\PracticeLocationInviteController@cancel` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `practice-location-invites/{token}` | `practice-locations.invites.show` | `App\Http\Controllers\PracticeLocationInviteController@show` | `web` |
| `POST` | `practice-location-invites/{token}/accept` | `practice-locations.invites.accept` | `App\Http\Controllers\PracticeLocationInviteController@accept` | `web` |
| `POST` | `practice-location-invites/{token}/decline` | `practice-locations.invites.decline` | `App\Http\Controllers\PracticeLocationInviteController@decline` | `web` |
| `GET|HEAD` | `practicien-{specialty}` | `therapists.filter.specialty` | `App\Http\Controllers\TherapistSearchController@filterBySpecialty` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `practicien-{specialty}-region-{region}` | `therapists.filter.specialty-region` | `App\Http\Controllers\TherapistSearchController@filterBySpecialtyRegion` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `privacy-policy` | `privacypolicy` | `Closure` | `web` |
| `GET|HEAD` | `profile` | `profile.edit` | `App\Http\Controllers\ProfileController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PATCH` | `profile` | `profile.update` | `App\Http\Controllers\ProfileController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `profile` | `profile.destroy` | `App\Http\Controllers\ProfileController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `profile/company-info` | `profile.editCompanyInfo` | `App\Http\Controllers\ProfileController@editCompanyInfo` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `PUT` | `profile/company-info` | `profile.updateCompanyInfo` | `App\Http\Controllers\ProfileController@updateCompanyInfo` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `profile/license` | `profile.license` | `App\Http\Controllers\ProfileController@license` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `quotes/{id}` | `invoices.showQuote` | `App\Http\Controllers\InvoiceController@showQuote` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `PUT` | `quotes/{quote}` | `invoices.updateQuote` | `App\Http\Controllers\InvoiceController@updateQuote` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `quotes/{quote}/edit` | `invoices.editQuote` | `App\Http\Controllers\InvoiceController@editQuote` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `POST` | `quotes/{quote}/send-email` | `quotes.send.email` | `App\Http\Controllers\InvoiceController@sendQuoteEmail` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `PATCH` | `quotes/{quote}/status` | `quotes.updateStatus` | `App\Http\Controllers\InvoiceController@updateQuoteStatus` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\Invoice` |
| `GET|HEAD` | `recettes` | `recettes.index` | `App\Http\Controllers\RecetteController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `recettes/{slug}` | `recettes.show` | `App\Http\Controllers\RecetteController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|POST|HEAD` | `recherche-practicien` | `therapists.search` | `App\Http\Controllers\TherapistSearchController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `region-{region}` | `therapists.filter.region` | `App\Http\Controllers\TherapistSearchController@filterByRegion` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `register` | `register` | `App\Http\Controllers\Auth\RegisteredUserController@create` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `register` | `` | `App\Http\Controllers\Auth\RegisteredUserController@store` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `register-formation` | `register-formation` | `App\Http\Controllers\Auth\RegisteredUserController@createformation` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `register-formation` | `` | `App\Http\Controllers\Auth\RegisteredUserController@storeformation` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `POST` | `register-pro` | `` | `App\Http\Controllers\Auth\RegisteredUserController@storepro` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `register-pro/{extra?}` | `register-pro` | `App\Http\Controllers\Auth\RegisteredUserController@createpro` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `reservations/payment/cancel` | `reservations.payment_cancel` | `App\Http\Controllers\ReservationController@paymentCancel` | `web` |
| `GET|HEAD` | `reservations/payment/success` | `reservations.payment_success` | `App\Http\Controllers\ReservationController@paymentSuccess` | `web` |
| `DELETE` | `reservations/{id}` | `reservations.destroy` | `App\Http\Controllers\ReservationController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `reset-password` | `password.store` | `App\Http\Controllers\Auth\NewPasswordController@store` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `reset-password/{token}` | `password.reset` | `App\Http\Controllers\Auth\NewPasswordController@create` | `web, Illuminate\Auth\Middleware\RedirectIfAuthenticated, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `sanctum/csrf-cookie` | `sanctum.csrf-cookie` | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` | `web` |
| `GET|HEAD` | `search` | `search` | `App\Http\Controllers\SearchController@search` | `web` |
| `GET|HEAD` | `session-note-templates` | `session-note-templates.index` | `App\Http\Controllers\SessionNoteTemplateController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `POST` | `session-note-templates` | `session-note-templates.store` | `App\Http\Controllers\SessionNoteTemplateController@store` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `session-note-templates/create` | `session-note-templates.create` | `App\Http\Controllers\SessionNoteTemplateController@create` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `session-note-templates/{session_note_template}` | `session-note-templates.show` | `App\Http\Controllers\SessionNoteTemplateController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT|PATCH` | `session-note-templates/{session_note_template}` | `session-note-templates.update` | `App\Http\Controllers\SessionNoteTemplateController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `session-note-templates/{session_note_template}` | `session-note-templates.destroy` | `App\Http\Controllers\SessionNoteTemplateController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `session-note-templates/{session_note_template}/edit` | `session-note-templates.edit` | `App\Http\Controllers\SessionNoteTemplateController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `session_notes/{sessionNote}` | `session_notes.show` | `App\Http\Controllers\SessionNoteController@show` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `PUT` | `session_notes/{sessionNote}` | `session_notes.update` | `App\Http\Controllers\SessionNoteController@update` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `DELETE` | `session_notes/{sessionNote}` | `session_notes.destroy` | `App\Http\Controllers\SessionNoteController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `session_notes/{sessionNote}/edit` | `session_notes.edit` | `App\Http\Controllers\SessionNoteController@edit` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Auth\Middleware\Authorize:viewAny,App\Models\ClientProfile` |
| `GET|HEAD` | `sign/{token}` | `emargement.sign.form` | `App\Http\Controllers\EmargementController@showSignForm` | `web` |
| `POST` | `sign/{token}` | `emargement.sign.submit` | `App\Http\Controllers\EmargementController@submitSignature` | `web` |
| `POST` | `signing/{signing}/resend` | `documents.resend` | `App\Http\Controllers\DocumentSigningController@resend` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `sitemap` | `` | `App\Http\Controllers\SitemapController@index` | `web` |
| `GET|HEAD` | `sitemap-practicien.xml` | `sitemap-test` | `Closure` | `web` |
| `GET|HEAD` | `special-availabilities` | `special-availabilities.index` | `App\Http\Controllers\SpecialAvailabilityController@index` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `special-availabilities` | `special-availabilities.store` | `App\Http\Controllers\SpecialAvailabilityController@store` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `special-availabilities/create` | `special-availabilities.create` | `App\Http\Controllers\SpecialAvailabilityController@create` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT|PATCH` | `special-availabilities/{special_availability}` | `special-availabilities.update` | `App\Http\Controllers\SpecialAvailabilityController@update` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `special-availabilities/{special_availability}` | `special-availabilities.destroy` | `App\Http\Controllers\SpecialAvailabilityController@destroy` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `special-availabilities/{special_availability}/edit` | `special-availabilities.edit` | `App\Http\Controllers\SpecialAvailabilityController@edit` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `stripe/dashboard` | `stripe.dashboard` | `App\Http\Controllers\StripeController@redirectToStripeDashboard` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `stripe/webhook` | `stripe.webhook` | `App\Http\Controllers\StripeController@handleWebhook` | `web` |
| `POST` | `super-pdp/connect` | `super-pdp.connect` | `App\Http\Controllers\SuperPdpController@connect` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `DELETE` | `super-pdp/disconnect` | `super-pdp.disconnect` | `App\Http\Controllers\SuperPdpController@disconnect` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `super-pdp/oauth/callback` | `super-pdp.oauth.callback` | `App\Http\Controllers\SuperPdpController@callback` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `PATCH` | `super-pdp/preferences` | `super-pdp.preferences.update` | `App\Http\Controllers\SuperPdpController@updatePreferences` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `super-pdp/received-invoices` | `super-pdp.received-invoices.index` | `App\Http\Controllers\SuperPdpReceivedInvoiceController@index` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `super-pdp/received-invoices/{receivedInvoice}/download` | `super-pdp.received-invoices.download` | `App\Http\Controllers\SuperPdpReceivedInvoiceController@download` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `tallstackui/script/{file?}` | `tallstackui.script` | `TallStackUi\Foundation\Http\Controllers\TallStackUiAssetsController@script` | `` |
| `GET|HEAD` | `tallstackui/style/{file?}` | `tallstackui.style` | `TallStackUi\Foundation\Http\Controllers\TallStackUiAssetsController@style` | `` |
| `GET|HEAD` | `test-certificate` | `generateTestCertificate` | `App\Http\Controllers\TestCertificateController@generateTestCertificate` | `web` |
| `GET|HEAD` | `testimonials/submit/{token}` | `testimonials.submit` | `App\Http\Controllers\TestimonialController@showSubmitForm` | `web` |
| `POST` | `testimonials/submit/{token}` | `testimonials.submit.post` | `App\Http\Controllers\TestimonialController@submit` | `web` |
| `GET|HEAD` | `testimonials/thankyou` | `testimonials.thankyou` | `App\Http\Controllers\TestimonialController@thankYou` | `web` |
| `GET|HEAD` | `thank-you` | `thank_you` | `Closure` | `web` |
| `GET|HEAD` | `therapist/stripe` | `therapist.stripe` | `App\Http\Controllers\StripeController@portal` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `therapist/{slug}/request-info` | `therapist.sendInformationRequest` | `App\Http\Controllers\PublicTherapistController@sendInformationRequest` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `tisanes` | `tisanes.index` | `App\Http\Controllers\TisaneController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `tisanes/{slug}` | `tisanes.show` | `App\Http\Controllers\TisaneController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `tools/konva` | `konva.editor.legacy` | `Closure` | `web` |
| `GET|HEAD` | `training-access/{token}` | `digital-trainings.access.show` | `App\Http\Controllers\PublicTrainingAccessController@show` | `web` |
| `POST` | `training-access/{token}/blocks/{block}/comments` | `digital-trainings.access.comments.store` | `App\Http\Controllers\PublicTrainingCommentController@store` | `web` |
| `POST` | `training-access/{token}/blocks/{block}/complete` | `digital-trainings.access.blocks.complete` | `App\Http\Controllers\PublicTrainingAccessController@markBlockCompleted` | `web` |
| `GET|HEAD` | `training-access/{token}/blocks/{block}/download` | `digital-trainings.access.blocks.download` | `App\Http\Controllers\PublicTrainingAccessController@downloadBlockFile` | `web` |
| `POST` | `training-access/{token}/blocks/{block}/viewed` | `digital-trainings.access.blocks.viewed` | `App\Http\Controllers\PublicTrainingAccessController@markBlockViewed` | `web` |
| `POST` | `training-access/{token}/complete` | `digital-trainings.access.complete` | `App\Http\Controllers\PublicTrainingAccessController@markCompleted` | `web` |
| `GET|HEAD` | `trainings` | `trainings.index` | `App\Http\Controllers\TrainingController@index` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `trainings/{training}` | `trainings.show` | `App\Http\Controllers\TrainingController@show` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `trainings/{training}/lesson/{lesson}` | `trainings.show-lesson` | `App\Http\Controllers\TrainingController@showLesson` | `web, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `unavailability` | `unavailabilities.index` | `App\Http\Controllers\AppointmentController@indexUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `unavailability/create` | `unavailabilities.create` | `App\Http\Controllers\AppointmentController@createUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `POST` | `unavailability/store` | `unavailabilities.store` | `App\Http\Controllers\AppointmentController@storeUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `PUT` | `unavailability/{id}` | `unavailabilities.update` | `App\Http\Controllers\AppointmentController@updateUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `DELETE` | `unavailability/{id}` | `unavailabilities.destroy` | `App\Http\Controllers\AppointmentController@destroyUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `unavailability/{id}/edit` | `unavailabilities.edit` | `App\Http\Controllers\AppointmentController@editUnavailability` | `web, Illuminate\Auth\Middleware\Authenticate` |
| `GET|HEAD` | `up` | `` | `Closure` | `` |
| `GET|HEAD` | `upgrade/license` | `upgrade.license` | `App\Http\Controllers\UserLicenseController@showUpgradePage` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `POST` | `upgrade/license/process` | `upgrade.license.process` | `App\Http\Controllers\UserLicenseController@processLicenseUpgrade` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `verify-email` | `verification.notice` | `App\Http\Controllers\Auth\EmailVerificationPromptController` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews` |
| `GET|HEAD` | `verify-email/{id}/{hash}` | `verification.verify` | `App\Http\Controllers\Auth\VerifyEmailController` | `web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\TrackPageViews, Illuminate\Routing\Middleware\ValidateSignature, Illuminate\Routing\Middleware\ThrottleRequests:6,1` |
| `GET|HEAD` | `webrtc-demo` | `` | `Closure` | `web` |
| `GET|HEAD` | `webrtc/{room}` | `webrtc.room` | `App\Http\Controllers\WebRTCController@room` | `web` |

## Model Relationships

| Model | Relationships detected | File |
| --- | --- | --- |
| `Appointment` | `practiceLocation` belongsTo `\App\Models\PracticeLocation`<br>`product` belongsTo `Product`<br>`user` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile`<br>`meeting` hasOne `Meeting`<br>`invoice` hasOne `Invoice`<br>`giftVoucher` belongsTo `GiftVoucher` | `app/Models/Appointment.php` |
| `AssistantSession` | None detected | `app/Models/AssistantSession.php` |
| `Audience` | `user` belongsTo `User`<br>`clients` belongsToMany `ClientProfile` | `app/Models/Audience.php` |
| `Availability` | `user` belongsTo `User`<br>`products` belongsToMany `Product`<br>`practiceLocation` belongsTo `\App\Models\PracticeLocation` | `app/Models/Availability.php` |
| `BlogPost` | None detected | `app/Models/BlogPost.php` |
| `BookingLink` | `user` belongsTo `User`<br>`appointments` hasMany `Appointment` | `app/Models/BookingLink.php` |
| `Chapter` | `training` belongsTo `Training`<br>`lessons` hasMany `Lesson` | `app/Models/Chapter.php` |
| `ClientFile` | `clientProfile` belongsTo `ClientProfile` | `app/Models/ClientFile.php` |
| `ClientProfile` | `user` belongsTo `User`<br>`appointments` hasMany `Appointment`<br>`sessionNotes` hasMany `SessionNote`<br>`invoices` hasMany `Invoice`<br>`testimonialRequests` hasMany `TestimonialRequest`<br>`testimonials` hasMany `Testimonial`<br>`conseilsSent` belongsToMany `\App\Models\Conseil`<br>`metrics` hasMany `Metric`<br>`clientFiles` hasMany `ClientFile`<br>`messages` hasMany `\App\Models\Message`<br>`company` belongsTo `CorporateClient`<br>`trainingEnrollments` hasMany `DigitalTrainingEnrollment`<br>`communityMemberships` hasMany `CommunityMember`<br>`communityGroups` belongsToMany `CommunityGroup` | `app/Models/ClientProfile.php` |
| `CommunityChannel` | `group` belongsTo `CommunityGroup`<br>`messages` hasMany `CommunityMessage`<br>`pinnedMessage` belongsTo `CommunityMessage` | `app/Models/CommunityChannel.php` |
| `CommunityGroup` | `user` belongsTo `User`<br>`channels` hasMany `CommunityChannel`<br>`members` hasMany `CommunityMember`<br>`messages` hasMany `CommunityMessage`<br>`participantProfiles` belongsToMany `ClientProfile` | `app/Models/CommunityGroup.php` |
| `CommunityMember` | `group` belongsTo `CommunityGroup`<br>`clientProfile` belongsTo `ClientProfile` | `app/Models/CommunityMember.php` |
| `CommunityMessage` | `group` belongsTo `CommunityGroup`<br>`channel` belongsTo `CommunityChannel`<br>`user` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile`<br>`attachments` hasMany `CommunityMessageAttachment` | `app/Models/CommunityMessage.php` |
| `CommunityMessageAttachment` | `message` belongsTo `CommunityMessage` | `app/Models/CommunityMessageAttachment.php` |
| `Conseil` | `user` belongsTo `User`<br>`clients` belongsToMany `\App\Models\ClientProfile` | `app/Models/Conseil.php` |
| `CorporateClient` | `user` belongsTo `User`<br>`clientProfiles` hasMany `ClientProfile` | `app/Models/CorporateClient.php` |
| `CrmLead` | `activities` hasMany `CrmLeadActivity`<br>`creator` belongsTo `User`<br>`owner` belongsTo `User` | `app/Models/CrmLead.php` |
| `CrmLeadActivity` | `lead` belongsTo `CrmLead`<br>`user` belongsTo `User` | `app/Models/CrmLeadActivity.php` |
| `DesignTemplate` | None detected | `app/Models/DesignTemplate.php` |
| `DigitalTraining` | `user` belongsTo `User`<br>`product` belongsTo `Product`<br>`modules` hasMany `TrainingModule`<br>`enrollments` hasMany `DigitalTrainingEnrollment`<br>`comments` hasMany `DigitalTrainingBlockComment` | `app/Models/DigitalTraining.php` |
| `DigitalTrainingBlockComment` | `parent` belongsTo `self`<br>`replies` hasMany `self`<br>`training` belongsTo `DigitalTraining`<br>`module` belongsTo `TrainingModule`<br>`block` belongsTo `TrainingBlock`<br>`enrollment` belongsTo `DigitalTrainingEnrollment`<br>`clientProfile` belongsTo `ClientProfile` | `app/Models/DigitalTrainingBlockComment.php` |
| `DigitalTrainingEnrollment` | `training` belongsTo `DigitalTraining`<br>`clientProfile` belongsTo `ClientProfile`<br>`comments` hasMany `DigitalTrainingBlockComment` | `app/Models/DigitalTrainingEnrollment.php` |
| `Document` | `owner` belongsTo `User` | `app/Models/Document.php` |
| `DocumentSignEvent` | `document` belongsTo `Document` | `app/Models/DocumentSignEvent.php` |
| `DocumentSigning` | `document` belongsTo `Document` | `app/Models/DocumentSigning.php` |
| `EmailTemplate` | None detected | `app/Models/EmailTemplate.php` |
| `Emargement` | `appointment` belongsTo `Appointment` | `app/Models/Emargement.php` |
| `Event` | `user` belongsTo `User`<br>`associatedProduct` belongsTo `Product`<br>`reservations` hasMany `Reservation` | `app/Models/Event.php` |
| `FacebookMetric` | None detected | `app/Models/FacebookMetric.php` |
| `Favorite` | `favoritable` morphTo | `app/Models/Favorite.php` |
| `GiftVoucher` | `therapist` belongsTo `User`<br>`redemptions` hasMany `GiftVoucherRedemption`<br>`saleInvoice` belongsTo `Invoice` | `app/Models/GiftVoucher.php` |
| `GiftVoucherOrder` | `therapist` belongsTo `User`<br>`voucher` belongsTo `GiftVoucher`<br>`saleInvoice` belongsTo `Invoice` | `app/Models/GiftVoucherOrder.php` |
| `GiftVoucherRedemption` | `voucher` belongsTo `GiftVoucher`<br>`therapist` belongsTo `User`<br>`appointment` belongsTo `Appointment`<br>`invoice` belongsTo `Invoice` | `app/Models/GiftVoucherRedemption.php` |
| `GoogleBusinessAccount` | `user` belongsTo `User` | `app/Models/GoogleBusinessAccount.php` |
| `HuileHE` | `favorites` morphMany `Favorite` | `app/Models/HuileHE.php` |
| `HuileHV` | `favorites` morphMany `Favorite` | `app/Models/HuileHV.php` |
| `InformationRequest` | `therapist` belongsTo `User` | `app/Models/InformationRequest.php` |
| `InventoryItem` | None detected | `app/Models/InventoryItem.php` |
| `Invoice` | `user` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile`<br>`items` hasMany `InvoiceItem`<br>`appointment` belongsTo `Appointment`<br>`receipts` hasMany `Receipt`<br>`corporateClient` belongsTo `CorporateClient`<br>`packPurchase` belongsTo `PackPurchase` | `app/Models/Invoice.php` |
| `InvoiceItem` | `product` belongsTo `Product`<br>`inventoryItem` belongsTo `InventoryItem` | `app/Models/InvoiceItem.php` |
| `Lesson` | `chapter` belongsTo `Chapter` | `app/Models/Lesson.php` |
| `LicenseHistory` | `user` belongsTo `User`<br>`licenseTier` belongsTo `LicenseTier` | `app/Models/LicenseHistory.php` |
| `LicenseTier` | `userLicenses` hasMany `UserLicense`<br>`licenseHistories` hasMany `LicenseHistory` | `app/Models/LicenseTier.php` |
| `MarketingEmail` | None detected | `app/Models/MarketingEmail.php` |
| `Meeting` | `appointment` belongsTo `Appointment` | `app/Models/Meeting.php` |
| `Message` | `clientProfile` belongsTo `ClientProfile`<br>`user` belongsTo `User` | `app/Models/Message.php` |
| `Metric` | `client` belongsTo `ClientProfile`<br>`entries` hasMany `MetricEntry`<br>`clientProfile` belongsTo `ClientProfile` | `app/Models/Metric.php` |
| `MetricEntry` | `metric` belongsTo `Metric` | `app/Models/MetricEntry.php` |
| `Milestone` | None detected | `app/Models/Milestone.php` |
| `Newsletter` | `user` belongsTo `User`<br>`recipients` hasMany `NewsletterRecipient`<br>`audience` belongsTo `Audience` | `app/Models/Newsletter.php` |
| `NewsletterMonthlyUsage` | None detected | `app/Models/NewsletterMonthlyUsage.php` |
| `NewsletterOptOut` | `therapist` belongsTo `User`<br>`recipient` belongsTo `NewsletterRecipient` | `app/Models/NewsletterOptOut.php` |
| `NewsletterRecipient` | `newsletter` belongsTo `Newsletter`<br>`clientProfile` belongsTo `ClientProfile`<br>`user` belongsTo `User` | `app/Models/NewsletterRecipient.php` |
| `PackProduct` | `user` belongsTo `User`<br>`items` hasMany `PackProductItem`<br>`purchases` hasMany `PackPurchase` | `app/Models/PackProduct.php` |
| `PackProductItem` | `pack` belongsTo `PackProduct`<br>`product` belongsTo `Product` | `app/Models/PackProductItem.php` |
| `PackPurchase` | `user` belongsTo `User`<br>`pack` belongsTo `PackProduct`<br>`clientProfile` belongsTo `ClientProfile`<br>`items` hasMany `PackPurchaseItem`<br>`installments` hasMany `PurchaseInstallment`<br>`digitalTraining` belongsTo `DigitalTraining`<br>`invoice` hasOne `Invoice` | `app/Models/PackPurchase.php` |
| `PackPurchaseItem` | `purchase` belongsTo `PackPurchase`<br>`product` belongsTo `Product` | `app/Models/PackPurchaseItem.php` |
| `PageViewLog` | None detected | `app/Models/PageViewLog.php` |
| `PracticeLocation` | `user` belongsTo `User`<br>`owner` belongsTo `User`<br>`availabilities` hasMany `Availability`<br>`appointments` hasMany `Appointment`<br>`memberships` hasMany `PracticeLocationMember`<br>`members` belongsToMany `User`<br>`invites` hasMany `PracticeLocationInvite`<br>`pendingInvites` hasMany `PracticeLocationInvite` | `app/Models/PracticeLocation.php` |
| `PracticeLocationInvite` | `practiceLocation` belongsTo `PracticeLocation`<br>`invitedUser` belongsTo `User`<br>`invitedBy` belongsTo `User` | `app/Models/PracticeLocationInvite.php` |
| `PracticeLocationMember` | `practiceLocation` belongsTo `PracticeLocation`<br>`user` belongsTo `User`<br>`addedBy` belongsTo `User` | `app/Models/PracticeLocationMember.php` |
| `Product` | `user` belongsTo `User`<br>`invoiceItems` hasMany `InvoiceItem`<br>`bookingQuestionnaire` belongsTo `Questionnaire`<br>`availabilities` belongsToMany `Availability` | `app/Models/Product.php` |
| `PurchaseInstallment` | `purchase` belongsTo `PackPurchase` | `app/Models/PurchaseInstallment.php` |
| `Question` | `questionnaire` belongsTo `Questionnaire` | `app/Models/Question.php` |
| `Questionnaire` | `questions` hasMany `Question`<br>`user` belongsTo `User` | `app/Models/Questionnaire.php` |
| `Receipt` | `original` belongsTo `self`<br>`reversals` hasMany `self`<br>`invoice` belongsTo `Invoice`<br>`user` belongsTo `User` | `app/Models/Receipt.php` |
| `Recette` | `favorites` morphMany `Favorite` | `app/Models/Recette.php` |
| `ReferralCode` | `user` belongsTo `User` | `app/Models/ReferralCode.php` |
| `ReferralInvite` | `referrer` belongsTo `User`<br>`invitedUser` belongsTo `User` | `app/Models/ReferralInvite.php` |
| `Reservation` | `event` belongsTo `Event` | `app/Models/Reservation.php` |
| `Response` | `questionnaire` belongsTo `Questionnaire`<br>`clientProfile` belongsTo `ClientProfile`<br>`appointment` belongsTo `Appointment` | `app/Models/Response.php` |
| `SessionNote` | `user` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile`<br>`template` belongsTo `SessionNoteTemplate` | `app/Models/SessionNote.php` |
| `SessionNoteTemplate` | `user` belongsTo `User` | `app/Models/SessionNoteTemplate.php` |
| `SpecialAvailability` | `user` belongsTo `User`<br>`products` belongsToMany `Product`<br>`practiceLocation` belongsTo `\App\Models\PracticeLocation` | `app/Models/SpecialAvailability.php` |
| `StripeFinanceBalanceTransaction` | None detected | `app/Models/StripeFinanceBalanceTransaction.php` |
| `StripeFinanceCoupon` | None detected | `app/Models/StripeFinanceCoupon.php` |
| `StripeFinanceCustomer` | `user` belongsTo `User`<br>`subscriptions` hasMany `StripeFinanceSubscription`<br>`invoices` hasMany `StripeFinanceInvoice`<br>`notes` hasMany `StripeFinanceNote` | `app/Models/StripeFinanceCustomer.php` |
| `StripeFinanceForecastAssumption` | None detected | `app/Models/StripeFinanceForecastAssumption.php` |
| `StripeFinanceInvoice` | `customer` belongsTo `StripeFinanceCustomer`<br>`subscription` belongsTo `StripeFinanceSubscription` | `app/Models/StripeFinanceInvoice.php` |
| `StripeFinanceNote` | `customer` belongsTo `StripeFinanceCustomer`<br>`subscription` belongsTo `StripeFinanceSubscription`<br>`creator` belongsTo `User` | `app/Models/StripeFinanceNote.php` |
| `StripeFinancePayment` | None detected | `app/Models/StripeFinancePayment.php` |
| `StripeFinancePayout` | None detected | `app/Models/StripeFinancePayout.php` |
| `StripeFinancePrice` | None detected | `app/Models/StripeFinancePrice.php` |
| `StripeFinanceProduct` | None detected | `app/Models/StripeFinanceProduct.php` |
| `StripeFinancePromotionCode` | None detected | `app/Models/StripeFinancePromotionCode.php` |
| `StripeFinanceSubscription` | `customer` belongsTo `StripeFinanceCustomer`<br>`user` belongsTo `User`<br>`invoices` hasMany `StripeFinanceInvoice`<br>`latestInvoice` hasOne `StripeFinanceInvoice`<br>`notes` hasMany `StripeFinanceNote` | `app/Models/StripeFinanceSubscription.php` |
| `StripeFinanceSyncRun` | None detected | `app/Models/StripeFinanceSyncRun.php` |
| `StripeFinanceUpcomingInvoice` | `customer` belongsTo `StripeFinanceCustomer`<br>`subscription` belongsTo `StripeFinanceSubscription` | `app/Models/StripeFinanceUpcomingInvoice.php` |
| `StripeWebhookEvent` | None detected | `app/Models/StripeWebhookEvent.php` |
| `SuperPdpConnection` | `user` belongsTo `User`<br>`receivedInvoices` hasMany `SuperPdpReceivedInvoice` | `app/Models/SuperPdpConnection.php` |
| `SuperPdpReceivedInvoice` | `connection` belongsTo `SuperPdpConnection`<br>`user` belongsTo `User` | `app/Models/SuperPdpReceivedInvoice.php` |
| `Testimonial` | `testimonialRequest` belongsTo `TestimonialRequest`<br>`therapist` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile` | `app/Models/Testimonial.php` |
| `TestimonialRequest` | `therapist` belongsTo `User`<br>`clientProfile` belongsTo `ClientProfile`<br>`testimonial` hasOne `Testimonial` | `app/Models/TestimonialRequest.php` |
| `TherapistArticle` | `user` belongsTo `User` | `app/Models/TherapistArticle.php` |
| `Tisane` | `favorites` morphMany `Favorite` | `app/Models/Tisane.php` |
| `Training` | `chapters` hasMany `Chapter` | `app/Models/Training.php` |
| `TrainingBlock` | `module` belongsTo `TrainingModule`<br>`comments` hasMany `DigitalTrainingBlockComment` | `app/Models/TrainingBlock.php` |
| `TrainingModule` | `training` belongsTo `DigitalTraining`<br>`blocks` hasMany `TrainingBlock` | `app/Models/TrainingModule.php` |
| `Unavailability` | `user` belongsTo `User` | `app/Models/Unavailability.php` |
| `User` | `favorites` hasMany `Favorite`<br>`events` hasMany `Event`<br>`products` hasMany `Product`<br>`invoices` hasMany `Invoice`<br>`superPdpConnection` hasOne `SuperPdpConnection`<br>`superPdpReceivedInvoices` hasMany `SuperPdpReceivedInvoice`<br>`appointments` hasMany `Appointment`<br>`availabilities` hasMany `Availability`<br>`clientProfiles` hasMany `ClientProfile`<br>`activeLicense` hasOne `UserLicense`<br>`licenseHistories` hasMany `LicenseHistory`<br>`license` hasOne `UserLicense`<br>`questionnaires` hasMany `Questionnaire`<br>`testimonialRequests` hasMany `TestimonialRequest`<br>`testimonials` hasMany `Testimonial`<br>`inventoryItems` hasMany `InventoryItem`<br>`informationRequests` hasMany `\App\Models\InformationRequest`<br>`giftVouchers` hasMany `\App\Models\GiftVoucher`<br>`giftVoucherOrders` hasMany `\App\Models\GiftVoucherOrder`<br>`practiceLocations` hasMany `\App\Models\PracticeLocation`<br>`ownedPracticeLocations` hasMany `\App\Models\PracticeLocation`<br>`practiceLocationMemberships` hasMany `\App\Models\PracticeLocationMember`<br>`sharedPracticeLocations` belongsToMany `\App\Models\PracticeLocation`<br>`communityGroups` hasMany `CommunityGroup` | `app/Models/User.php` |
| `UserLessonProgress` | None detected | `app/Models/UserLessonProgress.php` |
| `UserLicense` | `user` belongsTo `User`<br>`licenseTier` belongsTo `LicenseTier` | `app/Models/UserLicense.php` |

## Mobile Build Implications

- The `/mobile` product is a Laravel server-rendered mobile app surface packaged into Android with Capacitor.
- The safest Android product scope remains the practitioner workspace plus transactional public search/booking flows.
- Web behavior must remain authoritative. Mobile routes should either branch to mobile views from existing controllers or live under the isolated `mobile.*` route namespace.
- Public/marketing, admin, and system email/PDF templates should not be forced into mobile app scope unless they are part of a user journey inside the Android app.
- Every added mobile route should be verified with `php artisan route:list --path=mobile`, `php artisan view:cache`, `npm run build`, and browser checks at phone viewport.

