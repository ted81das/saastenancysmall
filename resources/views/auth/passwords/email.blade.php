<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 px-4 md:p-0 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">

                @if (session('status'))
                    <div role="alert" class="alert my-4 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <x-input.field label="{{ __('Email Address') }}" type="email" name="email"
                                   value="{{ old('email') }}" required autofocus="true" class="my-2"
                                   autocomplete="email" max-width="w-full"/>

                    @error('email')
                        <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    @if (config('app.recaptcha_enabled'))
                        <div class="my-4">
                            {!! htmlFormSnippet() !!} <!-- reCAPTCHA widget -->
                        </div>

                        @error('g-recaptcha-response')
                            <span class="text-xs text-red-500" role="alert">
                                {{ $message }}
                            </span>
                        @enderror
                    @endif

                    <x-button-link.primary class="inline-block !w-full my-2" elementType="button" type="submit">
                        {{ __('Send Password Reset Link') }}
                    </x-button-link.primary>

                </form>

            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 px-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="!text-3xl md:!text-4xl !font-semibold">
                {{ __('Reset Your Password.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('You will receive an email with a link to reset your password.') }}
            </p>
        </div>
    </x-slot>

    @if (config('app.recaptcha_enabled'))
        @push('tail')
            {!! htmlScriptTagJsApi() !!} <!-- Include reCAPTCHA script -->
        @endpush
    @endif

</x-layouts.focus>
