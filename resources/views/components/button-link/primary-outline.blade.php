<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => 'border border-primary-500 text-primary-500 bg-transparent hover:bg-primary-200 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800']) }}
>
    {{ $slot }}
</x-button-link.default>
