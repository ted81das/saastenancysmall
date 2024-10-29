<?php

namespace App\Constants;

enum PlanType: string
{
    case SEAT_BASED = 'seat_based';
    case FLAT_RATE = 'flat_rate';
}
