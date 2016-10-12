<?php

namespace Hyn\Statemachine\Events;

use Hyn\Statemachine\Contracts\TransitionContract;

abstract class AbstractTransitionEvent
{
    /**
     * @var TransitionContract
     */
    public $transition;

    public function __construct(TransitionContract $transition)
    {
        $this->transition = $transition;
    }
}
