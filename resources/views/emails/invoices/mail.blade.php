@php
    $cp      = $invoice->clientProfile;
    $company = $cp->company ?? null;

    $billingFirst = $cp->first_name_billing ?: $cp->first_name;
    $billingLast  = $cp->last_name_billing  ?: $cp->last_name;
@endphp

@component('mail::message')
@if($company)
# Bonjour,

Veuillez trouver ci-joint la facture n° **{{ $invoice->invoice_number }}** destinée à :

**{{ $company->name }}**  
À l’attention de **{{ $billingFirst }} {{ $billingLast }}**.

@else
# Bonjour {{ $billingFirst }},

Veuillez trouver ci-joint votre facture n° **{{ $invoice->invoice_number }}**.
@endif

Merci pour votre confiance.

Cordialement,  
{{ $therapistName }}
@endcomponent
