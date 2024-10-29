<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Filament\Admin\Resources\PlanResource;
use App\Filament\CrudDefaults;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()->before(function (DeleteAction $action) {
                if ($this->record->subscriptions()->count() > 0) {
                    Notification::make()
                        ->warning()
                        ->title(__('Unable to delete plan'))
                        ->body(__('This plan has subscriptions and cannot be deleted.'))
                        ->persistent()
                        ->send();

                    $action->cancel();
                }
            }),
        ];
    }
}
