@script
<script>
    $wire.on('start-overlay-checkout', (event) => {
        console.log(event.paymentProvider);
        if (event.paymentProvider === 'paddle') {
            startPaddleCheckout(
                event.email,
                event.initData.productDetails,
                event.successUrl,
                event.subscriptionUuid,
                event.orderUuid,
                event.initData.paddleDiscountId ?? null,
            );
        }
    });

</script>
@endscript

@push('head')

    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>

    <script>
        function startPaddleCheckout(
            email,
            productDetails,
            successUrl,
            subscriptionUuid = null,
            orderUuid = null,
            paddleDiscountId = null,
        ) {

            let sandbox = {{ config('services.paddle.is_sandbox') ? 'true' : 'false' }};

            if (sandbox) {
                Paddle.Environment.set("sandbox");
            }

            Paddle.Setup({
                token: '{{ config('services.paddle.client_side_token') }}',
                checkout: {
                    settings: {
                        displayMode: "overlay",
                        theme: "light",
                    }
                },
                eventCallback: function(data) {
                    switch (data.name) {
                        case "checkout.completed":

                            setTimeout(function() {
                                window.location.href = successUrl;
                            }, 2000);

                            break;
                        case "checkout.closed":
                            window.location.reload();

                    }
                }
            });

            let customData = {};

            if (typeof subscriptionUuid != null) {
                customData.subscriptionUuid = subscriptionUuid;
            }

            if (typeof orderUuid != null) {
                customData.orderUuid = orderUuid;
            }

            let items = [];
            for (let i = 0; i < productDetails.length; i++) {
                items.push({
                    priceId: productDetails[i].paddlePriceId,
                    quantity: productDetails[i].quantity
                });
            }

            Paddle.Checkout.open({
                settings: {
                    successUrl: successUrl,
                },
                items: items,
                customData: customData,
                customer: {
                    email: email
                },
                discountId: paddleDiscountId,
            });
        }
    </script>
@endpush




