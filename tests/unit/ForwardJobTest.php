<?php

namespace Hyn\Statemachine\Tests;

use Hyn\Statemachine\Jobs\Forward;
use Hyn\Statemachine\Stubs\Definitions\HouseCatDefinition;
use Hyn\Statemachine\Stubs\Models\Cat;
use Hyn\Statemachine\Stubs\Models\HouseCat;
use Hyn\Statemachine\Stubs\States\Cat\Fed;
use Hyn\Statemachine\Stubs\States\Cat\Idle;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ForwardJobTest extends TestCase
{
    /**
     * @test
     */
    public function is_unique_per_model()
    {
        $cat = new Cat;
        $cat->id = 7;

        $other = new Cat;
        $other->id = 8;

        $job = new Forward($cat, HouseCatDefinition::class);

        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertEquals(HouseCatDefinition::class . ':cats:7', $job->uniqueId());
        $this->assertNotEquals($job->uniqueId(), (new Forward($other, HouseCatDefinition::class))->uniqueId());
    }

    /**
     * @test
     */
    public function subclass_of_the_model_shares_the_lock()
    {
        $cat = new Cat;
        $cat->id = 7;

        $houseCat = new HouseCat;
        $houseCat->id = 7;

        $this->assertEquals(
            (new Forward($cat, HouseCatDefinition::class))->uniqueId(),
            (new Forward($houseCat, HouseCatDefinition::class))->uniqueId()
        );
    }

    /**
     * @test
     */
    public function moves_the_model_forward()
    {
        $cat = new Cat;
        $cat->state = Idle::resolveName();

        (new Forward($cat, HouseCatDefinition::class))->handle();

        $this->assertEquals(Fed::resolveName(), $cat->state);
    }
}
