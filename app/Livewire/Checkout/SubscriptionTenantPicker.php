<?php

namespace App\Livewire\Checkout;

use App\Services\SessionManager;
use App\Services\TenantCreationManager;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SubscriptionTenantPicker extends Component
{
    public $tenant;

    private SessionManager $sessionManager;

    private TenantCreationManager $tenantCreationManager;

    public function boot(SessionManager $sessionManager, TenantCreationManager $tenantCreationManager)
    {
        $this->sessionManager = $sessionManager;
        $this->tenantCreationManager = $tenantCreationManager;
    }

    public function mount()
    {
        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        if (! empty($subscriptionCheckoutDto->tenantUuid)) {
            $this->tenant = $subscriptionCheckoutDto->tenantUuid;
        } else {
            $this->tenant = $this->tenantCreationManager->findUserTenantsForNewSubscription(auth()->user())->first()?->uuid;
        }

        $subscriptionCheckoutDto->tenantUuid = $this->tenant;
        $this->sessionManager->saveSubscriptionCheckoutDto($subscriptionCheckoutDto);
    }

    public function updatedTenant(string $value)
    {
        if (! empty($value)) {

            $tenant = $this->tenantCreationManager->findUserTenantForNewSubscriptionByUuid(auth()->user(), $value);

            if ($tenant === null) {
                throw ValidationException::withMessages([
                    'tenant' => __('You do not have access to this account.'),
                ]);
            }
        }

        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();
        $subscriptionCheckoutDto->tenantUuid = $value;
        $this->sessionManager->saveSubscriptionCheckoutDto($subscriptionCheckoutDto);
    }

    public function render()
    {
        return view('livewire.checkout.subscription-tenant-picker', [
            'userTenants' => $this->tenantCreationManager->findUserTenantsForNewSubscription(auth()->user()),
        ]);
    }
}
