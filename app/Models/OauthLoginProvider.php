<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthLoginProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_name',
        'name',
        'client_id',
        'client_secret',
        'enabled',
    ];
}
