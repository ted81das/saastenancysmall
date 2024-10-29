<x-layouts.focus-center>

    <div class="text-center my-4 mx-4">
        <x-heading.h6 class="text-primary-500">
            {{ __('Pay securely, cancel any time.') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900">
            {{ __('Complete Subscription') }}
        </x-heading.h2>
    </div>


    <x-section.columns class="max-w-none md:max-w-6xl flex-wrap-reverse">
        <x-section.column>
            <livewire:checkout.subscription-checkout-form />
        </x-section.column>


        <x-section.column>
            <div class="md:sticky md:top-2">
                <x-heading.h2 class="text-primary-900 !text-xl">
                    {{ __('Plan details') }}
                </x-heading.h2>

                <div class="rounded-2xl border border-natural-300 mt-4 overflow-hidden p-6">

                    <div class="flex flex-row gap-3">
                        <div class="rounded-2xl text-5xl bg-primary-50 p-2 text-center w-24 h-24 text-primary-500 justify-self-center self-center min-w-[5rem]">
                            {{ substr($plan->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xl font-semibold flex flex-row md:gap-2 flex-wrap">
                                <span class="py-1">
                                    {{ $plan->product->name }}
                                </span>
                                @if ($plan->has_trial)
                                    <span class="text-xs font-normal rounded-full border border-primary-500 text-primary-500 px-2 md:px-4 font-semibold py-1 inline-block self-center">
                                        {{ $plan->trial_interval_count }} {{ $plan->trialInterval()->firstOrFail()->name }} {{ __(' free trial included') }}
                                    </span>
                                @endif
                            </span>
                            @if ($plan->interval_count > 1)
                                <span class="text-xs">{{ $plan->interval_count }} {{ ucfirst($plan->interval->name) }}</span>
                            @else
                                <span class="text-xs">{{ ucfirst($plan->interval->adverb) }} {{ __('subscription.') }}</span>
                            @endif

                            <span class="text-xs">
                                {{ __('Starts immediately.') }}
                            </span>

                        </div>
                    </div>

                    <div class="flex gap-4">

                        @inject('tenantCreationManager', 'App\Services\TenantCreationManager')

                        @if ($tenantCreationManager->findUserTenantsForNewSubscription(auth()->user())->count() > 0)
                            <livewire:checkout.subscription-tenant-picker />
                        @endif

                        @if ($plan->type === \App\Constants\PlanType::SEAT_BASED->value)
                            <livewire:checkout.subscription-seats :plan="$plan" />
                        @endif

                    </div>

                    <div class="text-primary-900 font-semibold my-4">
                        {{ __('What you get:') }}
                    </div>
                    <div>
                        <ul class="flex flex-col items-start gap-3">
                            @if ($plan->product->features)
                                @foreach($plan->product->features as $feature)
                                    <x-features.li-item>{{ $feature['feature'] }}</x-features.li-item>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <livewire:checkout.subscription-totals :totals="$totals" :plan="$plan" page="{{request()->fullUrl()}}"/>

                </div>
            </div>

        </x-section.column>

    </x-section.columns>

</x-layouts.focus-center>
