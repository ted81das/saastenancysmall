<x-layouts.email>
    <x-slot name="preview">
        {{ __('Thanks for your order') }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Hello,') }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('Thank you for your order at :app!', ['app' => config('app.name')]) }}
                <br>
                <br>
                {{ __('Order number:')}} {{$order->uuid}}
            </p>
            <div role="separator" style="line-height: 24px">&zwj;</div>

            @php($index = 1)
            @foreach ($order->items as $item)
                <table cellpadding="0" cellspacing="0" role="none" style="margin-top: 5px;">
                    <tr>
                        <td style="width: 7%">
                            #{{ $index++ }}
                        </td>
                        <td>
                            <div style="margin-left: 12px">
                                <div style="display: flex; flex-direction: row; flex-wrap: wrap; font-size: 20px; font-weight: 600">
                                    <span style="padding-top: 4px; padding-bottom: 4px">
                                          {{ $item->oneTimeProduct->name }}
                                    </span>
                                </div>
                                @if ($item->oneTimeProduct->description)
                                    <div style="font-size: 12px">{{$item->oneTimeProduct->description}}</div>
                                @endif
                                <div style="font-size: 12px; margin-top: 8px">
                                    {{ __('Quantity:') }}  {{ $item->quantity }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            @endforeach

            <div role="separator" style="background-color: #e2e8f0; height: 1px; line-height: 1px; margin: 24px 0">&zwj;</div>

            <table width="100%">
                <tr style="width: 100%">
                    <td align="left">
                        <span class="font-bold">{{ __('Total') }}</span>
                    </td>
                    <td align="right">
                        <span class="font-bold">@money($order->total_amount, $order->currency->code)</span>
                    </td>
                </tr>
            </table>

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
