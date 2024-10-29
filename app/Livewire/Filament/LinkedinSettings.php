<?php

namespace App\Livewire\Filament;

class LinkedinSettings extends OauthProviderSettings
{
    protected string $slug = 'linkedin-openid';

    public function render()
    {
        return view('livewire.filament.linkedin-settings');
    }
}
