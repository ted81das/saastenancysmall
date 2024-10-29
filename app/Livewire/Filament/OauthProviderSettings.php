<?php

namespace App\Livewire\Filament;

use App\Services\ConfigManager;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

abstract class OauthProviderSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    protected string $slug = '';

    use InteractsWithForms;

    public ?array $data = [];

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function render()
    {
        return view('livewire.filament.'.$this->slug.'-settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'client_id' => $this->configManager->get('services.'.$this->slug.'.client_id'),
            'client_secret' => $this->configManager->get('services.'.$this->slug.'.client_secret'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('client_id')
                            ->label(__('Client ID'))
                            ->required(),
                        TextInput::make('client_secret')
                            ->label(__('Client Secret'))
                            ->password()
                            ->required(),
                    ])->columnSpan([
                        'sm' => 6,
                        'xl' => 8,
                        '2xl' => 8,
                    ]),
                Section::make()->schema([
                    ViewField::make('how-to')
                        ->label(__('Stripe Settings'))
                        ->view('filament.admin.resources.oauth-login-provider-resource.pages.partials.'.$this->slug.'-how-to'),
                ])->columnSpan([
                    'sm' => 6,
                    'xl' => 4,
                    '2xl' => 4,
                ]),
            ])->columns(12)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('services.'.$this->slug.'.client_id', $data['client_id']);
        $this->configManager->set('services.'.$this->slug.'.client_secret', $data['client_secret']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
