<footer class="bg-primary-500 text-white mt-12">
    <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="/" class="flex items-center">
                    <img src="{{asset(config('app.logo.light') )}}" class="h-6 me-3" alt="Logo" />
                </a>
            </div>
            <ul class="flex flex-wrap gap-6 text-primary-100 dark:text-gray-400 text-sm mt-2">
                <li class="mb-4">
                    <a href="{{route('blog')}}" class="text-primary-100 hover:text-primary-50">{{ __('Blog') }}</a>
                </li>
                <li class="mb-4">
                    <a href="{{route('privacy-policy')}}" class="text-primary-100 hover:text-primary-50">{{ __('Privacy Policy') }}</a>
                </li>
                <li>
                    <a href="{{route('terms-of-service')}}" class="text-primary-100 hover:hover:text-primary-50">{{ __('Terms of Service') }}</a>
                </li>
            </ul>
        </div>
        <hr class="my-6 border-primary-300 sm:mx-auto lg:my-8" />
        <div class="sm:flex sm:items-center sm:justify-between">
          <span class="text-xs text-primary-100 sm:text-center dark:text-gray-400">© {{ date('Y') }} <a href="/" class="hover:underline text-primary-100">{{ config('app.name') }}™</a>. {{ __('All rights reserved.') }}
          </span>
            <div class="flex gap-3 mt-4 sm:justify-center sm:mt-0">
                @if (!empty(config('app.social_links.facebook')))
                    <x-link.social-icon name="facebook" title="{{ __('Facebook page') }}" link="{{config('app.social_links.facebook')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.instagram')))
                    <x-link.social-icon name="instagram" title="{{ __('Instagram page') }}" link="{{config('app.social_links.instagram')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.youtube')))
                    <x-link.social-icon name="youtube" title="{{ __('YouTube page') }}" link="{{config('app.social_links.youtube')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.x')))
                    <x-link.social-icon name="x" title="{{ __('Twitter page') }}" link="{{config('app.social_links.x')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.linkedin')))
                    <x-link.social-icon name="linkedin" title="{{ __('Linkedin page') }}" link="{{config('app.social_links.linkedin')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.github')))
                    <x-link.social-icon name="github" title="{{ __('Github page') }}" link="{{config('app.social_links.github')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
                @if (!empty(config('app.social_links.discord')))
                    <x-link.social-icon name="discord" title="{{ __('Discord community') }}" link="{{config('app.social_links.discord')}}" class="text-primary-100 border-primary-200 hover:text-primary-50"/>
                @endif
            </div>
        </div>
    </div>
</footer>
