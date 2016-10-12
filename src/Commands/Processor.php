<?php

namespace Hyn\Statemachine\Commands;

use Illuminate\Console\Command;

class Processor extends Command
{
    protected $signature = 'state-machine:processor';
    protected $description = 'Moves objects through their state machines.';


    public function handle()
    {

    }
}