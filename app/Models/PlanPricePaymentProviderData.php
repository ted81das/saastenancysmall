<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPricePaymentProviderData extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_price_id',
        'payment_provider_id',
        'payment_provider_price_id',
    ];
}
