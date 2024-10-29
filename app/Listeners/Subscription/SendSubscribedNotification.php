<?php

namespace App\Listeners\Subscription;

use App\Events\Subscription\Subscribed;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendSubscribedNotification implements ShouldQueue
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
    public function handle(Subscribed $event): void
    {
        Mail::to($event->subscription->user->email)
            ->send(new \App\Mail\Subscription\Subscribed($event->subscription));
    }
}
