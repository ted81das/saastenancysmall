<?php

namespace App\Livewire\Filament;

use App\Services\ConfigManager;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class PostmarkSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    protected string $slug = 'postmark';

    use InteractsWithForms;

    public ?array $data = [];

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function render()
    {
        return view('livewire.filament.amazon-ses-settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'token' => $this->configManager->get('services.'.$this->slug.'.token'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('token')
                            ->label(__('Token'))
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('services.'.$this->slug.'.token', $data['token']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
