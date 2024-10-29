<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 md:p-10 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <p class="text-xs mt-2 text-end">{{__('No account?')}} <a class="text-primary-500 font-bold" href="{{ route('register') }}">{{__('Register')}}</a></p>

                    <x-input.field label="{{ __('Email Address') }}" type="email" name="email"
                                   value="{{ old('email') }}" required autofocus="true" class="my-2"
                                   autocomplete="email" max-width="w-full"/>
                    @error('email')
                    <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <x-input.field label="{{ __('Password') }}" type="password" name="password" required class="my-2"  max-width="w-full"/>

                    @error('password')
                        <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    @if (config('app.recaptcha_enabled'))
                        <div class="my-4">
                            {!! htmlFormSnippet() !!}
                        </div>

                        @error('g-recaptcha-response')
                            <span class="text-xs text-red-500" role="alert">
                                {{ $message }}
                            </span>
                        @enderror

                        @push('tail')
                            {!! htmlScriptTagJsApi() !!}
                        @endpush

                    @endif

                    <div class="my-3 flex flex-wrap gap-2 justify-between text-sm">
                        <div class="flex gap-2">
                            <input class="checkbox checkbox-sm" type="checkbox" name="remember"
                                   id="remember" {{ old('remember') ? 'checked' : '' }}>

                            <label class="text-sm" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                        <div>
                            @if (Route::has('password.request'))
                                <a class="text-primary-500 text-xs" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <x-button-link.primary class="inline-block !w-full my-2" elementType="button" type="submit">
                        {{ __('Login') }}
                    </x-button-link.primary>



                    <x-auth.social-login>
                        <x-slot name="before">
                            <div class="flex flex-col w-full">
                                <div class="divider">{{ __('or') }}</div>
                            </div>
                        </x-slot>
                    </x-auth.social-login>

                </form>
            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="!text-3xl md:!text-4xl !font-semibold">
                {{ __('Login.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('It\'s great to see you back again :)') }}
            </p>
        </div>
    </x-slot>

</x-layouts.focus>
