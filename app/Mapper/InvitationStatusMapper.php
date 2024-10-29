<?php

namespace App\Mapper;

use App\Constants\InvitationStatus;

class InvitationStatusMapper
{
    public function mapForDisplay(string $status)
    {
        return match ($status) {
            InvitationStatus::PENDING->value => __('Pending'),
            InvitationStatus::ACCEPTED->value => __('Accepted'),
            InvitationStatus::REJECTED->value => __('Rejected'),
            default => __('Pending'),
        };
    }
}
