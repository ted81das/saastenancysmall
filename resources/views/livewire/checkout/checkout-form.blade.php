<div>
    <form action="" method="post" wire:submit="checkout">
        @csrf

        @guest()
        <div class="mb-4">

            <x-heading.h2 class="text-primary-900 !text-xl">
                {{ __('Enter your details') }}
            </x-heading.h2>

            <div class="relative rounded-2xl border border-natural-300 mt-4 overflow-hidden p-6">

                @if (!empty($intro))
                    <div class="mb-4 text-sm">
                        {{ $intro }}
                    </div>
                @endif

                <div class="absolute top-0 right-0 p-2">
                    <span wire:loading>
                        <span class="loading loading-spinner loading-xs"></span>
                    </span>
                </div>

                <label class="form-control w-full" for="email">
                    <div class="label">
                        <span class="label-text">{{ __('Email Address') }}</span>
                    </div>
                    <input type="email" class="input input-bordered input-md w-full"  name="email" required id="email" wire:model.blur="email" value="{{ old('email') }}">

                </label>

                @error('email')
                    <span class="text-xs text-red-500" role="alert">
                        {{ $message }}
                    </span>
                @enderror


                @if(!empty($email))
                    <label class="form-control w-full" for="password">
                        <div class="label">
                            <span class="label-text">{{ __('Password') }}</span>
                        </div>
                        <input type="password" class="input input-bordered input-md w-full" name="password" required id="password" wire:model="password" >
                    </label>


                    @error('password')
                        <span class="text-xs text-red-500 ms-1" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                @endif

                    @if ($userExists)
                        <div class="mt-2 ms-1 text-xs text-neutral-400">{{ __('You are already registered, enter your password.') }}</div>
                    @elseif(!empty($email))
                        <div class="mt-2 ms-1 text-xs text-neutral-400">{{ __('Enter a password for your new account.') }}</div>
                    @endif

                @if($userExists)
                    @if (Route::has('password.request'))
                        <div class="text-end">
                            <a class="text-primary-500 text-xs" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        </div>
                    @endif
                @endif


                @if(!$userExists || empty($email))
                    <label class="form-control w-full" for="name">
                        <div class="label">
                            <span class="label-text">{{ __('Your Name') }}</span>
                        </div>
                        <input type="text" class="input input-bordered input-md w-full" name="name" required id="name" wire:model="name" value="{{ old('name') }}">
                    </label>

                    @error('name')
                        <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                @endif


                @if (config('app.recaptcha_enabled'))

                    <div wire:ignore>
                        <input type="text" wire:model="recaptcha" x-on:captcha-success.window="$wire.recaptcha = $event.detail.token" hidden>
                        <div class="my-4">
                            {!! htmlFormSnippet([
                                "callback" => "onRecaptchaSuccess"
                            ]) !!}
                        </div>
                    </div>

                    @error('g-recaptcha-response')
                    <span class="text-xs text-red-500" role="alert">
                                {{ $message }}
                            </span>
                    @enderror

                @endif

                @push('tail')
                    <script>

                        function onRecaptchaSuccess(token) {
                            Livewire.dispatch('captcha-success', { token: token });
                        }

                        document.addEventListener('livewire:initialized', () => {
                            Livewire.on('reset-recaptcha', (event) => {
                                // wait .5 seconds before resetting the recaptcha
                                setTimeout(() => {
                                    grecaptcha.reset();
                                }, 500);
                            });
                        });

                    </script>
                @endpush

                @push('tail')
                    {!! htmlScriptTagJsApi() !!}
                @endpush

                @if(empty($email))
                    <x-auth.social-login>
                        <x-slot name="before">
                            <div class="flex flex-col w-full">
                                <div class="divider">{{ __('or') }}</div>
                            </div>
                        </x-slot>
                    </x-auth.social-login>
                @endif

            </div>
        </div>

        @endguest

        <x-heading.h2 class="text-primary-900 !text-xl">
            {{ __('Pay with') }}
        </x-heading.h2>

        <div class="rounded-2xl border border-natural-300 mt-4 overflow-hidden">

            @foreach($paymentProviders as $paymentProvider)
                <div class="border-b border-natural-300 p-4">
                    <div class="form-control">
                        <label class="label cursor-pointer">
                                    <span class="label-text ps-4 flex flex-col gap-3">
                                        <span class="text-xl flex flex-row gap-3">
                                            <span>
                                                {{ $paymentProvider->getName() }}
                                            </span>
                                            <span class="-m-2">
                                                <img src="{{asset('images/payment-providers/' . $paymentProvider->getSlug() . '.png')}}" alt="{{ $paymentProvider->getName() }}" class="h-6 grayscale">
                                            </span>
                                        </span>
                                        @if ($paymentProvider->isRedirectProvider())
                                            <span class="">{{ __('You will be redirected to complete your payment.') }}</span>
                                        @endif
                                        @if ($paymentProvider->isOverlayProvider())
                                            <span class="">{{ __('You will be asked to enter your payment details in a secure overlay.') }}</span>
                                        @endif
                                    </span>
                            <input type="radio"
                                   value="{{ $paymentProvider->getSlug() }}"
                                   class="radio checked:bg-primary-500"
                                   name="paymentProvider"
                                   wire:model="paymentProvider"
                            />

                        </label>
                    </div>
                </div>

            @endforeach


            @foreach($paymentProviders as $paymentProvider)
                @includeIf('payment-providers.' . $paymentProvider->getSlug())
            @endforeach

        </div>

        <p class="text-xs text-neutral-600 p-4">
            {{ __('By continuing, you agree to our') }} <a target="_blank" href="{{route('terms-of-service')}}" class="text-primary-900 underline">{{ __('Terms of Service') }}</a> {{ __('and') }} <a target="_blank" href="{{route('privacy-policy')}}" class="text-primary-900 underline">{{ __('Privacy Policy') }}</a>.
        </p>

        <x-button-link.primary class="inline-block !w-full my-4" elementType="button" type="submit">
            {{ __('Confirm & Pay') }}
        </x-button-link.primary>
    </form>


</div>
