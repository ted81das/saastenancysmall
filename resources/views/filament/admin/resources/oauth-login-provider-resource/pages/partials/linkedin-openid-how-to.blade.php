<div class="px-4">
    <p class="pb-4">
        {{ __('To integrate Linkedin with your application, check out ') }}

        <a href="https://learn.microsoft.com/en-us/linkedin/shared/authentication/client-credentials-flow?context=linkedin%2Fconsumer%2Fcontext"
            target="_blank" class="text-blue-500 hover:underline">
            {{ __('getting access to the LinkedIn API.') }}
        </a>
    </p>
    <p>
        {{ __('On your LinkedIn app, under "Auth" tab -> "Authorized redirect URLs for your app", add the following URL:') }}
        <code class="bg-gray-100 px-4 py-2 block my-4 overflow-x-scroll dark:bg-gray-800 dark:text-gray-200">
            {{ config('app.url') . config('services.linkedin-openid.redirect') }}
        </code>
    </p>
    <p class="pb-4">
        Make sure to request access on your app to <strong>"Sign In with LinkedIn using OpenID Connect"</strong> in order for the login with LinkedIn to work.
    </p>
</div>
