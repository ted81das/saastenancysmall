<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource;
use Tests\Feature\FeatureTest;

class ProductResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(ProductResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
