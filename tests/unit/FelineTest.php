<?php

namespace Hyn\Statemachine\Tests;

use Hyn\Statemachine\Statemachine;
use Hyn\Statemachine\Stubs\Definitions\CatDefinition;
use Hyn\Statemachine\Stubs\Models\Cat;

class FelineTest extends TestCase
{
    /**
     * @var Statemachine
     */
    protected $machine;

    public function boot()
    {
        $this->machine = new Statemachine(
            new Cat,
            new CatDefinition
        );
    }

    /**
     * @test
     */
    public function machine_creation_works()
    {
        $this->assertEquals(Statemachine::class, get_class($this->machine));
    }

    /**
     * @test
     */
    public function definition_is_valid()
    {
        $definition = $this->machine->getDefinition();

        $this->assertEquals(CatDefinition::class, get_class($definition));

        $this->assertArrayHasKey('states', $definition->mapping());
        $this->assertArrayHasKey('transitions', $definition->mapping());
    }

    /**
     * @test
     */
    public function starts_in_initial_state()
    {
        $current = $this->machine->current();

        $this->assertTrue($current->isInitial());
    }

    /**
     * @test
     */
    public function moves_through_a_transition()
    {
        $this->machine->forward();
    }
}