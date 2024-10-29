<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneTimeProductPricePaymentProviderData extends Model
{
    use HasFactory;

    protected $fillable = [
        'one_time_product_price_id',
        'payment_provider_id',
        'payment_provider_price_id',
    ];
}
