<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'inventory_item_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total_price',
        'total_price_with_tax',
        'line_discount_type',
        'line_discount_value',
        'line_discount_amount_ht',
        'global_discount_amount_ht',
        'total_price_before_discount',
		'label',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

public function getNameAttribute()
{
    return match ($this->type) {
        'product' => $this->product?->name ?? '(Produit inconnu)',
        'inventory' => $this->inventoryItem?->name ?? '(Item inventaire inconnu)',
        default => $this->label ?: ($this->description ?? '(Sans nom)'),
    };
}

    public function getVatRateAttribute()
    {
        return match ($this->type) {
            'product' => $this->product?->tax_rate ?? 0,
            'inventory' => $this->inventoryItem?->vat_rate_sale ?? 0,
            default => $this->tax_rate ?? 0,
        };
    }

 public function getUnitPriceHtAttribute()
{
    if ($this->type === 'inventory') {
        $rate = $this->vat_rate;
        return $rate > 0 ? $this->unit_price / (1 + $rate / 100) : $this->unit_price;
    }

    // Produit : déjà HT
    return $this->unit_price;
}

public function getUnitPriceTtcAttribute()
{
    if ($this->type === 'inventory') {
        return $this->unit_price; // Déjà TTC
    }

    // Produit : ajouter TVA
    return $this->unit_price * (1 + $this->vat_rate / 100);
}

public function getTotalHtAttribute()
{
    return $this->unit_price_ht * $this->quantity;
}

public function getTotalVatAttribute()
{
    return $this->total_ttc - $this->total_ht;
}

public function getTotalTtcAttribute()
{
    return $this->unit_price_ttc * $this->quantity;
}

}
