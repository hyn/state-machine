<?php

namespace Hyn\Statemachine\Stubs\States\Cat;

use Hyn\Statemachine\State;
use Hyn\Statemachine\Stubs\Transitions\Cat\WakesUp;

class Sleeping extends State
{
    public function isInitial() : bool
    {
        return true;
    }

    public function isFinal() : bool
    {
        return true;
    }


    /**
     * Returns all possible transitions from this state.
     *
     * @return array
     */
    public function transitions() : array
    {
        return [
            WakesUp::class,
        ];
    }
}