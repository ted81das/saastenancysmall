<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\FeatureTest;

class RoadmapControllerTest extends FeatureTest
{
    public function test_index()
    {
        $response = $this->get(route('roadmap'));

        $response->assertStatus(200);
    }

    public function test_suggest_unauthenticated()
    {
        $this->expectException(AuthenticationException::class);
        $this->get(route('roadmap.suggest'));
    }

    public function test_suggest_authenticated()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->get(route('roadmap.suggest'));

        $response->assertStatus(200);
    }

    public function test_roadmap_disabled()
    {
        config(['app.roadmap_enabled' => false]);

        $this->expectException(NotFoundHttpException::class);
        $this->get(route('roadmap'));

        $this->expectException(NotFoundHttpException::class);
        $this->get(route('roadmap.suggest'));

        $this->expectException(NotFoundHttpException::class);
        $this->get(route('roadmap.viewItem'));
    }
}
