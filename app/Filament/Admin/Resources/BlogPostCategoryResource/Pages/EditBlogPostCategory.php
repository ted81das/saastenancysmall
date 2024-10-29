<?php

namespace App\Filament\Admin\Resources\BlogPostCategoryResource\Pages;

use App\Filament\Admin\Resources\BlogPostCategoryResource;
use App\Filament\CrudDefaults;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPostCategory extends EditRecord
{
    use CrudDefaults;
    protected static string $resource = BlogPostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
