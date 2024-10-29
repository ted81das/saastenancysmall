@props(['backButton' => true])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.layouts.partials.head')
</head>
<body {{ $attributes->merge(['class' => 'text-primary-900 w-full']) }} >
    <div id="app">

        <div class="flex justify-between">
            <a href="{{route('home')}}">
                <img src="{{asset(config('app.logo.dark') )}}" class="inline-block h-6 m-6" alt="Logo" />
            </a>

            @if($backButton)
                <div class="self-end text-primary-300 m-4 text-xs">
                    <x-link href="{{route('home')}}" class="flex items-center text-primary-200">{{__('<< back')}}</x-link>
                </div>
            @endif
        </div>

        <div>
            {{$slot}}
        </div>

        @include('components.layouts.partials.tail')
    </div>
</body>
</html>
