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
use Illuminate\Support\HtmlString;
use Livewire\Component;

class StripeSettings extends Component implements HasForms
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
        return view('livewire.filament.stripe-settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'secret_key' => $this->configManager->get('services.stripe.secret_key'),
            'publishable_key' => $this->configManager->get('services.stripe.publishable_key'),
            'webhook_signing_secret' => $this->configManager->get('services.stripe.webhook_signing_secret'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('publishable_key')
                            ->label(__('Publishable Key'))
                            ->helperText(new HtmlString(__('The Stripe publishable key is used to authenticate requests from the Stripe JavaScript library. Check out the <strong><a href="https://stripe.com/docs/keys" target="_blank">Stripe documentation</a></strong> for more information.'))),
                        TextInput::make('secret_key')
                            ->label(__('Secret Key'))
                            ->password()
                            ->helperText(new HtmlString(__('The Stripe secret key is used to authenticate requests to the Stripe API. Check out the <strong><a href="https://stripe.com/docs/keys" target="_blank">Stripe documentation</a></strong> for more information.'))),
                        TextInput::make('webhook_signing_secret')
                            ->label(__('Webhook Signing Secret'))
                            ->helperText(new HtmlString(__('The Stripe webhook signing secret is used to verify that incoming webhooks are from Stripe. Check out the <strong><a href="https://stripe.com/docs/webhooks/signatures" target="_blank">Stripe documentation</a></strong> for more information.'))),
                    ])->columnSpan([
                        'sm' => 6,
                        'xl' => 8,
                        '2xl' => 8,
                    ]),
                Section::make()->schema([
                    ViewField::make('how-to')
                        ->label(__('Stripe Settings'))
                        ->view('filament.admin.resources.payment-provider-resource.pages.partials.stripe-how-to'),
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

        $this->configManager->set('services.stripe.secret_key', $data['secret_key']);
        $this->configManager->set('services.stripe.publishable_key', $data['publishable_key']);
        $this->configManager->set('services.stripe.webhook_signing_secret', $data['webhook_signing_secret']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
