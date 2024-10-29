<?php

namespace App\Livewire\Filament;

use App\Services\ConfigManager;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class InvoiceSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    use InteractsWithForms;

    public ?array $data = [];

    public function render()
    {
        return view('livewire.filament.invoice-settings');
    }

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function mount(): void
    {
        $this->form->fill([
            'invoices_enabled' => $this->configManager->get('invoices.enabled', false),
            'serial_number_series' => $this->configManager->get('invoices.serial_number.series', 'INV'),
            'seller_name' => $this->configManager->get('invoices.seller.attributes.name'),
            'seller_address' => $this->configManager->get('invoices.seller.attributes.address'),
            'seller_code' => $this->configManager->get('invoices.seller.attributes.code'),
            'seller_tax_number' => $this->configManager->get('invoices.seller.attributes.vat'),
            'seller_phone' => $this->configManager->get('invoices.seller.attributes.phone'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    Section::make(__('Invoice generation'))
                        ->schema([
                            Toggle::make('invoices_enabled')
                                ->label(__('Enable invoice generation'))
                                ->helperText(__('If enabled, invoices will be generated for each successful transaction. Customers will be able to see their invoices in their dashboard.'))
                                ->required(),
                            TextInput::make('serial_number_series')
                                ->required()
                                ->default('')
                                ->label(__('Invoice number prefix')),
                            TextInput::make('seller_name')
                                ->default('')
                                ->label(__('Company name')),
                            TextInput::make('seller_code')
                                ->default('')
                                ->label(__('Company code')),
                            TextInput::make('seller_address')
                                ->default('')
                                ->label(__('Company address')),
                            TextInput::make('seller_tax_number')
                                ->default('')
                                ->label(__('Company tax number (VAT)')),
                            TextInput::make('seller_phone')
                                ->default('')
                                ->label(__('Company phone')),
                            Actions::make([
                                Action::make('preview')
                                    ->label(__('Generate Preview'))
                                    ->icon('heroicon-o-eye')
                                    ->color('gray')
                                    ->modalSubmitAction(false)
                                    ->modalCancelAction(false)
                                    ->modalContent()
                                    ->openUrlInNewTab()
                                    ->modalContent(function ($get) {
                                        $url = route('invoice.preview', [
                                            'serial_number_series' => $get('serial_number_series'),
                                            'seller_name' => $get('seller_name'),
                                            'seller_code' => $get('seller_code'),
                                            'seller_address' => $get('seller_address'),
                                            'seller_tax_number' => $get('seller_tax_number'),
                                            'seller_phone' => $get('seller_phone'),
                                        ]);

                                        return new HtmlString('<iframe src="' . $url . '" class="w-full h-screen"></iframe>');
                                    })

                            ]),
                        ]),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('invoices.enabled', $data['invoices_enabled']);
        $this->configManager->set('invoices.serial_number.series', $data['serial_number_series']);
        $this->configManager->set('invoices.seller.attributes.name', $data['seller_name']);
        $this->configManager->set('invoices.seller.attributes.address', $data['seller_address']);
        $this->configManager->set('invoices.seller.attributes.code', $data['seller_code']);
        $this->configManager->set('invoices.seller.attributes.vat', $data['seller_tax_number']);
        $this->configManager->set('invoices.seller.attributes.phone', $data['seller_phone']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
