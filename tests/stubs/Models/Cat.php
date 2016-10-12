<?php

namespace Hyn\Statemachine\Stubs\Models;

use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $born
 */
class Cat extends Model implements ProcessedByStatemachine
{
}
