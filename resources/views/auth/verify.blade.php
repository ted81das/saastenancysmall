<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 px-4 md:p-0 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">


                @if (session('sent'))
                    <div role="alert" class="alert my-4 text-sm">
                        @svg('info', 'h-6 w-6')
                        <span>{{ __('A fresh verification link has been sent to your email address.') }}</span>
                    </div>
                @endif

                <x-heading.h2 class="!text-2xl md:!text-3xl !font-semibold mb-4">
                    {{ __('Check your email.') }}
                </x-heading.h2>

                <p>
                    {{ __('Please check your email for a verification link.') }}
                </p>

                <p class="my-4">
                    {{ __('If you did not receive the email, you can resend it.') }}
                </p>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-button-link.primary class="inline-block !w-full my-2" elementType="button" type="submit">
                        {{ __('Send another verification email') }}
                    </x-button-link.primary>
                </form>

            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 px-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="!text-3xl md:!text-4xl !font-semibold">
                {{ __('Verify Your Email Address.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('This is necessary to continue your registration.') }}
            </p>
        </div>
    </x-slot>

</x-layouts.focus>
