<?php

namespace Hyn\Statemachine\Contracts;

interface MachineDefinitionContract
{
    /**
     * Models assigned to this statemachine definition.
     *
     * @return array
     */
    public function models() : array;

    /**
     * The flow from states to transitions the state machine has to respect.
     *
     * @see https://state-machine.readme.io/v1.0/docs/the-definition
     *
     * @return array
     */
    public function mapping() : array;
}