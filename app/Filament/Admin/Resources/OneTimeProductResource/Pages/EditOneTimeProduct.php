<?php

namespace App\Filament\Admin\Resources\OneTimeProductResource\Pages;

use App\Filament\Admin\Resources\OneTimeProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOneTimeProduct extends EditRecord
{
    protected static string $resource = OneTimeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
