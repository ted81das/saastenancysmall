<?php

namespace App\Constants;

enum SubscriptionStatus: string
{
    case NEW = 'new';
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CANCELED = 'canceled';
    case PAUSED = 'paused';
    case PAST_DUE = 'past_due';
}
