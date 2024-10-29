<?php

namespace App\Livewire;

use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Services\DiscountManager;
use App\Services\SubscriptionDiscountManager;
use App\Services\SubscriptionManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class AddSubscriptionDiscountForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public string $subscriptionUuid;

    private SubscriptionManager $subscriptionManager;

    private DiscountManager $discountManager;

    private SubscriptionDiscountManager $subscriptionDiscountManager;

    public function boot(
        SubscriptionManager $subscriptionManager,
        DiscountManager $discountManager,
        SubscriptionDiscountManager $subscriptionDiscountManager,
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->discountManager = $discountManager;
        $this->subscriptionDiscountManager = $subscriptionDiscountManager;
    }

    public function render()
    {
        return view('livewire.add-subscription-discount-form', [
            'backUrl' => SubscriptionResource::getUrl(),
        ]);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->required()
                    ->nullable(false),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();
        $code = $data['code'];
        $user = auth()->user();

        $subscription = $this->subscriptionManager->findByUuidOrFail($this->subscriptionUuid);

        $result = $this->subscriptionDiscountManager->applyDiscount($subscription, $code, $user);

        if (! $result) {

            Notification::make()
                ->title(__('Could not apply discount code.'))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('Discount code has been applied.'))
            ->send();

        $this->redirect(SubscriptionResource::getUrl());
    }
}
