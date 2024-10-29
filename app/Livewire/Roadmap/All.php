<?php

namespace App\Livewire\Roadmap;

use App\Services\RoadmapManager;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class All extends Component
{
    use WithPagination;

    #[Url]
    public $done = false;

    public function render(RoadmapManager $roadmapManager)
    {
        return view('livewire.roadmap.all', [
            'items' => $this->done ? $roadmapManager->getCompleted() : $roadmapManager->getAll(),
        ]);
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
