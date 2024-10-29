<x-button-link.default
    {{ $attributes }}
    {{ $attributes->merge(['class' => 'relative text-primary-50 after:bg-primary-500 hover:bg-primary-600 before:rounded-full after:rounded-full overflow-hidden inline-flex !p-[2px]']) }}
>
    <span class="absolute inset-[-1000%] animate-[spin_2s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#a655f7_0%,#facd15_50%,#a855f7_100%)]"></span><span class="inline-flex items-center justify-center w-full h-full px-5 py-2 text-sm  text-primary-500 rounded-full cursor-pointer bg-white backdrop-blur-3xl">
        {{ $slot }}
    </span>
</x-button-link.default>
