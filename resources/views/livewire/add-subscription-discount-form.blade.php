<div>
    <form wire:submit="submit">
        {{ $this->form }}


        <div class="container flex mt-4">
            <a class="btn btn-sm normal-case mr-4" href="{{ $backUrl }}">
                {{ __('Cancel') }}
            </a>

            <button class="btn btn-primary btn-sm normal-case">
                @svg('heroicon-o-plus-circle', 'fi-btn-icon h-5 w-5')
                {{ __('Add discount') }}
            </button>
        </div>

    </form>

{{--    <x-filament-actions::modals />--}}
</div>
