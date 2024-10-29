<?php

namespace App\Livewire\Checkout;

use App\Services\SessionManager;
use App\Services\TenantCreationManager;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProductTenantPicker extends Component
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
        $cartDto = $this->sessionManager->getCartDto();

        if (! empty($cartDto->tenantUuid)) {
            $this->tenant = $cartDto->tenantUuid;
        } else {
            $this->tenant = $this->tenantCreationManager->findUserTenantsForNewOrder(auth()->user())->first()?->uuid;
        }

        $cartDto->tenantUuid = $this->tenant;
        $this->sessionManager->saveCartDto($cartDto);
    }

    public function updatedTenant(string $value)
    {
        if (! empty($value)) {

            $tenant = $this->tenantCreationManager->findUserTenantForNewOrderByUuid(auth()->user(), $value);

            if ($tenant === null) {
                throw ValidationException::withMessages([
                    'tenant' => __('You do not have access to this account.'),
                ]);
            }
        }

        $cartDto = $this->sessionManager->getCartDto();
        $cartDto->tenantUuid = $value;
        $this->sessionManager->saveCartDto($cartDto);
    }

    public function render()
    {
        return view('livewire.checkout.product-tenant-picker', [
            'userTenants' => $this->tenantCreationManager->findUserTenantsForNewOrder(auth()->user()),
        ]);
    }
}
