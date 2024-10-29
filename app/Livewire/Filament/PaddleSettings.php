<?php

namespace App\Livewire\Filament;

use App\Services\ConfigManager;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class PaddleSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    use InteractsWithForms;

    public ?array $data = [];

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function render()
    {
        return view('livewire.filament.paddle-settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'vendor_id' => $this->configManager->get('services.paddle.vendor_id'),
            'client_side_token' => $this->configManager->get('services.paddle.client_side_token'),
            'vendor_auth_code' => $this->configManager->get('services.paddle.vendor_auth_code'),
            'webhook_secret' => $this->configManager->get('services.paddle.webhook_secret'),
            'is_sandbox' => $this->configManager->get('services.paddle.is_sandbox'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([

                        TextInput::make('vendor_id')
                            ->label(__('Vendor ID')),
                        TextInput::make('client_side_token')
                            ->label(__('Client Side Token')),
                        TextInput::make('vendor_auth_code')
                            ->label(__('Vendor Auth Code')),
                        TextInput::make('webhook_secret')
                            ->label(__('Webhook Secret')),
                        Toggle::make('is_sandbox')
                            ->label(__('Is Sandbox'))
                            ->required(),
                    ])->columnSpan([
                        'sm' => 6,
                        'xl' => 8,
                        '2xl' => 8,
                    ]),
                Section::make()->schema([
                    ViewField::make('how-to')
                        ->label(__('Paddle Settings'))
                        ->view('filament.admin.resources.payment-provider-resource.pages.partials.paddle-how-to'),
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

        $this->configManager->set('services.paddle.vendor_id', $data['vendor_id']);
        $this->configManager->set('services.paddle.client_side_token', $data['client_side_token']);
        $this->configManager->set('services.paddle.vendor_auth_code', $data['vendor_auth_code']);
        $this->configManager->set('services.paddle.webhook_secret', $data['webhook_secret']);
        $this->configManager->set('services.paddle.is_sandbox', $data['is_sandbox']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
