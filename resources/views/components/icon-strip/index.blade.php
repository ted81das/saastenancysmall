<div {{ $attributes->merge(['class' => ' mx-auto overflow-x-auto scrollbar-hide px-6']) }}>
    <div class="flex gap-8">
        {{ $slot }}
    </div>
</div>
