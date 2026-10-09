<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Stubs\States\Cat;
use Hyn\Statemachine\Transition;

class Eating extends Transition
{
    public function requirementsMet() : bool
    {
        return true;
    }

    public function priority() : int
    {
        return 10;
    }

    public function suggests() : array
    {
        return [
            Cat\Fed::class,
        ];
    }

    public function fire()
    {
        return new Cat\Fed($this->model);
    }

    public function reset()
    {
        return $this->fire();
    }
}
