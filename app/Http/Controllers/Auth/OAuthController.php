<?php

namespace App\Http\Controllers\Auth;

use App\Models\OauthLoginProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends RegisterController
{
    public function redirect(string $provider)
    {
        $providerObj = OauthLoginProvider::where('provider_name', $provider)->firstOrFail();

        if (! $providerObj->enabled) {
            abort(404);
        }

        if (Auth::check()) {
            return redirect()->route('home');
        }

        Redirect::setIntendedUrl(url()->previous());

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $providerObj = OauthLoginProvider::where('provider_name', $provider)->firstOrFail();

        if (! $providerObj->enabled) {
            abort(404);
        }

        $oauthUser = Socialite::driver($provider)->user();

        $isRegistration = false;

        $password = Str::random();
        DB::transaction(function () use ($provider, $oauthUser, $password, &$isRegistration) {
            $user = User::where('email', $oauthUser->email)->first();

            if ($user) {
                $user->update([
                    'name' => $oauthUser->name ?? $user->name ?? $oauthUser->nickname,
                ]);
            } else {
                $user = User::create([
                    'name' => $oauthUser->name ?? $oauthUser->nickname ?? '',
                    'email' => $oauthUser->email,
                    'password' => $password,
                ]);

                $isRegistration = true;
            }

            $user->userParameters()->updateOrCreate(
                ['name' => 'oauth_provider_'.$provider],
                ['value' => $provider]
            );

            if (property_exists($oauthUser, 'id') && $oauthUser->id) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_id'],
                    ['value' => $oauthUser->id]
                );
            }

            if (property_exists($oauthUser, 'token') && $oauthUser->token) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_token'],
                    ['value' => $oauthUser->token]
                );
            }

            if (property_exists($oauthUser, 'refreshToken') && $oauthUser->refreshToken) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_refresh_token'],
                    ['value' => $oauthUser->refreshToken]
                );
            }

            if (property_exists($oauthUser, 'expiresIn') && $oauthUser->expiresIn) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_expires_in'],
                    ['value' => $oauthUser->expiresIn]
                );
            }

            if (property_exists($oauthUser, 'avatar') && $oauthUser->avatar) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_avatar'],
                    ['value' => $oauthUser->avatar]
                );
            }

            if (property_exists($oauthUser, 'nickname') && $oauthUser->nickname) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_nickname'],
                    ['value' => $oauthUser->nickname]
                );
            }

            $user->markEmailAsVerified();

            Auth::login($user);
        });

        if (Redirect::getIntendedUrl()) {
            return redirect()->intended();
        }

        if ($isRegistration) {
            return redirect()->route('registration.thank-you');
        }

        return redirect()->route('home');
    }
}
