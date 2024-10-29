<div>
    <form wire:submit="save">
        {{ $this->form }}

        <diV class="pt-4 flex gap-4">
            <x-filament::button type="submit">
                <x-filament::loading-indicator class="h-5 w-5 inline" wire:loading />
                {{ __('Save Changes') }}
            </x-filament::button>
        </diV>
    </form>

    <x-filament-actions::modals />
</div>
