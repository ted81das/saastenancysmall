<?php

namespace App\Livewire\Filament;

use App\Models\Currency;
use App\Models\EmailProvider;
use App\Services\ConfigManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class GeneralSettings extends Component implements HasForms
{
    private ConfigManager $configManager;

    use InteractsWithForms;

    public ?array $data = [];

    public function render()
    {
        return view('livewire.filament.general-settings');
    }

    public function boot(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => $this->configManager->get('app.name'),
            'description' => $this->configManager->get('app.description'),
            'support_email' => $this->configManager->get('app.support_email'),
            'date_format' => $this->configManager->get('app.date_format'),
            'datetime_format' => $this->configManager->get('app.datetime_format'),
            'default_currency' => $this->configManager->get('app.default_currency'),
            'google_tracking_id' => $this->configManager->get('app.google_tracking_id'),
            'tracking_scripts' => $this->configManager->get('app.tracking_scripts'),
            'payment_proration_enabled' => $this->configManager->get('app.payment.proration_enabled'),
            'default_email_provider' => $this->configManager->get('mail.default'),
            'default_email_from_name' => $this->configManager->get('mail.from.name'),
            'default_email_from_email' => $this->configManager->get('mail.from.address'),
            'show_subscriptions' => $this->configManager->get('app.customer_dashboard.show_subscriptions', true),
            'show_orders' => $this->configManager->get('app.customer_dashboard.show_orders', true),
            'show_transactions' => $this->configManager->get('app.customer_dashboard.show_transactions', true),
            'social_links_facebook' => $this->configManager->get('app.social_links.facebook') ?? '',
            'social_links_x' => $this->configManager->get('app.social_links.x') ?? '',
            'social_links_linkedin' => $this->configManager->get('app.social_links.linkedin-openid') ?? '',
            'social_links_instagram' => $this->configManager->get('app.social_links.instagram') ?? '',
            'social_links_youtube' => $this->configManager->get('app.social_links.youtube') ?? '',
            'social_links_github' => $this->configManager->get('app.social_links.github') ?? '',
            'social_links_discord' => $this->configManager->get('app.social_links.discord') ?? '',
            'roadmap_enabled' => $this->configManager->get('app.roadmap_enabled', true),
            'recaptcha_enabled' => $this->configManager->get('app.recaptcha_enabled', false),
            'recaptcha_api_site_key' => $this->configManager->get('recaptcha.api_site_key', ''),
            'recaptcha_api_secret_key' => $this->configManager->get('recaptcha.api_secret_key', ''),
            'cookie_consent_enabled' => $this->configManager->get('cookie-consent.enabled', false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make()->tabs([
                    Tabs\Tab::make(__('Application'))
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            TextInput::make('site_name')
                                ->label(__('Site Name'))
                                ->required(),
                            Textarea::make('description')
                                ->helperText(__('This will be used as the meta description for your site (for pages that have no description).')),
                            TextInput::make('support_email')
                                ->label(__('Support Email'))
                                ->required()
                                ->email(),
                            TextInput::make('date_format')
                                ->label(__('Date Format'))
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            // make sure that the date format is valid
                                            $timestamp = strtotime('2021-01-01');
                                            $date = date($value, $timestamp);

                                            if ($date === false) {
                                                $fail(__('The :attribute is invalid.'));
                                            }
                                        };
                                    },
                                ])
                                ->required(),
                            TextInput::make('datetime_format')
                                ->label(__('Date Time Format'))
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            // make sure that the date format is valid
                                            $timestamp = strtotime('2021-01-01 00:00:00');
                                            $date = date($value, $timestamp);

                                            if ($date === false) {
                                                $fail(__('The :attribute is invalid.'));
                                            }
                                        };
                                    },
                                ])
                                ->required(),
                        ]),
                    Tabs\Tab::make(__('Payment'))
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Select::make('default_currency')
                                ->label(__('Default Currency'))
                                ->options(function () {
                                    $currencies = [];
                                    foreach (Currency::all() as $currency) {
                                        $currencies[$currency->code] = $currency->name.' ('.$currency->code.')';
                                    }

                                    return $currencies;
                                })
                                ->helperText(__('This is the currency that will be used for all payments.'))
                                ->required()
                                ->searchable(),
                            Toggle::make('payment_proration_enabled')
                                ->label(__('Payment Proration Enabled'))
                                ->helperText(__('If enabled, when a customer upgrades or downgrades their subscription, the amount they have already paid will be prorated and credited towards their new plan.')),
                        ]),
                    Tabs\Tab::make(__('Email'))
                        ->icon('heroicon-o-envelope')
                        ->schema([
                            Select::make('default_email_provider')
                                ->label(__('Default Email Provider'))
                                ->options(function () {
                                    $providers = [
                                        'smtp' => 'SMTP',
                                    ];

                                    foreach (EmailProvider::all() as $provider) {
                                        $providers[$provider->slug] = $provider->name;
                                    }

                                    return $providers;
                                })
                                ->helperText(__('This is the email provider that will be used for all emails.'))
                                ->required()
                                ->searchable(),
                            TextInput::make('default_email_from_name')
                                ->label(__('Default "From" Email Name'))
                                ->helperText(__('This is the name that will be used as the "From" name for all emails.'))
                                ->required(),
                            TextInput::make('default_email_from_email')
                                ->label(__('Default "From" Email Address'))
                                ->helperText(__('This is the email address that will be used as the "From" address for all emails.'))
                                ->required()
                                ->email(),
                        ]),
                    Tabs\Tab::make(__('Analytics & Cookies'))
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Toggle::make('cookie_consent_enabled')
                                ->label(__('Cookie Consent Bar Enabled'))
                                ->helperText(__('If enabled, the cookie consent bar will be shown to users.')),
                            TextInput::make('google_tracking_id')
                                ->helperText(__('Google analytics will only be inserted if either "Cookie Consent Bar" is disabled or in case user has consented to cookies.'))
                                ->label(__('Google Tracking ID')),
                            Textarea::make('tracking_scripts')
                                ->helperText(__('Paste in any other analytics or tracking scripts here. Those scripts will only be inserted if either "Cookie Consent Bar" is disabled or in case user has consented to cookies.'))
                                ->label(__('Other Tracking Scripts')),
                        ]),
                    Tabs\Tab::make(__('Customer Dashboard'))
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Toggle::make('show_subscriptions')
                                ->label(__('Show Subscriptions'))
                                ->helperText(__('If enabled, customers will be able to see their subscriptions on the dashboard.'))
                                ->required(),
                            Toggle::make('show_orders')
                                ->label(__('Show Orders'))
                                ->helperText(__('If enabled, customers will be able to see their orders on the dashboard.'))
                                ->required(),
                            Toggle::make('show_transactions')
                                ->label(__('Show Transactions'))
                                ->helperText(__('If enabled, customers will be able to see their transactions on the dashboard.'))
                                ->required(),
                        ]),
                    Tabs\Tab::make(__('Roadmap'))
                        ->icon('heroicon-o-bug-ant')
                        ->schema([
                            Toggle::make('roadmap_enabled')
                                ->label(__('Roadmap Enabled'))
                                ->helperText(__('If enabled, the roadmap will be visible to the public.'))
                                ->required(),
                        ]),
                    Tabs\Tab::make(__('Recaptcha'))
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Toggle::make('recaptcha_enabled')
                                ->label(__('Recaptcha Enabled'))
                                ->helperText(new HtmlString(__('If enabled, recaptcha will be used on the registration & login forms. For more info on how to configure Recaptcha, see the <a class="text-primary-500" href=":url" target="_blank">documentation</a>.', ['url' => 'https://saasykit.com/docs/recaptcha'])))
                                ->required(),
                            TextInput::make('recaptcha_api_site_key')
                                ->label(__('Recaptcha Site Key')),
                            TextInput::make('recaptcha_api_secret_key')
                                ->label(__('Recaptcha Secret Key')),
                        ]),
                    Tabs\Tab::make(__('Social Links'))
                        ->icon('heroicon-o-heart')
                        ->schema([
                            TextInput::make('social_links_facebook')
                                ->label(__('Facebook')),
                            TextInput::make('social_links_x')
                                ->label(__('X (Twitter)')),
                            TextInput::make('social_links_linkedin')
                                ->label(__('LinkedIn')),
                            TextInput::make('social_links_instagram')
                                ->label(__('Instagram')),
                            TextInput::make('social_links_youtube')
                                ->label(__('YouTube')),
                            TextInput::make('social_links_github')
                                ->label(__('GitHub')),
                            TextInput::make('social_links_discord')
                                ->label(__('Discord')),
                        ]),
                ])
                    ->persistTabInQueryString('settings-tab'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->configManager->set('app.name', $data['site_name']);
        $this->configManager->set('app.description', $data['description']);
        $this->configManager->set('app.support_email', $data['support_email']);
        $this->configManager->set('app.date_format', $data['date_format']);
        $this->configManager->set('app.datetime_format', $data['datetime_format']);
        $this->configManager->set('app.default_currency', $data['default_currency']);
        $this->configManager->set('app.google_tracking_id', $data['google_tracking_id'] ?? '');
        $this->configManager->set('app.tracking_scripts', $data['tracking_scripts'] ?? '');
        $this->configManager->set('app.payment.proration_enabled', $data['payment_proration_enabled']);
        $this->configManager->set('mail.default', $data['default_email_provider']);
        $this->configManager->set('mail.from.name', $data['default_email_from_name']);
        $this->configManager->set('mail.from.address', $data['default_email_from_email']);
        $this->configManager->set('app.customer_dashboard.show_subscriptions', $data['show_subscriptions']);
        $this->configManager->set('app.customer_dashboard.show_orders', $data['show_orders']);
        $this->configManager->set('app.customer_dashboard.show_transactions', $data['show_transactions']);
        $this->configManager->set('app.social_links.facebook', $data['social_links_facebook']);
        $this->configManager->set('app.social_links.x', $data['social_links_x']);
        $this->configManager->set('app.social_links.linkedin-openid', $data['social_links_linkedin']);
        $this->configManager->set('app.social_links.instagram', $data['social_links_instagram']);
        $this->configManager->set('app.social_links.youtube', $data['social_links_youtube']);
        $this->configManager->set('app.social_links.github', $data['social_links_github']);
        $this->configManager->set('app.social_links.discord', $data['social_links_discord']);
        $this->configManager->set('app.roadmap_enabled', $data['roadmap_enabled']);
        $this->configManager->set('app.recaptcha_enabled', $data['recaptcha_enabled']);
        $this->configManager->set('recaptcha.api_site_key', $data['recaptcha_api_site_key']);
        $this->configManager->set('recaptcha.api_secret_key', $data['recaptcha_api_secret_key']);
        $this->configManager->set('cookie-consent.enabled', $data['cookie_consent_enabled']);

        Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
}
