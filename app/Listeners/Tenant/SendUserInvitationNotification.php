<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\UserInvitedToTenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendUserInvitationNotification implements ShouldQueue
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
    public function handle(UserInvitedToTenant $event): void
    {
        Mail::to($event->invitation->email)
            ->send(new \App\Mail\Tenant\UserInvitation($event->invitation));
    }
}
