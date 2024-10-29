<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmailProviderResource;
use Tests\Feature\FeatureTest;

class EmailProviderResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(EmailProviderResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
