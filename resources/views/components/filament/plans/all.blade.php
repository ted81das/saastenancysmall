<section class="bg-white dark:bg-gray-900 p-5 dark:text-white">
        <div class="mx-auto max-w-screen-md text-center mb-8 lg:mb-12">
            @if($subscription !== null)
                <h2 class="mb-4 text-xl tracking-tight text-gray-900 dark:text-white">{{ __('You are currently on the') }} <div class="badge badge-primary badge-outline font-bold text-xl p-3">{{ $subscription->plan->product->name  }}</div> {{__('plan.')}}</h2>
            @endif
        </div>
        <div class="plan-switcher tabs tabs-boxed justify-center w-full bg-white mb-4 dark:bg-gray-900">
            @foreach($groupedPlans as $interval => $plans)
                <a class="tab bg-white dark:bg-gray-900 dark:text-white text-black {{$preselectedInterval == $interval ? 'tab-active': ''}}" data-target="plans-{{$interval}}" aria-selected="{{$preselectedInterval == $interval ? 'true' : 'false'}}">{{$interval}}</a>
            @endforeach
        </div>

        @if($isGrouped)
            @foreach($groupedPlans as $interval => $plans)
                <div class="plans-container plans-{{$interval}} {{$preselectedInterval == $interval ? '': 'hidden'}}  grid max-w-md gap-10 row-gap-5 lg:max-w-screen-lg sm:row-gap-10 lg:grid-cols-3 xl:max-w-screen-lg sm:mx-auto dark:text-white pt-5 pb-5">
                    @foreach($plans as $plan)
                        <x-filament.plans.one :plan="$plan" :subscription="$subscription" />
                    @endforeach
                </div>
            @endforeach
        @else

            <div class="grid max-w-md gap-10 row-gap-5 lg:max-w-screen-lg sm:row-gap-10 lg:grid-cols-3 xl:max-w-screen-lg sm:mx-auto dark:text-white">
                @foreach($plans as $plan)
                        <x-filament.plans.one :plan="$plan" :subscription="$subscription" :featured="$featured == $plan->product->slug"/>
                @endforeach
            </div>
        @endif

</section>

