<x-layouts.focus-center>

    <div class="mx-4">
        <div class="card max-w-3xl bg-base-100 shadow-xl mx-auto text-center">
            <div class="card-body">
                @svg('party', 'w-24 h-24 mx-auto text-primary-500 stroke-primary-500')
                <x-heading.h3 class="text-primary-900">
                    {{ __('Thank you for your purchase!') }}
                </x-heading.h3>
                <p>
                    {{ __('Your order is being processed and you will receive an email with your order details shortly.') }}
                </p>

                <x-button-link.primary href="{{ route('home') }}" class="mt-4 mx-auto">
                    {{ __('Start Your Journey') }}
                </x-button-link.primary>

            </div>
        </div>
    </div>

</x-layouts.focus-center>
