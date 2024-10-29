@props(['code' => '500', 'message' => __('Something went wrong.')])

<x-layouts.focus-center>
    <div class="text-center pt-2">
        <img src="{{URL::asset('/images/dog.png')}}" class="w-64 md:w-80 mx-auto">
        <h1 class="text-6xl md:text-7xl">
            {{ $code}}
        </h1>
        <h2 class="text-3x">
            {{ $message }}
        </h2>

        <x-button-link.primary href="{{ route('home') }}" class="my-6 mx-auto inline-block">
            {{ __('Go Back Home') }}
        </x-button-link.primary>
    </div>

</x-layouts.focus-center>
