<x-layouts.email>
    <x-slot name="preview">
        {{ __('Sorry to see you go! :(') }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Hi :name,', ['name' => $subscription->user->name]) }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('We are sad to see you go. Please let us know if there is anything we can do to improve our service.') }}
            </p>

            <p style="margin-top: 16px; padding-top: 12px; padding-bottom: 12px">
                {{ __('Please drop us an email if you have any suggestions or feedback that you would like to share with us at') }}
                <a href="mailto:{{ config('app.support_email') }}">
                    {{ config('app.support_email') }}
                </a>.
            </p>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('If you change your mind in the future, you can always subscribe again from your account dashboard.') }}
            </p>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('Thank you for using our services. We hope to see you again soon!') }}
            </p>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('Sincerely,') }}<br>
                {{ config('app.name') }} {{ __('Team') }}
            </p>
        </td>
    </tr>

</x-layouts.email>
