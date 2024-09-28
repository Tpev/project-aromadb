<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des produits.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Détermine si l'utilisateur peut voir le produit.
     */
    public function view(User $user, Product $product)
    {
        return $product->user_id === $user->id;
    }

    /**
     * Détermine si l'utilisateur peut créer un produit.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le produit.
     */
    public function update(User $user, Product $product)
    {
        return $product->user_id === $user->id;
    }

    /**
     * Détermine si l'utilisateur peut supprimer le produit.
     */
    public function delete(User $user, Product $product)
    {
        return $product->user_id === $user->id;
    }
}
