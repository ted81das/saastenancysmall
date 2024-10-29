@props([
    'link' => '#',
])

<div {{ $attributes->merge(['class' => 'flex flex-col mt-6'])  }}>
    <div class="flex gap-2 items-center justify-center">
        <a class="flex items-center justify-center" href="{{ $link }}">
            {{ $avatars }}
        </a>
        <a class="flex gap-1 w-24 cursor-pointer" href="{{ $link }}">
            @for ($i = 0; $i < 5; $i++)
                @svg('star')
            @endfor
        </a>
    </div>
    <div class="mt-2 text-primary-50 text-sm italic">
        {{ $slot }}
    </div>
</div>
