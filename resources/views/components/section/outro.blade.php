<div {{ $attributes->merge(['class' => 'mx-auto max-w-none md:max-w-6xl p-4']) }}>
    <div class="bg-primary-500 rounded-3xl relative my-10 bg-gradient-to-r from-primary-600 to-primary-300 mx-auto py-12 p-4">
        {{ $slot }}
    </div>
</div>
