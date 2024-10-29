<?php

namespace App\Dto;

class CartDto
{
    /** @var array|CartItemDto[] */
    public array $items = [];

    public ?string $discountCode = null;

    public ?string $orderId = null;
    public ?string $tenantUuid = null;
}
