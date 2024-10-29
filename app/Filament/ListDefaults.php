<?php

namespace App\Filament;

trait ListDefaults
{
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
