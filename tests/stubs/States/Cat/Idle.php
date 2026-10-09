<?php

namespace Hyn\Statemachine\Stubs\States\Cat;

use Hyn\Statemachine\State;
use Hyn\Statemachine\Stubs\Transitions\Cat;

class Idle extends State
{
    public function isInitial() : bool
    {
        return true;
    }

    public function transitions() : array
    {
        return [
            Cat\Grooming::class,
            Cat\Eating::class,
            Cat\Napping::class,
        ];
    }
}
