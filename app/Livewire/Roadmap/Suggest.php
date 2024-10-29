<?php

namespace App\Livewire\Roadmap;

use App\Livewire\Forms\RoadmapItemForm;
use App\Services\RoadmapManager;
use Livewire\Component;

class Suggest extends Component
{
    public RoadmapItemForm $form;
    private RoadmapManager $roadmapManager;

    // boot
    public function boot(RoadmapManager $roadmapManager)
    {
        $this->roadmapManager = $roadmapManager;
    }

    public function save()
    {
        $this->validate();

        $this->roadmapManager->createItem(
            $this->form->title,
            $this->form->description,
            $this->form->type
        );

        $this->redirectRoute('roadmap');
    }

    public function render()
    {
        return view('livewire.roadmap.suggest');
    }

}
