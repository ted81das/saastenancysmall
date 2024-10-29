<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource;
use Tests\Feature\FeatureTest;

class UserResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(UserResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
