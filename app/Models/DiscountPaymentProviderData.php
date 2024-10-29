<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountPaymentProviderData extends Model
{
    use HasFactory;

    protected $fillable = [
        'discount_id',
        'payment_provider_id',
        'payment_provider_discount_id',
    ];

}
