<?php

namespace App\Livewire\Roadmap;

use App\Services\RoadmapManager;
use Livewire\Component;

class View extends Component
{
    public $slug;

    public function render(RoadmapManager $roadmapManager)
    {
        return view(
            'livewire.roadmap.view', [
                'item' => $roadmapManager->getItemBySlug($this->slug),
            ]
        );
    }

    public function upvote(int $id, RoadmapManager $roadmapManager)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $roadmapManager->upvote($id);
    }

    public function removeUpvote(int $id, RoadmapManager $roadmapManager)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $roadmapManager->removeUpvote($id);
    }
}
