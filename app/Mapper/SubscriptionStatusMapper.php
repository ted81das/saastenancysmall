<?php

namespace App\Mapper;

use App\Constants\SubscriptionStatus;

class SubscriptionStatusMapper
{
    public function mapForDisplay(string $status)
    {
        return match ($status) {
            SubscriptionStatus::ACTIVE->value => __('Active'),
            SubscriptionStatus::CANCELED->value => __('Canceled'),
            SubscriptionStatus::INACTIVE->value => __('Inactive'),
            SubscriptionStatus::PAST_DUE->value => __('Past Due'),
            SubscriptionStatus::PAUSED->value => __('Paused'),
            default => __('Pending'),
        };
    }
}
