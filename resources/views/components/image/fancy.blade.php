@props([
    'src' => '',
    'dir' => 'right',
    'fancyRight' => 'after:-right-2 after:-top-5 md:after:-right-3 md:after:-top-5',
    'fancyLeft' => 'after:-left-2 after:-top-5 md:after:-left-3 md:after:-top-5',
    'animation' => 'scale-up-center',
])

@php
    $fancy = $dir == 'right' ? $fancyRight : $fancyLeft;
@endphp

<div x-intersect="$el.classList.add(@js($animation))" {{ $attributes->merge(['class' => 'relative inline-block after:absolute after:bg-primary-100 after:w-16 md:after:w-32  after:content[""] after:h-16 md:after:h-32 after:-z-10 after:rounded-lg ' . $fancy])  }}>
    <img class=" drop-shadow-2xl rounded-2xl" src="{{$src}}" />
</div>

