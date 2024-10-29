<?php

namespace Database\Seeders;

use App\Models\OauthLoginProvider;
use Illuminate\Database\Seeder;

class OAuthLoginProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'google'],
            [
                'name' => 'Google',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'facebook'],
            [
                'name' => 'Facebook',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'twitter-oauth-2'],
            [
                'name' => 'Twitter',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'github'],
            [
                'name' => 'Github',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'linkedin-openid'],
            [
                'name' => 'LinkedIn',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'bitbucket'],
            [
                'name' => 'Bitbucket',
            ]
        );

        OauthLoginProvider::updateOrCreate(
            ['provider_name' => 'gitlab'],
            [
                'name' => 'Gitlab',
            ]
        );

        // older legacy linkedin provider
        OauthLoginProvider::where('provider_name', 'linkedin')->delete();
    }
}
