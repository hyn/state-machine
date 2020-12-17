<?php

namespace Hyn\Statemachine;

use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Hyn\Statemachine\Contracts\TransitionContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Transition extends Named implements TransitionContract
{
    use DispatchesJobs;

    /**
     * @var ProcessedByStatemachine|Model
     */
    protected $model;

    public function __construct(ProcessedByStatemachine $model)
    {
        $this->model = $model;
    }

    /**
     * Whether the transition can be retried.
     *
     * @return boolean
     */
    public function canRetry() : bool
    {
        return false;
    }

    /**
     * Priority of this transition. Higher indicates higher priority; 0 and up.
     *
     * @info 0 indicates no obvious priority above others.
     * @info a negative value indicates please prioritize others above this one.
     *
     * @return integer
     */
    public function priority() : int
    {
        return 0;
    }

    /**
     * Whether this transition is automated.
     *
     * @info if false, human action is required.
     *
     * @return bool
     */
    public function automated() : bool
    {
        return true;
    }
}
