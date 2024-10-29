<div>
    @php $isDiscountCodeAdded = !empty($addedCode); @endphp
    <div x-data="{ discountFormVisible: @js($isDiscountCodeAdded) }">
        <div class="text-end">
            <a href="#" class="text-primary-500 text-sm mt-4 mb-2 inline-block" x-on:click.prevent=" discountFormVisible = !discountFormVisible "
               x-show="!discountFormVisible">{{ __('Have a coupon code?') }}</a>
        </div>

        <div class="my-6" x-show="discountFormVisible">
            <hr class="my-4"/>

            @if (session('success'))
                <div class="text-xs flex flex-row gap-2 my-2">
                    @svg('check', 'h-4 w-4 stroke-primary-500')
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="text-xs flex flex-row gap-2 my-2">
                    @svg('error', 'h-4 w-4 stroke-primary-500')
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form wire:submit="add">

                @if ($isDiscountCodeAdded)
                    <div class="flex flex-row items-center gap-3 justify-end">
                        <div class="rounded border-primary-500 py-1 px-2 text-xs border border-dashed">
                            {{ $addedCode }}
                        </div>

                        <a wire:click.prevent="remove" class="!text-primary-500 !border-primary-500 !text-xs !py-1 cursor-pointer">
                            {{ __('Remove Discount') }}
                        </a>
                    </div>
                @else
                    <div class="flex flex-row items-center gap-3">
                        <x-input.field wire:model="code" placeholder="{{ __('Discount code') }}" type="text" class="input-sm !mx-0 !px-0"
                               value="{{$addedCode ?? ''}}" disabled="{{$isDiscountCodeAdded}}"/>

                        <x-button-link.primary-outline elementType="button" type="submit"
                                                       class="!text-primary-500 !border-primary-500 !text-xs !py-1 ">
                            {{ __('Add Discount') }}
                        </x-button-link.primary-outline>
                    </div>
                @endif

            </form>
        </div>
    </div>


    <hr class="mb-6 mt-2">
    <div class="flex flex-row justify-between">
        <div class="text-primary-900">
            {{ __('Price') }}
        </div>
        <div class="text-primary-900">
            @money($subtotal, $currencyCode)
        </div>
    </div>

    @if($discountAmount > 0)
        <div class="flex flex-row justify-between">
            <div class="text-primary-900">
                {{ __('Discount') }}
            </div>
            <div class="text-primary-900">
                @money($discountAmount, $currencyCode)
            </div>
        </div>

        <hr class="my-6">

        <div class="flex flex-row justify-between">
            <div class="text-primary-900">
                {{ __('Total') }}
            </div>
            <div class="text-primary-900">
                @money($amountDue, $currencyCode)
            </div>
        </div>

    @endif

    <hr class="my-6">
    <div class="flex flex-row justify-between">
        <div class="text-primary-500 text-xl font-bold">
            {{ __('Due now') }}
        </div>
        <div class="text-primary-500 text-xl font-bold">
            @money($amountDue, $currencyCode)
        </div>
    </div>


</div>
