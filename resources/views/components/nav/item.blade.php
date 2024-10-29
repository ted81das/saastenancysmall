@props(['route' => '#'])

@php($selected = request()->routeIs($route))
@php($selectedClass = $selected ? 'text-white' : 'text-primary-50')

<li {{ $attributes }}>
    <a href="{{ str_starts_with($route, '#') ? (route('home') . $route) : route($route) }}" class="text-sm block py-2 px-3 md:p-0 rounded hover:bg-primary-600 md:hover:bg-transparent md:hover:text-white md:dark:hover:text-primary-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700 {{ $selectedClass }}">
        {{ $slot }}
    </a>
</li>
