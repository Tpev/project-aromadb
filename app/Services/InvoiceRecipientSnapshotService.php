<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceRecipientSnapshotService
{
    public function current(Invoice $invoice): array
    {
        $invoice->loadMissing(['clientProfile.company', 'corporateClient']);

        $client = $invoice->clientProfile;
        $corporate = $invoice->corporateClient;
        $isCorporate = (bool) $corporate;
        $company = $isCorporate ? $corporate : $client?->company;

        $billingFirstName = $isCorporate
            ? (string) ($corporate->main_contact_first_name ?? '')
            : (string) ($client?->first_name_billing ?: $client?->first_name);
        $billingLastName = $isCorporate
            ? (string) ($corporate->main_contact_last_name ?? '')
            : (string) ($client?->last_name_billing ?: $client?->last_name);

        return [
            'recipient_type' => $isCorporate ? 'corporate' : 'individual',
            'client_name' => $isCorporate
                ? (string) ($corporate->trade_name ?: $corporate->name)
                : trim((string) $client?->first_name.' '.(string) $client?->last_name),
            'company_name' => (string) ($company?->trade_name ?: $company?->name),
            'billing_first_name' => $billingFirstName,
            'billing_last_name' => $billingLastName,
            'billing_contact_name' => trim($billingFirstName.' '.$billingLastName),
            'address' => (string) ($isCorporate
                ? $corporate->billing_address
                : ($client?->billing_address ?? $client?->address)),
            'postal_code' => (string) ($isCorporate
                ? $corporate->billing_zip
                : ($client?->billing_zip ?? $client?->zip)),
            'city' => (string) ($isCorporate
                ? $corporate->billing_city
                : ($client?->billing_city ?? $client?->city)),
            'country' => (string) ($isCorporate ? $corporate->billing_country : ''),
            'email' => (string) ($isCorporate
                ? ($corporate->billing_email ?: $corporate->main_contact_email)
                : ($client?->email_billing ?? $client?->email)),
            'cc_email' => (string) ($isCorporate && $client?->email
                && $client->email !== ($corporate->billing_email ?: $corporate->main_contact_email)
                    ? $client->email
                    : ''),
            'phone' => (string) ($isCorporate
                ? ($corporate->billing_phone ?: $corporate->main_contact_phone)
                : $client?->phone),
            'siret' => (string) ($corporate?->siret ?? ''),
            'vat_number' => (string) ($corporate?->vat_number ?? ''),
        ];
    }

    public function capture(Invoice $invoice, bool $force = false): array
    {
        if (! $force && ! empty($invoice->recipient_snapshot)) {
            return $invoice->recipient_snapshot;
        }

        if ($force) {
            $invoice->unsetRelation('clientProfile');
            $invoice->unsetRelation('corporateClient');
        }

        $snapshot = $this->current($invoice);
        $invoice->forceFill(['recipient_snapshot' => $snapshot])->saveQuietly();

        return $snapshot;
    }
}
