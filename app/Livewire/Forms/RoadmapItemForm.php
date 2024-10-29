<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class RoadmapItemForm extends Form
{
    #[Validate('required|min:5|max:100')]
    public $title = '';

    public $description = '';

    #[Validate('required|in:bug,feature')]
    public $type = 'feature';
}
