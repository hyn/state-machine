<?php

namespace Hyn\Statemachine\Contracts;

use Hyn\Statemachine\Processing;
use Throwable;

/**
 * Implemented by transitions that want to recover from an unhandled exception
 * in fire(). Without it the model stays in the transition until retried.
 */
interface HandlesFailure
{
    /**
     * Called when fire() throws anything other than a TransitioningException.
     * The exception is rethrown afterwards.
     *
     * @param Throwable $exception
     * @param Processing $processing
     * @return StateContract|null the state to move into, or null to stay in the transition
     */
    public function failed(Throwable $exception, Processing $processing): ?StateContract;
}
