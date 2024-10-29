<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 px-4 md:p-0 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">

                <div class="flex flex-row gap-3">

                    <div>
                        @svg('success', 'h-8 w-8')
                    </div>

                    <x-heading.h2 class="!text-2xl md:!text-3xl !font-semibold mb-4">
                        {{ __('Thank you!') }}
                    </x-heading.h2>
                </div>

                <p>
                    {{ __('Your account is successfully registered. Head to the home page to get started.') }}

                    <x-button-link.primary class="inline-block !w-full mt-6" href="{{ route('home') }}">
                        {{ __('Continue') }}
                    </x-button-link.primary>
                </p>

            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 px-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="!text-3xl md:!text-4xl !font-semibold">
                {{ __('Registration Complete.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('We are excited to have you on board. :)') }}
            </p>
        </div>
    </x-slot>

</x-layouts.focus>
