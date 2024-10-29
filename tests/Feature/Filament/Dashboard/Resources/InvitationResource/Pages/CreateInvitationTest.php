<?php

namespace Tests\Feature\Filament\Dashboard\Resources\InvitationResource\Pages;

use App\Constants\InvitationStatus;
use App\Constants\TenancyPermissionConstants;
use App\Events\Tenant\UserInvitedToTenant;
use App\Filament\Dashboard\Resources\InvitationResource\Pages\CreateInvitation;
use App\Models\Invitation;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class CreateInvitationTest extends FeatureTest
{
    public function test_create()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS]);
        $this->actingAs($user);

        Filament::setCurrentPanel(
            Filament::getPanel('dashboard'),
        );

        Filament::setTenant($tenant);

        Event::fake();

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'email@email.com',
                'role' => TenancyPermissionConstants::ROLE_ADMIN,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Invitation::class, [
            'email' => 'email@email.com',
            'role' => TenancyPermissionConstants::ROLE_ADMIN,
        ]);

        Event::assertDispatched(UserInvitedToTenant::class);
    }

    public function test_create_can_only_invite_user_that_is_not_already_in_the_tenant()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS]);
        $this->actingAs($user);

        Filament::setCurrentPanel(
            Filament::getPanel('dashboard'),
        );

        Filament::setTenant($tenant);

        Event::fake();

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => $user->email,
                'role' => TenancyPermissionConstants::ROLE_ADMIN,
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'This user is already in the team.']);

        Event::assertNotDispatched(UserInvitedToTenant::class);
    }

    public function test_create_can_only_invite_user_that_is_not_already_invited()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS]);
        $this->actingAs($user);

        $fakeEmail = fake()->email;
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => $fakeEmail,
            'tenant_id' => $tenant->id,
            'status' => InvitationStatus::PENDING->value,
        ]);

        Filament::setCurrentPanel(
            Filament::getPanel('dashboard'),
        );

        Filament::setTenant($tenant);

        Event::fake();

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => $fakeEmail,
                'role' => TenancyPermissionConstants::ROLE_ADMIN,
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'This email address has already been invited.']);

        Event::assertNotDispatched(UserInvitedToTenant::class);
    }
}
