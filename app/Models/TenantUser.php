<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Pivot
{
    /* Adding the HasRoles trait to the TenantUser model allows us to use the Spatie Permission package to manage roles and permissions for tenant users. */

    use HasRoles;

    protected string $guard_name = 'web';
}
