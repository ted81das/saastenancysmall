<x-nav.item route="#features">{{ __('Features') }}</x-nav.item>
<x-nav.item route="#tech-stack">{{ __('Tech Stack') }}</x-nav.item>
<x-nav.item route="#pricing">{{ __('Pricing') }}</x-nav.item>
<x-nav.item route="#faq">{{ __('FAQ') }}</x-nav.item>
<x-nav.item route="roadmap">{{ __('Roadmap') }}</x-nav.item>
<x-nav.item route="blog">{{ __('Blog') }}</x-nav.item>
@guest
    <x-nav.item route="login" class="md:hidden">{{ __('Login') }}</x-nav.item>
@endguest
