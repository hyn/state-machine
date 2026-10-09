<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Stubs\States\Cat;
use Hyn\Statemachine\Transition;
use RuntimeException;

/**
 * Fails unexpectedly and does not handle it.
 */
class KnocksOverVase extends Transition
{
    public function requirementsMet() : bool
    {
        return true;
    }

    public function suggests() : array
    {
        return [
            Cat\Awake::class,
        ];
    }

    public function fire()
    {
        throw new RuntimeException('Vase broke.');
    }

    public function reset()
    {
        return new Cat\Idle($this->model);
    }
}
