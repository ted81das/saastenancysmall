<?php

namespace App\Events\Subscription;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Subscription $subscription,
        public Carbon $oldEndsAt,  // date the subscription was supposed to end
        public Carbon $newEndsAt   // new date the subscription will end
    ) {
        //
    }
}
