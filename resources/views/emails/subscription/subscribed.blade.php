<x-layouts.email>
    <x-slot name="preview">
        {{ __('Welcome to :app!', ['app' => config('app.name')]) }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Welcome to :app!', ['app' => config('app.name')]) }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('Welcome aboard! We are thrilled to have you as a valued member of :app. Your subscription to our ":plan_name" plan has been successfully processed, and we\'re excited to help you unlock the full potential of our platform.', ['app' => config('app.name'), 'plan_name' => $subscription->plan->name]) }}
            </p>

            <p style="margin-top: 16px; padding-top: 12px; padding-bottom: 12px">
                {{ __('Our support team is here to assist you with any questions or concerns. Feel free to reach out to us at ') }}
                <a href="mailto:{{ config('app.support_email') }}">
                    {{ config('app.support_email') }}
                </a>.
            </p>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('Your feedback is essential to us. If you have any suggestions, feature requests, or thoughts on how we can enhance your experience, please don\'t hesitate to let us know. We value your input.') }}
            </p>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('Sincerely,') }}<br>
                {{ config('app.name') }} {{ __('Team') }}
            </p>
        </td>
    </tr>

</x-layouts.email>
