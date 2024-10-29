<div class="px-4">
    <p class="pb-4">
        {{ __('To integrate Gitlab with your application, check out ') }}

        <a href="https://docs.gitlab.com/ee/integration/oauth_provider.html" target="_blank"
            class="text-blue-500 hover:underline">
            {{ __('getting access to the Gitlab API.') }}
        </a>
    </p>
    <p>
        {{ __('When prompted to enter a redirect URI, use the following:') }}
        <code class="bg-gray-100 px-4 py-2 block my-4 overflow-x-scroll dark:bg-gray-800 dark:text-gray-200">
            {{ config('app.url') . config('services.gitlab.redirect') }}
        </code>
    </p>
</div>
