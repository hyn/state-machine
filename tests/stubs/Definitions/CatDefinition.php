<?php

namespace Hyn\Statemachine\Stubs\Definitions;

use Hyn\Statemachine\Contracts\MachineDefinitionContract;
use Hyn\Statemachine\Stubs\Models\Cat;
use Hyn\Statemachine\Stubs\States\Cat\Asleep;
use Hyn\Statemachine\Stubs\States\Cat\Awake;
use Hyn\Statemachine\Stubs\Transitions\Cat\WakesUp;

class CatDefinition implements MachineDefinitionContract
{

    /**
     * Models assigned to this statemachine definition.
     *
     * @return array
     */
    public function models() : array
    {
        return [
            Cat::class
        ];
    }

    /**
     * The flow from states to transitions the state machine has to respect.
     *
     * @see https://state-machine.readme.io/v1.0/docs/the-definition
     *
     * @return array
     */
    public function mapping() : array
    {
        return [
            'states' => [
                Asleep::class,
                Awake::class
            ],
            'transitions' => [
                WakesUp::class
            ]
        ];
    }
}
