<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 px-4 md:p-0 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <x-input.field label="{{ __('Password') }}" type="password" name="password" required class="my-2"  max-width="w-full" autocomplete="current-password"/>

                    @error('password')
                    <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <div class="my-3 flex flex-wrap gap-2 justify-between text-sm">
                        <div>
                            @if (Route::has('password.request'))
                                <a class="text-primary-500 text-xs" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <x-button-link.primary class="inline-block !w-full my-2" elementType="button" type="submit">
                        {{ __('Confirm Password') }}
                    </x-button-link.primary>

                </form>

            </div>
        </div>
    </x-slot>

    <x-slot name="right">
        <div class="py-4 px-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="!text-3xl md:!text-4xl !font-semibold">
                {{ __('Confirm Password.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('Enter your password to continue.') }}
            </p>
        </div>
    </x-slot>

</x-layouts.focus>
