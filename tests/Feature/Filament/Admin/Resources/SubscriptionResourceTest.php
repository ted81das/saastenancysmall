<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionResource;
use Tests\Feature\FeatureTest;

class SubscriptionResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(SubscriptionResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
