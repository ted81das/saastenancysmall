<x-layouts.app>
    <div class="m-4">
        <div class="text-center pt-4 pb-0 md:pt-12 md:mb-10">
            <x-heading.h1 class="font-semibold">
                {{ __('Roadmap') }}
            </x-heading.h1>
            <p class="pt-4">
                {{ __('Suggest a feature or stay on top of what we are working on.') }}
            </p>

        </div>

        <div class="max-w-none md:max-w-4xl mx-auto">
            <div class="text-end  my-6">
                <x-button-link.primary-outline href="{{route('roadmap')}}">{{ __('<< Back to Roadmap') }}</x-button-link.primary-outline>
            </div>

            <livewire:roadmap.view :slug="$slug"></livewire:roadmap.view>
        </div>
    </div>

</x-layouts.app>
