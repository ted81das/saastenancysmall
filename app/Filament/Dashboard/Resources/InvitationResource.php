<?php

namespace App\Filament\Dashboard\Resources;

use App\Constants\InvitationStatus;
use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Resources\InvitationResource\Pages;
use App\Mapper\InvitationStatusMapper;
use App\Models\Invitation;
use App\Services\TenantManager;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static ?string $navigationGroup = 'Team';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->helperText(__('Enter the email address of the person you want to invite.'))
                    ->rules([
                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                            // if there is a user with this email address in the tenant and status is pending and expires_at is greater than now, fail
                            if (Filament::getTenant()->invitations()
                                ->where('email', $value)
                                ->where('status', InvitationStatus::PENDING->value)
                                ->where('expires_at', '>', now())
                                ->exists()
                            ) {
                                $fail(__('This email address has already been invited.'));
                            }

                            if (Filament::getTenant()->users()->where('email', $value)->exists()) {
                                $fail(__('This user is already in the team.'));
                            }

                            /** @var TenantManager $tenantManager */
                            $tenantManager = app(TenantManager::class);

                            if (! $tenantManager->canInviteUser(Filament::getTenant(), auth()->user())) {
                                $fail(__('You have reached the maximum number of users allowed for your subscription.'));
                            }
                        },
                    ])
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options(function (TenantPermissionManager $tenantPermissionManager) {
                        return $tenantPermissionManager->getAllAvailableTenantRolesForDisplay();
                    })
                    ->default(TenancyPermissionConstants::ROLE_USER)
                    ->required()
                    ->helperText(__('Choose the role for this user.')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(__('Send invitations to your team members.'))
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('Inviter'))
                    ->formatStateUsing(function ($state, $record) {
                        return $record->user->name;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function ($state, InvitationStatusMapper $invitationStatusMapper) {
                        return $invitationStatusMapper->mapForDisplay($state);
                    }),
                Tables\Columns\TextColumn::make('role')
                    ->formatStateUsing(function ($state, $record) {
                        return Str::of($state)->replace(TenancyPermissionConstants::TENANCY_ROLE_PREFIX, '')->replace('-', ' ')->title();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        /** @var TenantPermissionManager $tenantPermissionManager */
        $tenantPermissionManager = app(TenantPermissionManager::class); // a bit ugly, but this is the Filament way :/

        return config('app.allow_tenant_invitations', false) && $tenantPermissionManager->tenantUserHasPermissionTo(
            Filament::getTenant(),
            auth()->user(),
            TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS,
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
            'edit' => Pages\EditInvitation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Invite People');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Filament::getTenant()->id)->where('expires_at', '>', now())->where('status', InvitationStatus::PENDING->value);
    }
}
