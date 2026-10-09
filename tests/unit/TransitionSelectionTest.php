<?php

namespace Hyn\Statemachine\Tests;

use Hyn\Statemachine\Exceptions\TransitioningException;
use Hyn\Statemachine\Statemachine;
use Hyn\Statemachine\Stubs\Definitions\HouseCatDefinition;
use Hyn\Statemachine\Stubs\Models\Cat;
use Hyn\Statemachine\Stubs\States\Cat\Asleep;
use Hyn\Statemachine\Stubs\States\Cat\Awake;
use Hyn\Statemachine\Stubs\States\Cat\Fed;
use Hyn\Statemachine\Stubs\States\Cat\Idle;
use Hyn\Statemachine\Stubs\Transitions\Cat\Eating;
use Hyn\Statemachine\Stubs\Transitions\Cat\Grooming;
use Hyn\Statemachine\Stubs\Transitions\Cat\Napping;

class TransitionSelectionTest extends TestCase
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
        // The Asleep stub is marked initial too, so start explicitly.
        $this->cat->state = Idle::resolveName();

        $this->machine = new Statemachine(
            $this->cat,
            new HouseCatDefinition
        );
    }

    /**
     * @test
     */
    public function highest_priority_transition_is_chosen()
    {
        $this->assertInstanceOf(Idle::class, $this->machine->current());

        // Idle lists Grooming (-5), Eating (10), Napping (0) in that order.
        $this->assertInstanceOf(Eating::class, $this->machine->identifyNextTransition());
    }

    /**
     * @test
     */
    public function forward_moves_through_highest_priority_transition()
    {
        $this->assertInstanceOf(Fed::class, $this->machine->forward());
        $this->assertEquals(Fed::resolveName(), $this->cat->state);
    }

    /**
     * @test
     */
    public function equal_priorities_keep_declaration_order()
    {
        $this->cat->state = Fed::resolveName();

        // Fed lists Napping (0) before Purring (0).
        $this->assertInstanceOf(Napping::class, $this->machine->identifyNextTransition());
    }

    /**
     * @test
     */
    public function identifies_transition_suggesting_requested_state()
    {
        $this->assertInstanceOf(Grooming::class, $this->machine->identifyNextTransition(new Awake($this->cat)));
        $this->assertInstanceOf(Napping::class, $this->machine->identifyNextTransition(new Asleep($this->cat)));
        $this->assertInstanceOf(Eating::class, $this->machine->identifyNextTransition(new Fed($this->cat)));
    }

    /**
     * @test
     */
    public function move_to_reaches_requested_state_regardless_of_priority()
    {
        $state = $this->machine->moveTo(new Asleep($this->cat));

        $this->assertInstanceOf(Asleep::class, $state);
        $this->assertEquals(Asleep::resolveName(), $this->cat->state);
    }

    /**
     * @test
     */
    public function move_to_unreachable_state_throws()
    {
        $this->expectException(TransitioningException::class);

        $this->machine->moveTo(new Idle($this->cat));
    }
}
