<?php

namespace App\Dto;

class SubscriptionTotalsDto extends TotalsDto
{
    public ?int $pricePerSeat = null;
    public ?int $quantity = null;
}
