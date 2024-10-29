<?php

namespace App\Dto;

class SubscriptionCheckoutDto
{
    public ?string $discountCode = null;
    public ?string $planSlug = null;
    public ?string $subscriptionId = null;
    public int $quantity = 1;
    public ?string $tenantUuid = null;
}
