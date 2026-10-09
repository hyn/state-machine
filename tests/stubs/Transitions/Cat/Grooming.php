<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Stubs\States\Cat;
use Hyn\Statemachine\Transition;

class Grooming extends Transition
{
    public function requirementsMet() : bool
    {
        return true;
    }

    public function priority() : int
    {
        return -5;
    }

    public function suggests() : array
    {
        return [
            Cat\Awake::class,
        ];
    }

    public function fire()
    {
        return new Cat\Awake($this->model);
    }

    public function reset()
    {
        return $this->fire();
    }
}
