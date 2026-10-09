<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Stubs\States\Cat;
use Hyn\Statemachine\Transition;

class Napping extends Transition
{
    public function requirementsMet() : bool
    {
        return true;
    }

    public function priority() : int
    {
        return 0;
    }

    public function suggests() : array
    {
        return [
            Cat\Asleep::class,
        ];
    }

    public function fire()
    {
        return new Cat\Asleep($this->model);
    }

    public function reset()
    {
        return $this->fire();
    }
}
