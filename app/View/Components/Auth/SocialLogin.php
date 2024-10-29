<?php

namespace App\View\Components\Auth;

use App\Models\OauthLoginProvider;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SocialLogin extends Component
{
    public function __construct(

    ) {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|string
    {
        return view('components.auth.social-login', [
            'oauthProviders' => OauthLoginProvider::where('enabled', true)->get(),
        ]);
    }
}
