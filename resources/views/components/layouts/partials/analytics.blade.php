@if ( !config('cookie-consent.enabled') ||
      (config('cookie-consent.enabled') && \Illuminate\Support\Facades\Cookie::get(config('cookie-consent.cookie_name')) !== null)
)
    @if (!empty(config('app.google_tracking_id')))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{config('app.google_tracking_id')}}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{config('app.google_tracking_id')}}');
        </script>
    @endif

    @if (!empty(config('app.tracking_scripts')))
        {!! config('app.tracking_scripts') !!}
    @endif

@endif
