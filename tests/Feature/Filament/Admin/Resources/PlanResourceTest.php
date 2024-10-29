<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource;
use Tests\Feature\FeatureTest;

class PlanResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(PlanResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
