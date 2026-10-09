<?php

namespace Hyn\Statemachine\Tests;

use Hyn\Statemachine\Statemachine;
use Hyn\Statemachine\Stubs\Definitions\HouseCatDefinition;
use Hyn\Statemachine\Stubs\Models\Cat;
use Hyn\Statemachine\Stubs\States\Cat\Awake;
use Hyn\Statemachine\Stubs\States\Cat\Idle;
use Hyn\Statemachine\Stubs\Transitions\Cat\FallsOffTable;
use Hyn\Statemachine\Stubs\Transitions\Cat\KnocksOverVase;
use RuntimeException;

class FailureTest extends TestCase
{
    /**
     * @var Cat
     */
    protected $cat;

    /**
     * @var Statemachine
     */
    protected $machine;

    public function boot()
    {
        $this->cat = new Cat;
        $this->cat->state = Idle::resolveName();

        $this->machine = new Statemachine(
            $this->cat,
            new HouseCatDefinition
        );
    }

    /**
     * @test
     */
    public function unhandled_failure_stays_in_transition_and_rethrows()
    {
        try {
            $this->machine->moveThrough(new KnocksOverVase($this->cat));
            $this->fail('Exception was swallowed.');
        } catch (RuntimeException $e) {
            $this->assertEquals('Vase broke.', $e->getMessage());
        }

        $this->assertEquals(KnocksOverVase::resolveName(), $this->cat->state);
        $this->assertTrue($this->machine->isInTransition());
    }

    /**
     * @test
     */
    public function handled_failure_moves_into_returned_state_and_rethrows()
    {
        $transition = new FallsOffTable($this->cat);

        try {
            $this->machine->moveThrough($transition);
            $this->fail('Exception was swallowed.');
        } catch (RuntimeException $e) {
            $this->assertEquals('Missed the jump.', $e->getMessage());
        }

        $this->assertEquals(Awake::resolveName(), $this->cat->state);
        $this->assertFalse($this->machine->isInTransition());

        $this->assertSame($e, $transition->failure);
        $this->assertInstanceOf(Idle::class, $transition->processing->from);
    }
}
