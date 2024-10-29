<?php

namespace App\Constants;

enum OrderStatus: string
{
    case NEW = 'new';
    case PENDING = 'pending';
    case REFUNDED = 'refunded';
    case DISPUTED = 'disputed';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
