@php
    $cp = $invoice->clientProfile;
    $corp = $invoice->corporateClient;
    $company = $cp?->company;

    if ($corp) {
        $recipientCompanyName = $corp->trade_name ?: $corp->name;
        $billingFirst = $corp->main_contact_first_name;
        $billingLast = $corp->main_contact_last_name;
    } else {
        $billingFirst = $cp?->first_name_billing ?: $cp?->first_name;
        $billingLast = $cp?->last_name_billing ?: $cp?->last_name;
        $recipientCompanyName = $company?->name;
    }

    $billingFullName = trim(($billingFirst ?? '') . ' ' . ($billingLast ?? ''));
@endphp

@component('mail::message')
@if($corp)
# Bonjour,

Nous vous recontactons au sujet de la facture n&deg; **{{ $invoice->invoice_number }}** destin&eacute;e &agrave; :

**{{ $recipientCompanyName }}**
@if($billingFullName)
&Agrave; l&rsquo;attention de **{{ $billingFullName }}**.
@endif

@elseif($company)
# Bonjour,

Nous vous recontactons au sujet de la facture n&deg; **{{ $invoice->invoice_number }}** destin&eacute;e &agrave; :

**{{ $company->name }}**
@if($billingFullName)
&Agrave; l&rsquo;attention de **{{ $billingFullName }}**.
@endif

@else
# Bonjour {{ $billingFirst ?? '' }},

Nous vous recontactons au sujet de votre facture n&deg; **{{ $invoice->invoice_number }}**.
@endif

Le montant restant &agrave; r&eacute;gler est de **{{ number_format($invoice->solde_restant, 2, ',', ' ') }} &euro;**.

@if($invoice->due_date)
La date d&rsquo;&eacute;ch&eacute;ance indiqu&eacute;e est le **{{ $invoice->due_date->format('d/m/Y') }}**.
@endif

Veuillez trouver la facture en pi&egrave;ce jointe.

@if($invoice->payment_link)
@component('mail::button', ['url' => $invoice->payment_link])
R&eacute;gler la facture en ligne
@endcomponent
@endif

Merci par avance pour votre r&egrave;glement.

Cordialement,  
{{ $therapistName }}
@endcomponent
