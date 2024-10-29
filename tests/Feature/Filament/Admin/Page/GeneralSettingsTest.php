<?php

namespace Tests\Feature\Filament\Admin\Page;

use App\Filament\Admin\Pages\GeneralSettings;
use Tests\Feature\FeatureTest;

class GeneralSettingsTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(GeneralSettings::getUrl([], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
