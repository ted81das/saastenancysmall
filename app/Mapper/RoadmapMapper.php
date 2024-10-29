<?php

namespace App\Mapper;

use App\Constants\RoadmapItemStatus;
use App\Constants\RoadmapItemType;

class RoadmapMapper
{
    public static function mapStatusForDisplay(RoadmapItemStatus|string $status): string
    {
        if (is_string($status)) {
            $status = RoadmapItemStatus::tryFrom($status);
        }

        switch ($status) {
            case RoadmapItemStatus::APPROVED:
                return __('👍 Approved');
            case RoadmapItemStatus::IN_PROGRESS:
                return __('⏳ In Progress');
            case RoadmapItemStatus::COMPLETED:
                return __('✅ Completed');
            case RoadmapItemStatus::CANCELLED:
                return __('⛔️ Cancelled');
            case RoadmapItemStatus::REJECTED:
                return __('👎 Declined');
            default:
                return __('🙏 Pending Approval');
        }
    }

    public static function mapTypeForDisplay(RoadmapItemType|string $type): string
    {
        if (is_string($type)) {
            $type = RoadmapItemType::tryFrom($type);
        }

        switch ($type) {
            case RoadmapItemType::BUG:
                return __('🐞 Bug');
            case RoadmapItemType::FEATURE:
                return __('🏅 Feature');
            default:
                return __('Unknown');
        }
    }

}
