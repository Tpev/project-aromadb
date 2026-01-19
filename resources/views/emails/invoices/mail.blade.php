@php
    $cp   = $invoice->clientProfile;
    $corp = $invoice->corporateClient;

    // Company via client profile (legacy)
    $company = $cp?->company;

    // Display name + billing contact (robust for corporate-only invoices)
    if ($corp) {
        $recipientCompanyName = $corp->trade_name ?: $corp->name;
        $billingFirst = $corp->main_contact_first_name;
        $billingLast  = $corp->main_contact_last_name;
    } else {
        $billingFirst = $cp?->first_name_billing ?: $cp?->first_name;
        $billingLast  = $cp?->last_name_billing  ?: $cp?->last_name;
        $recipientCompanyName = $company?->name;
    }

    $billingFullName = trim(($billingFirst ?? '').' '.($billingLast ?? ''));
@endphp

@component('mail::message')
@if($corp)
# Bonjour,

Veuillez trouver ci-joint la facture n° **{{ $invoice->invoice_number }}** destinée à :

**{{ $recipientCompanyName }}**
@if($billingFullName)
À l’attention de **{{ $billingFullName }}**.
@endif

@elseif($company)
# Bonjour,

Veuillez trouver ci-joint la facture n° **{{ $invoice->invoice_number }}** destinée à :

**{{ $company->name }}**
@if($billingFullName)
À l’attention de **{{ $billingFullName }}**.
@endif

@else
# Bonjour {{ $billingFirst ?? '' }},

Veuillez trouver ci-joint votre facture n° **{{ $invoice->invoice_number }}**.
@endif

Merci pour votre confiance.

Cordialement,  
{{ $therapistName }}
@endcomponent
