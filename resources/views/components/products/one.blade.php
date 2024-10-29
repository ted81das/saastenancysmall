@if ($product)
    <div {{ $attributes->merge(['class' => 'max-w-none md:max-w-lg border border-primary-500 rounded-2xl p-8 mt-6 mx-4 md:mx-auto']) }}>

        <x-heading.h4 class="text-center">
            {{$product->name}}
        </x-heading.h4>

        <p class="text-center pt-3 text-xs">
            {{$product->description}}
        </p>

        <div class="text-center mx-auto mt-6 text-base max-w-fit">
            <ul class="flex flex-col gap-3 flex-wrap justify-center items-center">
                @if($product->features)
                    @foreach($product->features as $feature)
                        <x-features.li-item class="text-left">{{$feature['feature']}}</x-features.li-item>
                    @endforeach
                @endif
            </ul>
        </div>

        <div class="text-center mx-auto">
            <p class="mt-6">
                <span class="ms-1 text-primary-500 text-2xl font-bold">@money($product->prices[0]->price, $product->prices[0]->currency->code)</span>
            </p>

            <x-button-link.primary href="{{route('buy.product', ['productSlug' => $product->slug])}}" class="mt-6 text-lg !py-4 px-6">
                {{ __('Get :name Now', ['name' => $product->name]) }}
            </x-button-link.primary>

            @if (!empty($extraDescription))
                <div class="flex text-center items-center flex-col">
                    <div>
                        {{$extraDescription}}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endif
