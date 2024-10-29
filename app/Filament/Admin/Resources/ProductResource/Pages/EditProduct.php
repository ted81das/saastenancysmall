<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\CrudDefaults;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    if ($this->record->plans()->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->title(__('Unable to delete product'))
                            ->body(__('This product has plans and cannot be deleted.'))
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
