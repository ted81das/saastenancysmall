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

class SmtpSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    protected string $slug = 'smtp';

    use InteractsWithForms;

    public ?array $data = [];

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function render()
    {
        return view('livewire.filament.smtp-settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'host' => $this->configManager->get('mail.mailers.smtp.host'),
            'port' => $this->configManager->get('mail.mailers.smtp.port'),
            'username' => $this->configManager->get('mail.mailers.smtp.username'),
            'password' => $this->configManager->get('mail.mailers.smtp.password'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('host')
                            ->label(__('Host')),
                        TextInput::make('port')
                            ->label(__('Port')),
                        TextInput::make('username')
                            ->label(__('Username')),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('mail.mailers.smtp.host', $data['host']);
        $this->configManager->set('mail.mailers.smtp.port', $data['port']);
        $this->configManager->set('mail.mailers.smtp.username', $data['username']);
        $this->configManager->set('mail.mailers.smtp.password', $data['password']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
