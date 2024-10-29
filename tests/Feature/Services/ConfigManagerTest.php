<?php

namespace Tests\Feature\Services;

use App\Constants\ConfigConstants;
use App\Services\ConfigManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\Feature\FeatureTest;

class ConfigManagerTest extends FeatureTest
{
    public function test_load_configs()
    {
        Cache::shouldReceive('many')->once()->with(ConfigConstants::OVERRIDABLE_CONFIGS)->andReturn([
            'app.name' => 'SaaSyKit',
            'app.support_email' => 'test@test.com',
        ]);

        Config::shouldReceive('set')->once()->with([
            'app.name' => 'SaaSyKit',
            'app.support_email' => 'test@test.com',
        ]);

        $configManager = new ConfigManager();

        $configManager->loadConfigs();
    }

    public function test_set_not_allowed()
    {
        $configManager = new ConfigManager();

        $this->expectException(\Exception::class);

        $configManager->set('not_allowed_key', 'http://localhost');
    }

    public function test_set()
    {
        $configManager = new ConfigManager();

        Cache::shouldReceive('forever')->once()->with('app.name', 'SaaSyKit');

        $configManager->set('app.name', 'SaaSyKit');

        $configInDb = \App\Models\Config::where('key', 'app.name')->first();

        $this->assertEquals('SaaSyKit', $configInDb->value);
    }

    public function test_get()
    {
        $configManager = new ConfigManager();

        $configManager->set('app.default_currency', 'EUR');

        $this->assertEquals('EUR', $configManager->get('app.default_currency'));
    }
}
