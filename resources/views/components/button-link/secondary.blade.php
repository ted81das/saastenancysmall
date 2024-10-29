<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => 'text-secondary-900 bg-secondary-500 hover:bg-secondary-600 focus:ring-secondary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800'])}}
>
    {{ $slot }}
</x-button-link.default>
