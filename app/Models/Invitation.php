<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'email',  // this is the email address of the person being invited
        'token',
        'tenant_id',
        'expires_at',
        'accepted_at',
        'user_id',  // this is the user that created the invitation (the inviter)
        'status',
        'role',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
