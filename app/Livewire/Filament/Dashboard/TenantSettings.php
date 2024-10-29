<?php

namespace App\Livewire\Filament\Dashboard;

use App\Services\TenantManager;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class TenantSettings extends Component implements HasForms
{
    use InteractsWithForms;

    private TenantManager $tenantManager;

    public ?array $data = [];

    public function render()
    {
        return view('livewire.filament.dashboard.tenant-settings');
    }

    public function boot(TenantManager $tenantManager): void
    {
        $this->tenantManager = $tenantManager;
    }

    public function mount(): void
    {
        $tenant = Filament::getTenant();
        $this->form->fill([
            'tenant_name' => $tenant->name,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tenant_name')
                    ->label(__('Workspace Name'))
                    ->helperText(__('Edit the name of your workspace'))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->tenantManager->updateTenantName(Filament::getTenant(), $data['tenant_name']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
