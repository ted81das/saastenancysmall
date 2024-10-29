<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'amount',
        'valid_until',
        'action_type',
        'redemptions',
        'max_redemptions',
        'max_redemptions_per_user',
        'is_recurring',
        'is_active',
        'duration_in_months',
        'maximum_recurring_intervals',
    ];

    protected static function booted(): void
    {
        static::updated(function (Discount $discount) {
            // delete discount_payment_provider_data when discount is updated to recreate provider discounts when discount is updated
            $discount->discountPaymentProviderData()->delete();
        });

        static::deleted(function (Discount $discount) {
            // delete discount_payment_provider_data when discount is deleted to recreate provider discounts when discount is deleted
            $discount->discountPaymentProviderData()->delete();
        });
    }

    public function discountPaymentProviderData(): HasMany
    {
        return $this->hasMany(DiscountPaymentProviderData::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }

    public function oneTimeProducts(): BelongsToMany
    {
        return $this->belongsToMany(OneTimeProduct::class);
    }

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }
}
