<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => 'text-primary-50 bg-primary-500 hover:bg-primary-600 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800']) }}
>
    {{ $slot }}
</x-button-link.default>
