<?php

namespace Hyn\Statemachine\Stubs\Transitions\Cat;

use Hyn\Statemachine\Contracts\HandlesFailure;
use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Processing;
use Hyn\Statemachine\Stubs\States\Cat;
use Throwable;

/**
 * Fails unexpectedly and lands in a state of its choosing.
 */
class FallsOffTable extends KnocksOverVase implements HandlesFailure
{
    public ?Throwable $failure = null;
    public ?Processing $processing = null;

    public function fire()
    {
        throw new \RuntimeException('Missed the jump.');
    }

    public function failed(Throwable $exception, Processing $processing): ?StateContract
    {
        $this->failure = $exception;
        $this->processing = $processing;

        return new Cat\Awake($this->model);
    }
}
