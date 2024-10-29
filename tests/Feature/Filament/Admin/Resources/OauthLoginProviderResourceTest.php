<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OauthLoginProviderResource;
use Tests\Feature\FeatureTest;

class OauthLoginProviderResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(OauthLoginProviderResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
