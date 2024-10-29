<?php

namespace App\Services;
use App\Models\User;

class AddressManager
{
    public function userHasAddressInfo(User $user): bool
    {
        $address = $user->address()->first();

        if (!$address) {
            return false;
        }

        if (empty($address->address_line_1) || empty($address->country_code)) {
            return false;
        }

        return true;
    }
}
