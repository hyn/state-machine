<?php

namespace Hyn\Statemachine;


use Hyn\Statemachine\Contracts\StateContract;

abstract class State extends Named implements StateContract
{

    /**
     * {@inheritdoc}
     */
    public function isInitial() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() : bool
    {
        return false;
    }
}
