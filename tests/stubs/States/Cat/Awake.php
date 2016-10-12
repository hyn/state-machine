<?php

namespace Hyn\Statemachine\Stubs\States\Cat;

use Hyn\Statemachine\State;

class Awake extends State
{
    /**
     * Returns all possible transitions from this state.
     *
     * @return array
     */
    public function transitions() : array
    {
        return [
        ];
    }
}
