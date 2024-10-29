<x-layouts.app>
    <div class="text-center pt-4 pb-0 md:pt-16 md:mb-10">
        <x-heading.h1 class="font-semibold">
            {{ $category->name }}
        </x-heading.h1>
        <p class="pt-4">
            {{ __('Check out our latest news and updates.') }}
        </p>

        <div class="flex gap-3 justify-center pt-6">
            <x-link.social-icon name="x" title="{{ __('Twitter page') }}" link="https://x.com/intent/post?text={{ urlencode(__('Check out the latest news and updates :app blog!', ['app' => config('app.name')])) }}&url={{ urlencode(url()->current()) }}" class="hover:text-primary-500"/>
            <x-link.social-icon name="linkedin" title="{{ __('LinkedIn community') }}" link="https://www.linkedin.com/shareArticle?url={{ urlencode(url()->current()) }}&title={{ urlencode(__('Check out the latest news and updates :app blog!', ['app' => config('app.name')])) }}" class="hover:text-primary-500"/>
        </div>
    </div>

    <x-blog.post-cards :posts="$posts" />

    <div class="mx-auto text-center p-4 md:max-w-lg">
        {{ $posts->links() }}
    </div>

</x-layouts.app>
