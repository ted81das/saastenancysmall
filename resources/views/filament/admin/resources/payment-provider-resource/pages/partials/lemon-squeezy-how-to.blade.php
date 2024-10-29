<div class="px-4">
    <p class="pb-4">
        {{__('To integrate Lemon Squeezy with your application, you need to do the following steps:')}}
    </p>
    <ol class="list-decimal ">
        <li class="pb-4">
            <strong>
                {{ __('Login to ') }} <a href="https://app.lemonsqueezy.com/" target="_blank" class="text-blue-500 hover:underline">{{ __('Lemon Squeezy Dashboard') }}</a>
            </strong>
        </li>
        <li class="pb-4">
            <strong>
                {{ __('API key') }}
            </strong>
            <p>
                {{ __('On the left menu, click on "Settings" > "API". Click on the "+" sign on the right to create a new API key. Give the API key a name and click "Create API Key". Copy the generated key into the form in the field called "API key".') }}
            </p>
        </li>
        <li class="pb-4">
            <strong>
                {{ __('Store ID') }}
            </strong>
            <p>
                {{ __('On the same page, on the left menu, click on "Settings" > "Stores". Copy the store id ("#xxxxx") without the # into the "Store ID" field.') }}
            </p>
        </li>
        <li class="pb-4">
            <strong>
                {{ __('Signing Secret') }}
            </strong>
            <p>
                {{ __('On the same page, on the left menu, click on "Settings" > "Webhooks". Click on "+" to create a new webhook and enter the URL below.') }}
                <code class="bg-gray-100 px-4 py-2 block my-4 overflow-x-scroll">
                    {{ route('payments-providers.lemon-squeezy.webhook') }}
                </code>
            </p>
            <p class="mt-4">
                {{ __('Generate a new webhook signing secret and copy it into the form in the field called "Signing Secret" and also into the "Signing secret" field in the Lemon Squeezy Webhook creation form.') }}
                <br>
                <br>
                {{ __('You can use ') }}
                <a href="https://www.lastpass.com/features/password-generator" target="_blank" class="text-blue-500 hover:underline">{{ __('LastPass') }}</a> {{ __(' to generate a secure webhook signing secret.') }}
            </p>
            <ul class="list-disc ps-4 mt-4">
                <li>
                    order_created
                </li>
                <li>
                    order_refunded
                </li>
                <li>
                    subscription_created
                </li>
                <li>
                    subscription_updated
                </li>
                <li>
                    subscription_cancelled
                </li>
                <li>
                    subscription_resumed
                </li>
                <li>
                    subscription_expired
                </li>
                <li>
                    subscription_paused
                </li>
                <li>
                    subscription_unpaused
                </li>
                <li>
                    subscription_payment_failed
                </li>
                <li>
                    subscription_payment_success
                </li>
                <li>
                    subscription_payment_recovered
                </li>
                <li>
                    subscription_payment_refunded
                </li>
                <li>
                    subscription_plan_changed
                </li>
            </ul>

            <p class="mt-4">
                {{ __('Click on "Save Webhook" and copy the generated webhook signing secret and enter it into the field in the form.') }}
            </p>
        </li>
    </ol>
</div>
