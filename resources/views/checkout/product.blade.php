<x-layouts.focus-center>

    <div class="text-center mx-4">
        <x-heading.h6 class="text-primary-500">
            {{ __('Pay securely.') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900">
            {{ __('Complete your purchase') }}
        </x-heading.h2>
    </div>

    <x-section.columns class="max-w-none md:max-w-6xl flex-wrap-reverse">
        <x-section.column>
            <livewire:checkout.product-checkout-form />
        </x-section.column>


        <x-section.column>
            <div class="md:sticky md:top-2">
                <x-heading.h2 class="text-primary-900 !text-xl">
                    {{ __('Product Details') }}
                </x-heading.h2>

                <div class="rounded-2xl border border-natural-300 mt-4 overflow-hidden p-6">
                    @php
                        $cartItem = $cartDto->items[0];
                    @endphp

                    <div class="flex flex-row gap-3">
                        <div class="rounded-2xl text-5xl bg-primary-50 p-2 text-center w-24 h-24 text-primary-500 justify-self-center self-center min-w-[5rem]">
                            {{ substr($product->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xl font-semibold flex flex-row md:gap-2 flex-wrap">
                                <span class="py-1">
                                    {{ $product->name }}
                                </span>
                            </span>

                            @if ($product->description)
                                <span class="text-xs">{{ $product->description }}</span>
                            @endif

                            <span class="text-xs">
                                {{ __('Quantity:') }} {{ $cartItem->quantity }}
                            </span>

                        </div>
                    </div>

                    <div class="flex gap-4">

                        @inject('tenantCreationManager', 'App\Services\TenantCreationManager')

                        @if ($tenantCreationManager->findUserTenantsForNewOrder(auth()->user())->count() > 0)
                            <livewire:checkout.product-tenant-picker />
                        @endif

                    </div>

                    <div class="text-primary-900 my-4">
                        {{ __('What you get:') }}
                    </div>
                    <div>
                        <ul class="flex flex-col items-start gap-3">
                            @if ($product->features)
                                @foreach($product->features as $feature)
                                    <x-features.li-item>{{ $feature['feature'] }}</x-features.li-item>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <livewire:checkout.product-totals :totals="$totals" :product="$product" page="{{request()->fullUrl()}}"/>

                </div>
            </div>

        </x-section.column>

    </x-section.columns>

</x-layouts.focus-center>
