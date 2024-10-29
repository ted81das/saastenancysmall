<?php

namespace App\Http\Controllers;

class RoadmapController extends Controller
{
    public function index()
    {
        $this->assertRoadmapEnabled();

        return view('roadmap.index');
    }

    public function viewItem(string $itemSlug)
    {
        $this->assertRoadmapEnabled();

        return view('roadmap.view', [
            'slug' => $itemSlug,
        ]);

    }

    public function suggest()
    {
        $this->assertRoadmapEnabled();

        return view('roadmap.suggest');
    }

    private function assertRoadmapEnabled()
    {
        if (! config('app.roadmap_enabled')) {
            abort(404);
        }
    }
}
