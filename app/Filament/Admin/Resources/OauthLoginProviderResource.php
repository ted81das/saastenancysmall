<?php

namespace App\Filament\Admin\Resources;

use App\Models\OauthLoginProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OauthLoginProviderResource extends Resource
{
    protected static ?string $model = OauthLoginProvider::class;

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider_name')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('enabled')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->getStateUsing(function (OauthLoginProvider $record) {
                        return new HtmlString(
                            '<div class="flex gap-2">'.
                            ' <img src="'.asset('images/oauth-providers/'.$record->provider_name.'.svg').'"  class="h-6"> '
                            .$record->name
                            .'</div>'
                        );
                    }),
                Tables\Columns\TextColumn::make('provider_name'),
                Tables\Columns\IconColumn::make('enabled')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\ListOauthLoginProviders::route('/'),
            'edit' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\EditOauthLoginProvider::route('/{record}/edit'),
            'google-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\GoogleSettings::route('/google-settings'),
            'github-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\GithubSettings::route('/github-settings'),
            'gitlab-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\GitlabSettings::route('/gitlab-settings'),
            'twitter-oauth-2-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\TwitterSettings::route('/twitter-settings'),
            'linkedin-openid-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\LinkedinSettings::route('/linkedin-settings'),
            'facebook-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\FacebookSettings::route('/facebook-settings'),
            'bitbucket-settings' => \App\Filament\Admin\Resources\OauthLoginProviderResource\Pages\BitbucketSettings::route('/bitbucket-settings'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('Oauth Login Providers');
    }
}
