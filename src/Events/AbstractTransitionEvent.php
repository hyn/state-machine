<?php

namespace Hyn\Statemachine\Events;

use Hyn\Statemachine\Processing;

abstract class AbstractTransitionEvent
{
    /**
     * @var Processing
     */
    public $processing;

    public function __construct(Processing $processing)
    {
        $this->processing = $processing;
    }
}
