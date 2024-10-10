<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des factures.
     */
    public function viewAny(User $user)
    {
        return true; // Allow any authenticated user to view their invoices
    }

    /**
     * Détermine si l'utilisateur peut voir la facture.
     */
    public function view(User $user, Invoice $invoice)
    {
        return $invoice->user_id === $user->id; // Only allow the owner to view the invoice
    }

    /**
     * Détermine si l'utilisateur peut créer une facture.
     */
    public function create(User $user)
    {
        return true; // All authenticated users can create invoices
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour la facture.
     */
    public function update(User $user, Invoice $invoice)
    {
        return $invoice->user_id === $user->id; // Only allow the owner to update the invoice
    }

    /**
     * Détermine si l'utilisateur peut supprimer la facture.
     */
    public function delete(User $user, Invoice $invoice)
    {
        return $invoice->user_id === $user->id; // Only allow the owner to delete the invoice
    }

    /**
     * Détermine si l'utilisateur peut restaurer la facture.
     */
    public function restore(User $user, Invoice $invoice)
    {
        return $invoice->user_id === $user->id; // Only allow the owner to restore the invoice
    }

    /**
     * Détermine si l'utilisateur peut forcer la suppression de la facture.
     */
    public function forceDelete(User $user, Invoice $invoice)
    {
        return $invoice->user_id === $user->id; // Only allow the owner to force delete the invoice
    }
}
