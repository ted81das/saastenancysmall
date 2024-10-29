<x-layouts.email>
    <x-slot name="preview">
        {{ __('Reset your password') }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Reset your password') }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('You are receiving this email because we received a password reset request for your account.') }}
            </p>

            <div style="text-align: center;">
                <a href="{{ $url }}" style="margin-top: 24px; margin-bottom: 24px; display: inline-block; border-radius: 16px; background-color: {{config('app.email_color_tint')}}; padding: 8px 24px; font-size: 20px; color: #fff; text-decoration-line: none">
                    {{ __('Reset Password') }}
                </a>
            </div>

            <p style="padding-top: 12px; padding-bottom: 12px;">
                {{ __('This password reset link will expire in 60 minutes.') }}

                <br/>

                {{ __('If you did not request a password reset, no further action is required.') }}
            </p>

            <div role="separator" style="background-color: #e2e8f0; height: 1px; line-height: 1px; margin: 32px 0;">&zwj;</div>

            <p style="">
                {{ __('If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:') }} <a href="{{ $url }}">
                    {{ $url }}
                </a>
            </p>

        </td>
    </tr>

</x-layouts.email>
