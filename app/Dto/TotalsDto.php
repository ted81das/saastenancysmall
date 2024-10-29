<?php

namespace App\Dto;

class TotalsDto
{
    /**
     * @var int total prices of products in cart (with tax)
     */
    public int $subtotal = 0;
    public int $discountAmount = 0;

    /**
     * @var int total prices of products in cart (with tax) + shipping cost + fees + tax on fees - discount
     */
    public int $amountDue = 0;
    public string $currencyCode;
}
