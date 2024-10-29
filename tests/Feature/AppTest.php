<?php

namespace Tests\Feature;

class AppTest extends FeatureTest
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_can_see_tracking_scripts_when_cookie_consent_bar_is_disabled()
    {
        config(['app.tracking_scripts' => '<javascript>Google Analytics</javascript>']);
        config(['cookie-consent.enabled' => false]);

        $response = $this->get('/');

        $response->assertSeeHtml('<javascript>Google Analytics</javascript>');
    }

    public function test_can_not_see_tracking_scripts_when_cookie_consent_bar_is_enabled_but_not_accepted()
    {
        config(['app.tracking_scripts' => '<javascript>Google Analytics</javascript>']);
        config(['cookie-consent.enabled' => true]);

        $response = $this->get('/');

        $response->assertDontSeeHtml('<javascript>Google Analytics</javascript>');
    }

    public function test_can_see_tracking_scripts_when_cookie_consent_bar_is_enabled_and_accepted()
    {
        config(['app.tracking_scripts' => '<javascript>Google Analytics</javascript>']);
        config(['cookie-consent.enabled' => true]);

        $response = $this->withCookie(config('cookie-consent.cookie_name'), 'accepted')->get('/');

        $response->assertSeeHtml('<javascript>Google Analytics</javascript>');
    }
}
