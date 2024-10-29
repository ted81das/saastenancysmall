<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => '
relative z-0 text-primary-50 after:bg-primary-500 hover:bg-primary-600 before:rounded-full glowing-button after:rounded-full

']) }}
>
    {{ $slot }}
</x-button-link.default>

