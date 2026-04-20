<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;
use App\Models\Questionnaire;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    public const BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY = 'first_time_only';
    public const BOOKING_QUESTIONNAIRE_EVERY_BOOKING = 'every_booking';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'tax_rate',
        'duration',
        'can_be_booked_online',
        'collect_payment',
        'visio',
        'adomicile',
        'en_entreprise',
        'dans_le_cabinet',
        'max_per_day',
        'image',
        'brochure',
        'display_order',
        'stripe_product_id',
        'stripe_price_id',
        'requires_emargement',
        'visible_in_portal',
        'price_visible_in_portal',
        'booking_questionnaire_enabled',
        'booking_questionnaire_id',
        'booking_questionnaire_frequency',
    ];

    protected $casts = [
        'requires_emargement'      => 'boolean',
        'visible_in_portal'        => 'boolean',
        'price_visible_in_portal'  => 'boolean',

        // (optionnel mais propre)
        'visio'           => 'boolean',
        'adomicile'       => 'boolean',
        'en_entreprise'   => 'boolean',
        'dans_le_cabinet' => 'boolean',
        'can_be_booked_online' => 'boolean',
        'collect_payment'      => 'boolean',
        'booking_questionnaire_enabled' => 'boolean',
    ];

    /** Le thérapeute qui a créé le produit. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Les lignes de facture liées. */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function bookingQuestionnaire()
    {
        return $this->belongsTo(Questionnaire::class, 'booking_questionnaire_id');
    }

    /** Les disponibilités liées au produit. */
    public function availabilities()
    {
        return $this->belongsToMany(Availability::class, 'availability_product');
    }

    /** Libellé des modes de consultation. */
    public function getConsultationModes()
    {
        $modes = [];
        if ($this->visio) $modes[] = 'En Visio';
        if ($this->adomicile) $modes[] = 'À Domicile';
        if ($this->en_entreprise) $modes[] = 'En entreprise';
        if ($this->dans_le_cabinet) $modes[] = 'Dans le Cabinet';

        return empty($modes) ? 'Non spécifié' : implode(', ', $modes);
    }

    /** Prix TTC (attribut virtuel). */
    public function getPriceInclTaxAttribute()
    {
        return $this->price + ($this->price * $this->tax_rate / 100);
    }

    /** (Optionnel) Scope pratique pour filtrer ce qui est visible dans le portail. */
    public function scopeVisibleInPortal($query)
    {
        return $query->where('visible_in_portal', true);
    }

    public function usesFirstTimeQuestionnaireAutomation(): bool
    {
        return ($this->booking_questionnaire_frequency ?? self::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY)
            === self::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY;
    }
}
