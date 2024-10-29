<?php

namespace App\Listeners\Subscription;

use App\Events\Subscription\SubscriptionCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionCancellationNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionCancelled $event): void
    {
        Mail::to($event->subscription->user->email)
            ->send(new \App\Mail\Subscription\SubscriptionCancelled($event->subscription));
    }
}
