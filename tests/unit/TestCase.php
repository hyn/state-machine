<?php

namespace Hyn\Statemachine\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var bool
     */
    protected $booted = false;

    public function setUp()
    {
        $this->setUpEloquent();

        $this->boot();
    }

    protected function setUpEloquent()
    {
        if (!$this->booted) {
            touch(__DIR__ . '/../database/testing.sqlite');

            $container = new Container;
            Container::setInstance($container);
            $container->bind('events', new Dispatcher($container));

            $capsule = new Manager($container);
            $capsule->addConnection([
                'driver' => 'sqlite',
                'database' => __DIR__ . '/../database/testing.sqlite'
            ]);

            $capsule->setEventDispatcher(new Dispatcher($container));

            $capsule->setAsGlobal();
            Model::setConnectionResolver($capsule->getDatabaseManager());

            $this->booted = true;

            Manager::schema()->create('cats', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('state')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function boot()
    {
        // ..
    }

    public function tearDown()
    {
        $this->down();

        if ($this->booted) {
            unlink(__DIR__ . '/../database/testing.sqlite');
        }
    }

    protected function down()
    {
        // ..
    }
}
