<?php

namespace App\Filament\Admin\Resources\OneTimeProductResource\Pages;

use App\Filament\Admin\Resources\OneTimeProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOneTimeProducts extends ListRecords
{
    protected static string $resource = OneTimeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
