<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoadmapItemResource;
use Tests\Feature\FeatureTest;

class RoadmapResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(RoadmapItemResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
