<div>
    <div class="overflow-x-auto relative">

        @if($invitations->isEmpty())
            <div class="text-center p-8">
                <p class="">{{ __('You have no invitations.') }}</p>
            </div>
        @else

            @error('invitation')
                <div class="alert alert-error p-2 px-4 text-xs text-white">
                    {{ $message }}
                </div>
            @enderror

            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Team') }}</th>
                        <th>{{ __('Inviter') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($invitations as $invitation)
                    <tr>
                        <td>{{ $invitation->tenant->name }}</td>
                        <td>{{ $invitation->user->name }}</td>
                        <td class="text-end flex gap-4 items-end justify-end">
                            <x-button-link.primary wire:click="acceptInvitation('{{ $invitation->uuid }}')">
                                {{ __('Accept') }}
                                <div wire:loading wire:target="acceptInvitation('{{ $invitation->uuid }}')">
                                    <span class="loading loading-spinner loading-xs"></span>
                                </div>
                            </x-button-link.primary>
                            <x-button-link.secondary class=" bg-red-200 hover:bg-red-300" wire:click="rejectInvitation('{{ $invitation->uuid }}')">
                                {{ __('Reject') }}
                                <div wire:loading wire:target="rejectInvitation('{{ $invitation->uuid }}')">
                                    <span class="loading loading-spinner loading-xs"></span>
                                </div>
                            </x-button-link.secondary>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>

        @endif
    </div>
</div>
