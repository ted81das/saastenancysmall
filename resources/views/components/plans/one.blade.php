@props([
    'popular' => false,
    'link' => '',
])

<div {{$attributes->merge(['class' => 'relative px-5 py-10 flex flex-col gap-4 mx-auto text-center border-2 border-primary-500 rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-2 transition'])}}>
    @if ($popular)
    <div class="absolute border-0 top-0 -mt-3 left-1/2 transform -translate-x-1/2 bg-primary-500 text-primary-50 mx-auto rounded z-0 text-xs px-2 py-1">
        {{ __('Most popular') }}
    </div>
    @endif

    <x-heading.h3>
        {{ $name }}
    </x-heading.h3>

    <div class="flex flex-col gap-1">
        <div class="text-4xl">
            {{ $price }}
        </div>

        <div class="text-neutral-400 text-sm">
            {{ $interval }}
        </div>
    </div>

    <div class="py-4">
        {{ $description }}
    </div>

    <x-button-link.primary href="{{$link}}">
        {{ __('Buy') }} {{ $name }}
    </x-button-link.primary>
</div>
