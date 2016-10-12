<?php

namespace Hyn\Statemachine\Contracts;

interface StateContract extends MustBeNamed
{
    /**
     * Returns all possible transitions from this state.
     *
     * @return array
     */
    public function transitions() : array;

    /**
     * Whether this state is the first.
     *
     * @return bool
     */
    public function isInitial() : bool;

    /**
     * Whether this state is the last.
     *
     * @return bool
     */
    public function isFinal() : bool;
}
