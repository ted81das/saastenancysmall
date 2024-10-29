<?php

namespace App\Filament\Admin\Resources;

use App\Constants\TenancyPermissionConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->helperText(__('The name of the role. Tenancy roles should start with ":prefix", and only tenancy permissions can be assigned to tenancy roles.', [
                            'prefix' => TenancyPermissionConstants::TENANCY_ROLE_PREFIX,
                        ]))
                        ->disabled(fn (?Model $record) => $record && $record->name === 'admin')
                        ->unique()
                        ->maxLength(255),
                    Forms\Components\Select::make('permissions')
                        ->disabled(fn (?Model $record) => $record && $record->name === 'admin')
                        ->relationship('permissions', 'name')
                        ->rules([
                            fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $roleName = $get('name');
                                if (str_starts_with($roleName, TenancyPermissionConstants::TENANCY_ROLE_PREFIX)) {
                                    $failedPermissions = [];
                                    Permission::whereIn('id', $value)->get()->each(function ($permission) use (&$failedPermissions) {
                                        if (! str_starts_with($permission->name, TenancyPermissionConstants::TENANCY_PERMISSION_PREFIX)) {
                                            $failedPermissions[] = $permission->name;
                                        }
                                    });

                                    if (count($failedPermissions) > 0) {
                                        $fail(__('The following permissions are not allowed for tenancy roles -> :permissions', [
                                            'prefix' => TenancyPermissionConstants::TENANCY_ROLE_PREFIX,
                                            'permissions' => implode(', ', $failedPermissions),
                                        ]));
                                    }
                                } else {
                                    $failedPermissions = [];
                                    Permission::whereIn('id', $value)->get()->each(function ($permission) use (&$failedPermissions) {
                                        if (str_starts_with($permission->name, TenancyPermissionConstants::TENANCY_PERMISSION_PREFIX)) {
                                            $failedPermissions[] = $permission->name;
                                        }
                                    });

                                    if (count($failedPermissions) > 0) {
                                        $fail(__('The following permissions are not allowed for admin roles -> :permissions', [
                                            'prefix' => TenancyPermissionConstants::TENANCY_ROLE_PREFIX,
                                            'permissions' => implode(', ', $failedPermissions),
                                        ]));
                                    }
                                }
                            },
                        ])
                        ->multiple()
                        ->preload()
                        ->helperText(__('Choose the permissions for this role. Tenancy permissions can only be assigned to tenancy roles.'))
                        ->placeholder(__('Select permissions...')),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(__('Manage the roles in your application. Roles that start with "tenancy:" are supposed to be used for multi-tenancy users to control user dashboard capabilities.'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(config('app.datetime_format'))->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(config('app.datetime_format'))->sortable(),
            ])
            ->filters([

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
            'index' => \App\Filament\Admin\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \App\Filament\Admin\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'edit' => \App\Filament\Admin\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
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
        return __('Roles');
    }
}
