<?php

namespace App\Mapper;

use App\Constants\OrderStatus;

class OrderStatusMapper
{
    public function mapForDisplay(string $status)
    {
        return match ($status) {
            OrderStatus::SUCCESS->value => __('Success'),
            OrderStatus::NEW->value => __('New'),
            OrderStatus::REFUNDED->value => __('Refunded'),
            default => __('Pending'),
        };
    }
}
