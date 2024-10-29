@props([
    'link' => '#',
    'name' => '',
    'title' => '',
])
<a href="{{$link}}" {{ $attributes->merge(['class' => 'rounded-full border p-1']) }}>
    @svg($name)
    <span class="sr-only">{{ $title }}</span>
</a>
