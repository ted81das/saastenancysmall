@props(['before' => ''])


<div {{ $attributes->merge(['class' => '']) }}>
    @if (count($oauthProviders) > 0)
        {{ $before }}
    @endif

    @foreach ($oauthProviders as $oauthProvider)
        <x-button-link.primary-outline href="{{ route('auth.oauth.redirect', $oauthProvider->provider_name) }}" class=" w-full inline-block !border-primary-900 !text-primary-900 hover:!bg-primary-100 my-2">
            <span class="flex flex-row gap-3 items-center justify-center">
                @svg('colored/' . $oauthProvider->provider_name, 'w-5 h-5')
                <span>
                    {{ __('Continue with '.$oauthProvider->name) }}
                </span>
            </span>
        </x-button-link.primary-outline>
    @endforeach
</div>
