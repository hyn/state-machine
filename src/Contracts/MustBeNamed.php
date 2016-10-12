<?php

namespace Hyn\Statemachine\Contracts;

interface MustBeNamed
{
    /**
     * Returns a unique string.
     *
     * @return string
     */
    public function name() : string;
}
