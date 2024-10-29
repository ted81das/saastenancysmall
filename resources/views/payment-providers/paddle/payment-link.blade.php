<x-layouts.app>

@push('head')
    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
    <script>
        @if(config('services.paddle.is_sandbox'))
            Paddle.Environment.set("sandbox");
        @endif

        document.addEventListener("DOMContentLoaded", (event) => {
            Paddle.Setup({
                token: '{{ config('services.paddle.client_side_token') }}',
                checkout: {
                    settings: {
                        displayMode: "overlay",
                        theme: "light",
                    }
                }
            });
        });

    </script>
@endpush

</x-layouts.app>
