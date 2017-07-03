<?php

namespace Hyn\Statemachine\Providers;

use Hyn\Statemachine\Commands\Processor;
use Illuminate\Support\ServiceProvider;

class StatemachineProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerCommands();
        $this->registerConfigurations();
    }

    protected function registerCommands()
    {
        $this->app->bind('command.state-machine:processor', Processor::class);

        $this->commands([
            'command.state-machine:processor'
        ]);
    }

    protected function registerConfigurations()
    {
        $configPath = __DIR__ . '/../config/state-machine.php';
        $this->publishes([
            $configPath => config_path('state-machine.php')
        ], 'config');
        $this->mergeConfigFrom($configPath, 'state-machine');
    }
}
