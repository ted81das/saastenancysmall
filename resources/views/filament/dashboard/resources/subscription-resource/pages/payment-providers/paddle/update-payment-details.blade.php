<x-filament-panels::page>

    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>

    @if(config('services.paddle.is_sandbox'))
        <script>
            Paddle.Environment.set("sandbox");
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            Paddle.Setup({
                seller: {{ config('services.paddle.vendor_id') }},
                checkout: {
                    settings: {
                        displayMode: "overlay",
                        theme: "light",
                        successUrl: '{{ $successUrl }}',
                    }
                }
            });
        });
    </script>


    <div class="container">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="card-actions justify-center mt-4">
                    <a class="btn btn-primary btn-sm normal-case" href="{{$successUrl}}">
                        {{ __('Back to Subscriptions') }}
                    </a>
                </div>
            </div>
        </div>

    </div>

</x-filament-panels::page>
