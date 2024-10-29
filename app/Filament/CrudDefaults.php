<?php

namespace App\Filament;

trait CrudDefaults
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
