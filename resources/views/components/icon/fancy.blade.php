@props([
    'name' => '',
    'primary' => 'text-primary-900 bg-primary-50',
    'primaryFill' => 'fill-primary-900',
    'secondary' => 'text-secondary-900 bg-secondary-100',
    'secondaryFill' => 'fill-primary-900',
    'type' => 'primary',
])

@php
    $classes = $type === 'primary' ? $primary : $secondary;
    $classesFill = $type === 'primary' ? $primaryFill : $secondaryFill;
@endphp

<div {{ $attributes->merge(['class' => 'rounded-full p-6 ' . $classes])  }}>
    @svg($name, 'drop-shadow-2xl mx-auto ' . $classesFill)
</div>
