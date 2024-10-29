<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DiscountResource;
use Tests\Feature\FeatureTest;

class DiscountResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(DiscountResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
