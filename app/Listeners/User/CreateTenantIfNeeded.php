<?php

namespace App\Listeners\User;

use App\Services\SessionManager;
use App\Services\TenantCreationManager;
use Illuminate\Auth\Events\Registered;

class CreateTenantIfNeeded
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private SessionManager $sessionManager,
        private TenantCreationManager $tenantCreationManager,
    ) {

    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if ($this->sessionManager->shouldCreateTenantForFreePlanUser()) {
            $this->tenantCreationManager->createTenantForFreePlanUser($event->user);
            $this->sessionManager->resetCreateTenantForFreePlanUser();
        }
    }
}
