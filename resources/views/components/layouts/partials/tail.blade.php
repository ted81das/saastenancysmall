@stack('tail')

@vite(['resources/js/app.js'])

@include('components.layouts.partials.analytics')

@include('cookie-consent::index')
