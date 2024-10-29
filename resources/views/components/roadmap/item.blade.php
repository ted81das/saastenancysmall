<div class="border border-neutral-200 rounded-lg p-4 my-4 flex gap-3" wire:key="item-{{ $item->id }}">

    <x-roadmap.upvote-box :item="$item"></x-roadmap.upvote-box>

    <div class="flex flex-col gap-1">
        <h3 class="!text-lg !font-semibold">
            <a class="text-primary-900 hover:text-primary-500 break-all line-clamp-1" rel="ugc" href="{{route('roadmap.viewItem', ['itemSlug' => $item->slug])}}">
                {{ $item->title }}
            </a>
        </h3>

        <div class="flex gap-2">
            <span class="max-w-fit text-primary-500 text-xxs uppercase border border-primary-300 rounded-lg px-2 py-1">
                {{ \App\Mapper\RoadmapMapper::mapStatusForDisplay($item->status) }}
            </span>
            <span class="max-w-fit text-secondary-700 text-xxs uppercase border border-secondary-700 rounded-lg px-2 py-1">
                {{ \App\Mapper\RoadmapMapper::mapTypeForDisplay($item->type) }}
            </span>
        </div>

        <p class="text-sm py-1 text-neutral-500 line-clamp-2 break-all">
            {{ strip_tags($item->description) ?? '' }}
        </p>

    </div>
</div>
