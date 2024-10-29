<?php

namespace App\Livewire\Invitations;

use App\Models\Invitation;
use App\Services\TenantManager;
use App\Services\UserDashboardManager;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class MyInvitations extends Component
{
    public function render(TenantManager $tenantManager): View
    {
        return view('livewire.invitations.my-invitations', [
            'invitations' => $tenantManager->getUserInvitations(auth()->user()),
        ]);
    }

    public function acceptInvitation(string $invitationUuid, TenantManager $tenantManager, UserDashboardManager $dashboardManager)
    {
        $invitation = Invitation::where('uuid', $invitationUuid)->firstOrFail();
        $result = $tenantManager->acceptInvitation($invitation, auth()->user());

        if ($result === false) {
            throw ValidationException::withMessages([
                'invitation' => __('You cannot accept this invitation, please contact support.'),
            ]);
        }

        return redirect($dashboardManager->getUserDashboardUrl(auth()->user()));
    }

    public function rejectInvitation(string $invitationUuid, TenantManager $tenantManager)
    {
        $invitation = Invitation::where('uuid', $invitationUuid)->firstOrFail();
        $tenantManager->rejectInvitation($invitation, auth()->user());
    }
}
