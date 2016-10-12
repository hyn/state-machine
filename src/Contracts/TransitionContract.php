<?php

namespace Hyn\Statemachine\Contracts;

interface TransitionContract extends MustBeNamed
{
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
     * @return StateContract
     */
    public function fire() : StateContract;

    /**
     * In case something went wrong, reset.
     *
     * @return StateContract|boolean
     */
    public function reset();
}
