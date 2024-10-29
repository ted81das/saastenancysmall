<x-layouts.app>

    <div class="m-4">
        <div class="text-center pt-4 pb-0 md:pt-12 md:mb-10">
            <x-heading.h1 class="font-semibold !text-4xl">
                {{ __('My Invitations') }}
            </x-heading.h1>
            <p class="pt-4">
                {{ __('Here you can manage your invitations to join teams.') }}
            </p>

        </div>

        <div class="mx-auto max-w-4xl mt-16">
            <livewire:invitations.my-invitations/>
        </div>
    </div>

</x-layouts.app>
