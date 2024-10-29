<?php

namespace App\Listeners\Subscription;

use App\Events\Subscription\InvoicePaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendInvoicePaymentFailedNotification implements ShouldQueue
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
    public function handle(InvoicePaymentFailed $event): void
    {
        Mail::to($event->subscription->user->email)
            ->send(new \App\Mail\Subscription\InvoicePaymentFailed($event->subscription));
    }
}
