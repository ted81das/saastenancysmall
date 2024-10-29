@inject('roadmapManager', 'App\Services\RoadmapManager')

@if ($roadmapManager->hasUserUpvoted($item) && $item->status !== \App\Constants\RoadmapItemStatus::COMPLETED->value)
    <div class="text-primary-500 border border-neutral-200 h-16 flex flex-col text-center justify-center items-center px-6 py-2 rounded-lg hover:border-primary-200 transition bg-primary-50">
        <a href="#" class="text-primary-500 flex flex-col text-center justify-center items-center" wire:click.prevent="removeUpvote({{$item->id}})" >
            <span>
                @svg('upvote', 'h-4')
            </span>
            <span class="text-sm font-semibold">
                {{ $item->upvotes ?? 0 }}
            </span>
            <span wire:loading wire:target="removeUpvote({{$item->id}})">
                <span class="loading loading-ring loading-xs"></span>
            </span>
        </a>
    </div>
@else
    @if ($roadmapManager->isUpvotable($item))
        <div class="text-primary-500 border border-neutral-200 h-16 flex flex-col text-center justify-center items-center px-6 py-2 rounded-lg hover:border-primary-200 transition">
            <a href="#" class="text-primary-500 flex flex-col text-center justify-center items-center" wire:click.prevent="upvote({{$item->id}})">
                <span>
                    @svg('upvote', 'h-4')
                </span>
                <span class="text-sm font-semibold">
                    {{ $item->upvotes ?? 0 }}
                </span>
                <span wire:loading wire:target="upvote({{$item->id}})">
                    <span class="loading loading-ring loading-xs"></span>
                </span>
            </a>
        </div>
    @else
        <div class="text-primary-500 border border-neutral-200 h-16 flex flex-col text-center justify-center items-center px-6 py-2 rounded-lg hover:border-primary-200 transition">
            <span class="text-primary-500 flex flex-col text-center justify-center items-center">
                <span>
                    @svg('upvote', 'h-4')
                </span>
                    <span class="text-sm font-semibold">
                    {{ $item->upvotes ?? 0 }}
                </span>
            </span>
        </div>
    @endif
@endif
