<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Stubs\States\Cat\Sleeping;
use Hyn\Statemachine\Transition;

class WakesUp extends Transition
{
    /**
     * Whether the requirements are met to process through this transition.
     *
     * @return bool
     */
    public function requirementsMet() : bool
    {
        return true;
    }

    /**
     * Returns the list of suggested states to move into.
     *
     * @return array
     */
    public function suggests() : array
    {
        return [
            Sleeping::class,
        ];
    }

    /**
     * Processing the transition.
     *
     * @return StateContract
     */
    public function fire() : StateContract
    {
        return new Sleeping($this->model);
    }

    /**
     * In case something went wrong, reset.
     *
     * @return StateContract|boolean
     */
    public function reset()
    {
        // TODO: Implement reset() method.
    }
}