<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => '
relative z-0 text-secondary-900 after:bg-secondary-500 hover:bg-secondary-600 before:rounded-full glowing-button after:rounded-full

']) }}
>
    {{ $slot }}
</x-button-link.default>

