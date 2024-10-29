<div>
    @inject('roadmapManager', 'App\Services\RoadmapManager')

    <div class="border border-neutral-200 rounded-lg p-4">
        <div class="flex gap-3">

            <x-roadmap.upvote-box :item="$item"></x-roadmap.upvote-box>

            <div class="flex flex-col gap-1">
                <h3 class="!text-lg !font-semibold">
                    {{ $item->title }}
                </h3>
                <div class="flex gap-2">
                    <span class="max-w-fit text-primary-500 text-xxs uppercase border border-primary-300 rounded-lg px-2 py-1">
                        {{ \App\Mapper\RoadmapMapper::mapStatusForDisplay($item->status) }}
                    </span>
                    <span class="max-w-fit text-secondary-700 text-xxs uppercase border border-secondary-700 rounded-lg px-2 py-1">
                        {{ \App\Mapper\RoadmapMapper::mapTypeForDisplay($item->type) }}
                    </span>
                </div>
            </div>

        </div>
        <div class="flex flex-col gap-1">
            <p class="py-4 text-neutral-500">
                {!!  $roadmapManager->prepareForDisplay($item->description) ?? __('No extra description.') !!}
            </p>

        </div>
    </div>
</div>
