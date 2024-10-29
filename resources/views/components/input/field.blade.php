@props([
    'label' => false,
    'id' => false,
    'name' => '',
    'type' => 'text',
    'value' => false,
    'placeholder' => false,
    'labelClass' => 'text-gray-900',
    'inputClass' => 'text-gray-900 bg-primary-50',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => false,
    'maxWidth' => 'max-w-xs',
    'disabled' => false,
])

@php
    $id = $id ?? 'text_' . rand();
    $required = $required ? 'required' : '';
    $autofocus = $autofocus ? 'autofocus' : '';
    $value = $value ? 'value="' . $value . '"' : '';
    $autocomplete = $autocomplete ? 'autocomplete="' . $autocomplete . '"' : '';
    $disabled = $disabled ? 'disabled' : '';
@endphp

<label {{ $attributes->merge(['class' => 'form-control w-full ' . $maxWidth]) }} for="{{$id}}">
    @if($label)
        <div class="label">
            <span class="label-text">{{ $label }}</span>
        </div>
    @endif
        <input type="{{$type}}"  class="input input-bordered input-md w-full {{$maxWidth}}" placeholder="{{$placeholder}}" name="{{$name}}" {{$required}} {{$autofocus}} {!! $value !!} {!! $autocomplete !!} {{$disabled}} id="{{$id}}">
</label>
