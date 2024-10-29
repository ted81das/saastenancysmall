<?php

namespace App\Constants;

class OrderStatusConstants
{
    public const FINAL_STATUSES = [
        OrderStatus::SUCCESS,
        OrderStatus::REFUNDED,
        OrderStatus::DISPUTED,
    ];
}
