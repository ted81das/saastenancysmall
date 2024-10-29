<?php

namespace App\Filament\Admin\Resources\DiscountResource\Pages;

use App\Filament\Admin\Resources\DiscountResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),

        ];
    }
}
