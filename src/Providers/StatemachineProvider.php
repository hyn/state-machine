<?php

namespace Hyn\Statemachine\Providers;

use Hyn\Statemachine\Commands\Processor;
use Illuminate\Support\ServiceProvider;

class StatemachineProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('command.state-machine:processor', Processor::class);

        $this->commands([
            'command.state-machine:processor'
        ]);
    }
}