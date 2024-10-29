<?php

namespace App\Console\Commands;

use App\Services\ConfigManager;
use Illuminate\Console\Command;

class ExportConfigs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-configs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the app configs from database to cache.';

    /**
     * Execute the console command.
     */
    public function handle(ConfigManager $configManager)
    {
        $configManager->exportAllConfigs();

        $this->info('Configs exported to cache successfully.');
    }
}
