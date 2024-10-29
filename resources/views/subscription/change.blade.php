<x-layouts.focus-center>

    <div class="text-center my-4">
        <x-heading.h6 class="text-primary-500">
            {{ __('Pay securely, cancel any time.') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900">
            {{ __('Update Subscription') }}
        </x-heading.h2>
    </div>


    <x-section.columns class="max-w-none md:max-w-6xl flex-wrap-reverse">

        <x-section.column>
            <x-heading.h2 class="text-primary-900 !text-xl">
                {{ __('New Plan details') }}
            </x-heading.h2>

            <div class="rounded-2xl border border-natural-300 mt-4 overflow-hidden p-6">

                <div class="flex flex-row gap-3">
                    <div class="rounded-2xl text-5xl bg-primary-50 p-2 text-center w-24 h-24 text-primary-500 justify-self-center self-center">
                        {{ substr($newPlan->name, 0, 1) }}
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xl font-semibold flex flex-row md:gap-2 flex-wrap">
                            <span class="py-1">
                                {{ $newPlan->product->name }}
                            </span>
                            @if ($newPlan->has_trial)
                                <span class="text-xs font-normal rounded-full border border-primary-500 text-primary-500 px-2 md:px-4 font-semibold py-1 inline-block self-center">
                                    {{ $newPlan->trial_interval_count }} {{ $newPlan->trialInterval()->firstOrFail()->name }} {{ __(' free trial included') }}
                                </span>
                            @endif
                        </span>
                        @if ($newPlan->interval_count > 1)
                            <span class="text-xs">{{ $newPlan->interval_count }} {{ ucfirst($newPlan->interval->name) }}</span>
                        @else
                            <span class="text-xs">{{ ucfirst($newPlan->interval->adverb) }} {{ __('subscription.') }}</span>
                        @endif

                        <span class="text-xs">
                            {{ __('Starts immediately.') }}
                        </span>



                    </div>
                </div>

                <div class="text-primary-900 my-4">
                    {{ __('What you get:') }}
                </div>
                <div>
                    <ul class="flex flex-col items-start gap-3">
                        @if ($newPlan->product->features)
                            @foreach($newPlan->product->features as $feature)
                                <x-features.li-item>{{ $feature['feature'] }}</x-features.li-item>
                            @endforeach
                        @endif
                    </ul>
                </div>



                <div>

                    <hr class="mb-6 mt-2">
                    <div class="flex flex-row justify-between">
                        <div class="text-primary-900">
                            {{ __('New Subscription price') }}
                            @if ($newPlan->type === \App\Constants\PlanType::SEAT_BASED->value)
                                ({{ $totals->quantity }} x @money($totals->pricePerSeat, $totals->currencyCode) / {{ __(' seat') }})
                            @endif
                        </div>
                        <div class="text-primary-900">
                            @money($totals->subtotal, $totals->currencyCode)
                        </div>
                    </div>

                    @if (!$isProrated)
                        <hr class="my-6">
                        <div class="flex flex-row justify-between">
                            <div class="text-primary-500 text-xl font-bold">
                                {{ __('Due now') }}
                            </div>
                            <div class="text-primary-500 text-xl font-bold">
                                @money($totals->amountDue, $totals->currencyCode)
                            </div>
                        </div>
                    @endif

                </div>

                @if ($isProrated)
                    <div role="alert" class="alert mt-4 text-sm">
                        @svg('info', 'h-6 w-6 text-primary-500 mr-2')
                        <span>
                            {{ __('You will be charged a prorated amount that covers the difference between your current plan and your new plan for the remainder of the current billing period.') }}
                        </span>
                    </div>

                @endif

            </div>

            <form action="" method="post">
                @csrf

                <p class="text-xs text-neutral-600 p-4">
                    {{ __('Cancel anytime in account settings at least one day before each renewal date. Plan automatically renews until cancelled. Your billing date may not line up with your apprenticeship start date.') }}
                    {{ __('By continuing, you agree to our') }} <a href="#" class="text-primary-900 underline">{{ __('Terms of Service') }}</a> {{ __('and') }} <a href="#" class="text-primary-900 underline">{{ __('Privacy Policy') }}</a>.
                </p>

                <x-button-link.primary class="inline-block !w-full my-4" elementType="button" type="submit">
                    {{ __('Confirm & Subscribe') }}
                </x-button-link.primary>
            </form>

        </x-section.column>

    </x-section.columns>

</x-layouts.focus-center>
