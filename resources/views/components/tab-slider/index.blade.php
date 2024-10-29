<div {{$attributes->merge(['class' => 'tab-slider mx-auto px-8 sm:px-0'])}} >
    <div class="mx-auto">
        <div class="overflow-x-auto overscroll-contain scrollbar-hide">
            <div
                role="tablist"
                aria-label="tabs"
                class="w-max mx-auto h-12 flex flex-nowrap items-center px-1 rounded-full bg-neutral-200  transition overflow-hidden"
            >
                {{ $tabNames }}
            </div>
        </div>
        <div class="">
            {{ $slot }}
        </div>
    </div>
</div>
