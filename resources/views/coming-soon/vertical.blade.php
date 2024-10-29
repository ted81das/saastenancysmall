<x-layouts.focus-center class="md:!max-w-6xl mx-auto overflow-x-hidden relative" :backButton="false">

    <x-effect.gradient class="!-start-1/3 -top-50 !w-2/4"/>
    <x-effect.gradient class="!-end-1/3 -top-72 !w-2/4"/>

    <div class="mx-auto md:max-w-6xl text-center mt-8">
        <div class="mx-4">
            <x-pill class="text-primary-500 bg-primary-50">{{ __('Launching soon 🚀') }}</x-pill>
            <x-heading.h1 class="mt-4 text-primary-500 font-bold">
                {{ __('Build your SaaS') }}
                <br class="hidden sm:block">
                {{ __('with SaaSykit') }}
            </x-heading.h1>

            <div class="text-center mx-auto md:max-w-4xl mt-8">
                <p class="text-primary-900 m-3">{{ __('SaaSykit is built using the beautiful Laravel framework (using TALL) and offers an intuitive Filament admin panel that houses all the pre-built components like product, plans, discounts, payment providers, email providers, transactions, blog, user & role management, and much more.') }}</p>
            </div>

            <div class="card md:max-w-2xl bg-base-100 shadow-xl bg-gradient-to-b from-primary-50 to-from-primary-100 my-8 mx-auto">
                <div class="card-body">
                    <x-heading.h4 class="mt-4">
                        {{ __('Sounds Interesting?') }}
                    </x-heading.h4>

                    <div class="flex flex-wrap md:flex-nowrap gap-3 items-center justify-center mt-4">
                        <label class="input input-bordered flex items-center gap-2 md:w-72 w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z" /><path d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z" /></svg>
                            <input type="text" class="grow" placeholder="{{ __('Your email')}}" />
                        </label>

                        <x-button-link.primary href="" class="!py-3">
                            {{ __('Subscribe') }}
                        </x-button-link.primary>
                    </div>

                    <p class="mt-4">
                        {{ __('Sign up to get notified when we launch.') }}
                    </p>
                    <p class="mt-4">
                        {{ __('We ') }} <span class="font-bold">{{ __('NEVER') }}</span> {{ __('spam. Promise.') }}
                    </p>
                </div>
            </div>

        </div>
    </div>

</x-layouts.focus-center>
