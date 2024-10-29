<div class="px-4">
    <p class="pb-4">
        {{ __('To integrate Bitbucket with your application, you need to do the following steps:') }}
    </p>
    <ol class="list-decimal ">
        <li class="pb-4">
            Log in to your Bitbucket instance. Open the Bitbucket Settings page and select Workplace settings.
        </li>
        <li class="pb-4">
            Select OAuth customers in the left navigation, then click the Add consumer button.
        </li>
        <li class="pb-4">
            In the Add OAuth consumer form, provide a name for your Hub service in Bitbucket and optional description.
        </li>
        <li class="pb-4">
            In the Callback URL field, paste the generated URL from the Redirect URI field in Hub.
            <code class="bg-gray-100 px-4 py-2 block my-4 overflow-x-scroll dark:bg-gray-800 dark:text-gray-200">
                {{ config('app.url') . config('services.bitbucket.redirect') }}
            </code>
        </li>
        <li class="pb-4">
            In the Permissions section, enable the Email and Read options in the Account section.
        </li>
        <li class="pb-4">
            Click the Save button.
        </li>
        <li class="pb-4">
            Expand the section for the new consumer to display the Key and Secret.
        </li>
    </ol>
</div>
