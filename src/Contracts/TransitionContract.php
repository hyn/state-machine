<?php

namespace Hyn\Statemachine\Contracts;

use Illuminate\Http\Response;

interface TransitionContract extends MustBeNamed
{
    public function __construct(ProcessedByStatemachine $model);

    /**
     * Whether the transition can be retried.
     *
     * @return boolean
     */
    public function canRetry() : bool;

    /**
     * Priority of this transition. Higher indicates higher priority; 0 and up.
     *
     * @info 0 indicates no obvious priority above others.
     * @info a negative value indicates please prioritize others above this one.
     *
     * @return integer
     */
    public function priority() : int;

    /**
     * Whether the requirements are met to process through this transition.
     *
     * @return bool
     */
    public function requirementsMet() : bool;

    /**
     * Whether this transition is automated.
     *
     * @info if false, human action is required.
     *
     * @return bool
     */
    public function automated() : bool;

    /**
     * Returns the list of suggested states to move into.
     *
     * @return array
     */
    public function suggests() : array;

    /**
     * Processing the transition.
     *
     * @return StateContract|Response|array ; ['response' => .., 'state' => ...] or State instance or Response instance
     */
    public function fire();

    /**
     * In case something went wrong, reset.
     *
     * @param ProcessedByStatemachine $model
     * @return StateContract|boolean
     */
    public function reset();
}
