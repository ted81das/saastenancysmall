<x-layouts.email>
    <x-slot name="preview">
        {{ __('Don\'t lose your subscription!') }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Don\'t lose your subscription!') }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('Hi :name,', ['name' => $subscription->user->name]) }}
                <br>
                <br>
                {{ __('We were unable to process your payment for your :plan subscription. Please update your payment details to avoid losing access to your account.', ['plan' => $subscription->plan->name]) }}
            </p>

            <div style="text-align: center;">
                <a href="{{ route('filament.dashboard.resources.subscriptions.index', ['tenant' => $subscription->tenant]) }}" style="margin-top: 24px; margin-bottom: 24px; display: inline-block; border-radius: 16px; background-color: {{config('app.email_color_tint')}}; padding: 8px 24px; font-size: 20px; color: #fff; text-decoration-line: none">
                    {{ __('Fix Problem') }}
                </a>
            </div>

            <div role="separator" style="background-color: #e2e8f0; height: 1px; line-height: 1px; margin: 32px 0;">&zwj;</div>
            <p style="margin-top: 16px; padding-top: 12px; padding-bottom: 12px">
                {{ __('Our support team is here to assist you with any questions or concerns. Feel free to reach out to us at ') }}
                <a href="mailto:{{ config('app.support_email') }}">
                    {{ config('app.support_email') }}
                </a>.
            </p>
            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('Sincerely,') }}<br>
                {{ config('app.name') }} {{ __('Team') }}
            </p>
        </td>
    </tr>

</x-layouts.email>
