@props(['active' => false, 'name' => ''])

@php($checked = $active == 'true' ? 'checked' : '')

<div {{$attributes->merge(['class' => 'collapse collapse-arrow join-item border border-base-300'])}}>
    <input type="radio" name="{{$name}}" {{ $checked }} />
    <div class="collapse-title text-lg font-medium">
        {{ $title }}
    </div>
    <div class="collapse-content">
        <p>
            {{ $slot }}
        </p>
    </div>
</div>
