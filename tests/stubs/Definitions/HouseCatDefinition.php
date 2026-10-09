<?php

namespace Hyn\Statemachine\Stubs\Definitions;

use Hyn\Statemachine\Contracts\MachineDefinitionContract;
use Hyn\Statemachine\Stubs\Models\Cat;
use Hyn\Statemachine\Stubs\States;
use Hyn\Statemachine\Stubs\Transitions;

class HouseCatDefinition implements MachineDefinitionContract
{
    public function models() : array
    {
        return [
            Cat::class
        ];
    }

    public function mapping() : array
    {
        return [
            'states' => [
                States\Cat\Asleep::class,
                States\Cat\Awake::class,
                States\Cat\Fed::class,
                States\Cat\Idle::class,
            ],
            'transitions' => [
                Transitions\Cat\Eating::class,
                Transitions\Cat\Grooming::class,
                Transitions\Cat\Napping::class,
                Transitions\Cat\Purring::class,
            ]
        ];
    }
}
