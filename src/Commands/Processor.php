<?php

namespace Hyn\Statemachine\Commands;

use Hyn\Statemachine\Contracts\MachineDefinitionContract;
use Hyn\Statemachine\Statemachine;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class Processor extends Command
{
    protected $signature = 'state-machine:processor';
    protected $description = 'Moves objects through their state machines.';


    public function handle()
    {
        /** @var Collection $definitions */
        $definitions = collect(config('state-machine.definitions', []));

        $definitions->each(function ($definition) {
            if (!class_exists($definition)) {
                return;
            }

            /** @var MachineDefinitionContract $definition */
            $definition = new $definition;

            collect($definition->models())
                ->each(function ($class) use ($definition) {
                    forward_static_call([$class, 'chunk'], 50, function ($objects) use ($definition) {
                        foreach ($objects as $object) {
                            $this->attemptTransitioning($object, $definition);
                        }
                    });
                });
        });
    }

    /**
     * @param $model
     * @param $definition
     * @return bool|\Hyn\Statemachine\Contracts\StateContract
     */
    protected function attemptTransitioning($model, $definition)
    {
        return (new Statemachine($model, $definition))->forward();
    }
}
