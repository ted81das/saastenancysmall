<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;

class AddressForm extends MyProfileComponent
{
    protected string $view = 'livewire.address-form';

    public array $data;

    public function mount(): void
    {
        $user = auth()->user();
        $address = $user->address()->first();

        if ($address) {
            $this->form->fill([
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'state' => $address->state,
                'zip' => $address->zip,
                'country_code' => $address->country_code,
                'phone' => $address->phone,
                'tax_number' => $address->tax_number,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('address_line_1')
                    ->label(__('Address Line 1'))
                    ->helperText(__('Street address, company name, c/o')),
                TextInput::make('address_line_2')
                    ->label(__('Address Line 2'))
                    ->helperText(__('Apartment, suite, unit, building, floor, etc.')),
                TextInput::make('city')
                    ->label(__('City')),
                TextInput::make('state')
                    ->label(__('State')),
                TextInput::make('zip')
                    ->label(__('Zip')),
                Country::make('country_code')
                    ->label(__('Country')),
                TextInput::make('phone')
                    ->label(__('Phone')),
                TextInput::make('tax_number')
                    ->label(__('Tax Number')),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();

        $user = auth()->user();

        $address = $user->address()->first();

        if ($address) {
            $address->update($data);
        } else {
            $user->address()->create($data);
        }

        Notification::make()
            ->title(__('Address Saved'))
            ->success()
            ->send();
    }
}
