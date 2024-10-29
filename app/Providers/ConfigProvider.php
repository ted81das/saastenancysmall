<?php

namespace App\Providers;

use App\Services\ConfigManager;
use Illuminate\Support\ServiceProvider;

class ConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(ConfigManager $configManager): void
    {
        try {
            $configManager->loadConfigs();
        } catch (\Exception $e) {
            // We don't want to throw an exception if the database is not yet migrated
        }
    }
}
