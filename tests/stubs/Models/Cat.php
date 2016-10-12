<?php

namespace Hyn\Statemachine\Stubs\Models;

use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 */
class Cat extends Model implements ProcessedByStatemachine
{
}
