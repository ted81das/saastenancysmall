<?php

namespace App\Mapper;

use App\Constants\TransactionStatus;

class TransactionStatusMapper
{
    public function mapForDisplay(string $status)
    {
        return match ($status) {
            TransactionStatus::NOT_STARTED->value => __('Not Started'),
            TransactionStatus::SUCCESS->value => __('Success'),
            TransactionStatus::FAILED->value => __('Failed'),
            TransactionStatus::REFUNDED->value => __('Refunded'),
            TransactionStatus::DISPUTED->value => __('Disputed'),
            default => __('Pending'),
        };
    }
}
