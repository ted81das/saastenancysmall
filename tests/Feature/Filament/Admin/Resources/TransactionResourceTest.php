<?php

namespace Tests\Feature\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionResource;
use App\Filament\Admin\Resources\TransactionResource;
use Tests\Feature\FeatureTest;

class TransactionResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->get(TransactionResource::getUrl('index', [], true, 'admin'))->assertSuccessful();

        $response->assertStatus(200);
    }
}
