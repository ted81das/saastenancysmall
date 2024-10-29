<?php

namespace App\Filament\Admin\Resources;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Admin\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Filament\Admin\Resources\UserResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('public_name')
                        ->required()
                        ->nullable()
                        ->helperText('This is the name that will be displayed publicly (for example in blog posts).')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->helperText(fn (string $context): string => ($context !== 'create') ? __('Leave blank to keep the current password.') : '')
                        ->maxLength(255),
                    Forms\Components\Select::make('roles')
                        ->multiple()
                        ->relationship('roles', 'name', modifyQueryUsing: function ($query) {
                            return $query->where('name', 'not like', TenancyPermissionConstants::TENANCY_ROLE_PREFIX.'%');
                        })
                        ->preload(),
                    Forms\Components\Checkbox::make('is_admin')
                        ->label('Is Admin?')
                        ->helperText('If checked, this user will be able to access the admin panel. There has to be at least 1 admin user, so if this field is disabled, you will have to create another admin user first before you can disable this one.')
                        // there has to be at least 1 admin user
                        ->disabled(fn (User $user): bool => $user->is_admin && User::where('is_admin', true)->count() === 1)
                        ->default(false),
                    Forms\Components\Checkbox::make('is_blocked')
                        ->label('Is Blocked?')
                        ->disabled(fn (User $user, string $context): bool => $user->is_admin == true || $context === 'create')
                        ->helperText('If checked, this user will not be able to log in or use any services provided.')
                        ->default(false),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(config('app.datetime_format')),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(config('app.datetime_format')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Impersonate::make()->redirectTo(route('home')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\Admin\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit' => \App\Filament\Admin\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('Users');
    }
}
