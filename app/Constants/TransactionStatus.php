<?php

namespace App\Constants;

enum TransactionStatus: string
{
    case NOT_STARTED = 'not_started';
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';

    case REFUNDED = 'refunded';

    case DISPUTED = 'disputed';
}
