<?php

namespace Hyn\Statemachine;

use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Contracts\TransitionContract;
use Illuminate\Support\Str;

abstract class Named
{
    /**
     * @return string
     */
    public static function resolveName() : string
    {
        $class = get_called_class();

        if (is_subclass_of($class, TransitionContract::class)) {
            $type = 'transition';
        } elseif (is_subclass_of($class, StateContract::class)) {
            $type = 'state';
        } else {
            throw new InvalidArgumentException('State or transition needed.');
        }

        $class = str_replace(
            ['App\\', '\\'],
            '',
            get_called_class()
        );

        $class = Str::snake($class, '-');

        return "$type.$class";
    }

    /**
     * Returns a unique string.
     *
     * @return string
     */
    public function name() : string
    {
        return call_user_func([get_called_class(), 'resolveName']);
    }
}
