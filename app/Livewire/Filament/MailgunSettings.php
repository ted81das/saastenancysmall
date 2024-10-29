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

class MailgunSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    protected string $slug = 'mailgun';

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
            'domain' => $this->configManager->get('services.'.$this->slug.'.domain'),
            'secret' => $this->configManager->get('services.'.$this->slug.'.secret'),
            'endpoint' => $this->configManager->get('services.'.$this->slug.'.endpoint'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('domain')
                            ->label(__('Domain'))
                            ->required(),
                        TextInput::make('secret')
                            ->label(__('Secret'))
                            ->password()
                            ->required(),
                        TextInput::make('endpoint')
                            ->label(__('Endpoint'))
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('services.'.$this->slug.'.domain', $data['domain']);
        $this->configManager->set('services.'.$this->slug.'.secret', $data['secret']);
        $this->configManager->set('services.'.$this->slug.'.endpoint', $data['endpoint']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
