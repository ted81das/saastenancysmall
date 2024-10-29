<?php

namespace App\Filament\Admin\Resources\BlogPostResource\Pages;

use App\Filament\Admin\Resources\BlogPostResource;
use App\Filament\CrudDefaults;
use App\Models\BlogPost;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    //    use CrudDefaults;
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // view the post
            Actions\Action::make('view')
                ->label(__('View Post'))
                ->color('success')
                ->url(
                    fn (BlogPost $resource) => route('blog.view', $resource->slug),
                    true
                )
                ->icon('heroicon-o-eye'),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]),
        ];
    }
}
