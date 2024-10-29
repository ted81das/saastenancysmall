<?php

namespace App\Constants;

class DiscountConstants
{
    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENTAGE = 'percentage';
    public const ACTION_TYPE_ANY = 'any';
    public const ACTION_TYPE_RENEWAL = 'renewal';
    public const ACTION_TYPE_UPGRADE = 'upgrade';

    public const ACTION_TYPES = [
        self::ACTION_TYPE_ANY,
        self::ACTION_TYPE_RENEWAL,
        self::ACTION_TYPE_UPGRADE,
    ];
}
