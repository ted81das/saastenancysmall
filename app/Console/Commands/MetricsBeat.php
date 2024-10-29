<?php

namespace App\Console\Commands;

use App\Services\MetricsManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class MetricsBeat extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:metrics-beat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store metrics for the app';

    /**
     * Execute the console command.
     */
    public function handle(MetricsManager $metricsManager)
    {
        $metricsManager->beat();
    }

}
