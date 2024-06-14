<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Contracts\TransitionContract;
use Hyn\Statemachine\Stubs\States\Cat\Awake;
use Hyn\Statemachine\Transition;
use Illuminate\Http\Response;

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
            Awake::class,
        ];
    }

    public function fire(): Response|StateContract|array|TransitionContract
    {
        return new Awake($this->model);
    }

    public function reset(): Response|StateContract|array|TransitionContract
    {
        // TODO: Implement reset() method.
    }
}
