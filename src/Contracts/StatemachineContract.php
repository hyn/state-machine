<?php

namespace Hyn\Statemachine\Contracts;

use Hyn\Statemachine\Exceptions\TransitioningException;
use Illuminate\Http\Response;

interface StatemachineContract
{
    /**
     * Moves forward based on the priorities and met requirements of the transitions.
     *
     * @return StateContract|bool
     */
    public function forward();

    /**
     * Tries to move through a specific transition.
     *
     * @param TransitionContract $transition
     * @param bool $force
     * @return Response|StateContract
     * @throws TransitioningException
     */
    public function moveThrough(TransitionContract $transition, $force = false);

    /**
     * Tries to move to a specific state.
     *
     * @param StateContract $state
     * @return StateContract
     * @throws TransitioningException
     */
    public function moveTo(StateContract $state) : StateContract;

    /**
     * Retries the last transition.
     *
     * @param TransitionContract|null $transition
     *
     * @return StateContract|bool
     * @throws TransitioningException
     */
    public function retry(TransitionContract $transition = null);

    /**
     * Sets the current state as the initial one.
     *
     * @return StateContract
     */
    public function restart() : StateContract;

    /**
     * Loads the current state.
     *
     * @return StateContract|TransitionContract
     */
    public function current();

    /**
     * Whether the model is currently in a transition.
     *
     * @return bool
     */
    public function isInTransition() : bool;
}
