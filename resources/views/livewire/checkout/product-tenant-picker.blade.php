<div>
    <div class="my-3 relative">
        <label class="form-control">
            <div class="label">
                <span class="label-text">{{ __('Pick a workspace:') }}</span>
            </div>

            <select wire:model.blur="tenant" class="select select-bordered">
                @foreach ($userTenants as $tenant)
                    <option value="{{ $tenant->uuid }}">{{ $tenant->name }}</option>
                @endforeach
                <option value="">{{ __('Create a new workspace') }}</option>
            </select>

            <div class="absolute top-0 right-0 p-2">
                <span wire:loading>
                    <span class="loading loading-spinner loading-xs"></span>
                </span>
            </div>

            @error('tenant')
                <span class="text-xs text-red-500 mt-1" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </label>
    </div>
</div>
