<?php

namespace App\Mapper;

use App\Constants\PlanType;

class PlanTypeMapper
{
    public function mapForDisplay(string $type)
    {
        return match ($type) {
            PlanType::FLAT_RATE->value => __('Flat Rate'),
            PlanType::SEAT_BASED->value => __('Seat Based'),
            default => __('Unknown'),
        };
    }
}
