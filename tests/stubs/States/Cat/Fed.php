<?php

namespace Hyn\Statemachine\Stubs\States\Cat;

use Hyn\Statemachine\State;
use Hyn\Statemachine\Stubs\Transitions\Cat;

class Fed extends State
{
    public function transitions() : array
    {
        return [
            Cat\Napping::class,
            Cat\Purring::class,
        ];
    }
}
