<div>

    <form wire:submit="cancel">
        {{ $this->form }}

        <p class="text-sm mt-3 italic">
            {{ __('Once you cancel, your subscription will be active until the end of your billing period. You will be able to continue using the service until the end of your billing period.') }}
        </p>

        <div class="container flex mt-4">
            <a class="btn btn-sm normal-case mr-4" href="{{ $backUrl }}">
                {{ __('Cancel') }}
            </a>

            <button class="btn btn-primary btn-sm normal-case">
                @svg('heroicon-s-power', 'fi-btn-icon h-5 w-5')
                {{ __('Confirm Subscription Cancellation') }}
            </button>
        </div>

    </form>

    <x-filament-actions::modals />
</div>
