<?php

namespace App\Filament\Admin\Resources\BlogPostCategoryResource\Pages;

use App\Filament\Admin\Resources\BlogPostCategoryResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPostCategory extends CreateRecord
{
    use CrudDefaults;

    protected static string $resource = BlogPostCategoryResource::class;
}
