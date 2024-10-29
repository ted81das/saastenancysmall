<div>
    <x-heading.h3 class="!text-lg">
        {{ __('Suggest a feature / improvement') }}
    </x-heading.h3>

    <form wire:submit="save" class="flex flex-col gap-3 mt-4">

        <div>
            <select class="select select-bordered w-full max-w-xs" wire:model="form.type">
                // loop over the RoadmapItem::TYPES
                @foreach(\App\Constants\RoadmapItemType::cases() as $type)
                    <option value="{{ $type }}">{{ \App\Mapper\RoadmapMapper::mapTypeForDisplay($type) }}</option>
                @endforeach
            </select>
            <div class="text-sm text-red-500 mt-1">
                @error('form.type') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <input type="text" wire:model="form.title" placeholder="{{__('What do you want to suggest?')}}" class="input input-bordered w-full " />

            <div class="text-sm text-red-500 mt-1">
                @error('form.title') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <textarea rows="7" wire:model="form.description" class="textarea textarea-bordered w-full " placeholder="{{ __('Provide more description.') }}"></textarea>

            <div class="text-sm text-red-500 mt-1">
                @error('form.description') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-button-link.primary elementType="button" type="submit">
            {{ __('Suggest') }}
        </x-button-link.primary>

    </form>


</div>

