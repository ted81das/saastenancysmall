<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\Feature\FeatureTest;

class LoginControllerTest extends FeatureTest
{
    public function test_recaptcha_is_viewed_if_enabled()
    {
        config(['app.recaptcha_enabled' => true]);

        $response = $this->get(route('login'));

        $response->assertSee('g-recaptcha');
    }

    public function test_recaptcha_is_not_viewed_if_disabled()
    {
        config(['app.recaptcha_enabled' => false]);

        $response = $this->get(route('login'));

        $response->assertDontSee('g-recaptcha');
    }

}
