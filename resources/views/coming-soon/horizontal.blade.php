<x-layouts.focus-center class="md:!max-w-6xl mx-auto overflow-x-hidden" :backButton="false">

    <x-effect.gradient class="-end-72 -top-16 !w-1/3 !opacity-70"/>

    <div class="md:max-w-6xl mx-auto mt-8">
        <div class="flex flex-wrap md:flex-nowrap gap-8 mx-4">
            <div class="md:basis-1/2">
                <x-pill class="text-primary-500 bg-primary-50">{{ __('Launching soon 🚀') }}</x-pill>
                <x-heading.h1 class="mt-4 text-primary-500 font-bold">
                    {{ __('Build ') }} <span class="">{{ __('your SaaS') }}</span>
                    <br class="hidden sm:block">
                    {{ __('with SaaSykit') }}
                </x-heading.h1>


                <div class="mt-8">
                    <p class="text-primary-900">{{ __('SaaSykit is built using the beautiful Laravel framework (using TALL) and offers an intuitive Filament admin panel that houses all the pre-built components like product, plans, discounts, payment providers, email providers, transactions, blog, user & role management, and much more.') }}</p>
                </div>
            </div>

            <div class="md:basis-1/2">
                <div class="card bg-base-100 shadow-xl  md:mt-8">
                    <div class="card-body ">
                        <x-heading.h4 class="mt-4">
                            {{ __('Sounds Interesting?') }}
                        </x-heading.h4>

                        <div class="flex flex-wrap md:flex-nowrap gap-3 items-center mt-4">

                            <label class="input input-bordered flex items-center gap-2 w-full">
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

        <x-heading.h3 class="mt-24 text-center">
            {{ __('How it works') }}
        </x-heading.h3>

        <x-section.columns class="max-w-none md:max-w-6xl mt-4">
            <x-section.column class="flex flex-col items-center justify-center text-center">
                <x-icon.fancy name="one" type="secondary" class="w-1/4 mx-auto" />
                <x-heading.h3 class="mx-auto pt-2">
                    {{ __('Order') }}
                </x-heading.h3>
                <p class="mt-2">{{ __('Order your SaaSykit license.') }}</p>
            </x-section.column>

            <x-section.column class="flex flex-col items-center justify-center text-center">
                <x-icon.fancy name="two" type="secondary" class="w-1/4 mx-auto" />
                <x-heading.h3 class="mx-auto pt-2">
                    {{ __('Build') }}
                </x-heading.h3>
                <p class="mt-2">{{ __('Build your SaaS with SaaSykit.') }}</p>
            </x-section.column>

            <x-section.column class="flex flex-col items-center justify-center text-center">
                <x-icon.fancy name="three" type="secondary" class="w-1/4 mx-auto" />
                <x-heading.h3 class="mx-auto pt-2">
                    {{ __('Launch') }}
                </x-heading.h3>
                <p class="mt-2">{{ __('Launch your SaaS and start earning.') }}</p>
            </x-section.column>

        </x-section.columns>
    </div>


</x-layouts.focus-center>
