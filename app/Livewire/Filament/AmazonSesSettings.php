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

class AmazonSesSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    protected string $slug = 'ses';

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
            'key' => $this->configManager->get('services.'.$this->slug.'.key'),
            'secret' => $this->configManager->get('services.'.$this->slug.'.secret'),
            'region' => $this->configManager->get('services.'.$this->slug.'.region'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('key')
                            ->label(__('Key'))
                            ->required(),
                        TextInput::make('secret')
                            ->label(__('Secret'))
                            ->password()
                            ->required(),
                        TextInput::make('region')
                            ->label(__('Region'))
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('services.'.$this->slug.'.key', $data['key']);
        $this->configManager->set('services.'.$this->slug.'.secret', $data['secret']);
        $this->configManager->set('services.'.$this->slug.'.region', $data['region']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
