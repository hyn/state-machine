<?php

namespace Hyn\Statemachine;

use Hyn\Statemachine\Contracts\MachineDefinitionContract;
use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Contracts\StatemachineContract;
use Hyn\Statemachine\Contracts\TransitionContract;
use Hyn\Statemachine\Events\Transitioned;
use Hyn\Statemachine\Events\Transitioning;
use Hyn\Statemachine\Exceptions\InvalidStateException;
use Hyn\Statemachine\Exceptions\TransitioningException;
use Illuminate\Contracts\Logging\Log as LoggerContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Statemachine implements StatemachineContract
{
    /**
     * @var ProcessedByStatemachine|Model
     */
    protected $model;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * @var MachineDefinitionContract
     */
    protected $definition;

    /**
     * Statemachine constructor.
     *
     * @param ProcessedByStatemachine        $model
     * @param MachineDefinitionContract|null $definition
     */
    public function __construct(
        ProcessedByStatemachine $model,
        MachineDefinitionContract $definition = null
    ) {
        $this->setModel($model);
        $this->retrieveDefinition($definition);
    }

    /**
     * @param MachineDefinitionContract|null $definition
     */
    protected function retrieveDefinition(MachineDefinitionContract $definition = null)
    {
        if (!$definition) {
            // .. todo, should try to retrieve based on configuration file
        }

        $this->definition = $definition;
    }

    /**
     * Moves forward based on the priorities and met requirements of the transitions.
     *
     * @return StateContract|bool
     */
    public function forward()
    {
        if ($this->isInTransition()) {
            return false;
        }

        $transition = $this->identifyNextTransition();

        if ($transition) {
            return $this->moveThrough($transition);
        }

        return false;
    }

    /**
     * Whether the model is currently in a transition.
     *
     * @return bool
     */
    public function isInTransition() : bool
    {
        return is_subclass_of($this->current(), TransitionContract::class);
    }

    /**
     * Loads the current state.
     *
     * @return StateContract|TransitionContract
     */
    public function current()
    {
        return $this->model->state
            ? $this->resolveByName($this->model->state)
            : $this->resolveStateByPosition('initial');
    }

    /**
     * Loads a state or transition based on a unique identifier.
     *
     * @eg: state.paid
     *
     * @param string $state
     * @return StateContract|TransitionContract
     * @throws InvalidStateException
     */
    public function resolveByName($state = '')
    {
        if (empty($state) || !strstr($state, '.')) {
            throw new InvalidStateException($state);
        }

        list($type, $identifier) = explode('.', $state);

        $type = Str::plural($type);

        foreach (Arr::get($this->definition->mapping(), $type, []) as $state) {
            /** @var State $instance */
            $instance = new $state($this->model);

            if ($instance->name() == $identifier) {
                return $instance;
            }
        }
    }

    /**
     * Loads the first state based on a position.
     *
     * @eg initial, final
     *
     * @param string $position
     * @return Collection|StateContract
     */
    public function resolveStateByPosition($position = 'initial')
    {
        $method = Str::camel("is_{$position}");

        $found = new Collection();

        foreach (Arr::get($this->definition->mapping(), 'states', []) as $state) {
            $instance = new $state($this->model);

            if (method_exists($instance, $method) && $instance->{$method}() === true) {
                $found->put($instance->name(), $instance);
            }
        }

        return $found->count() > 1 ? $found : $found->first();
    }

    /**
     * Loads the transition with the highest priority that have their requirements met.
     *
     * @param StateContract $nextState
     * @return TransitionContract
     */
    public function identifyNextTransition(StateContract $nextState = null)
    {
        $currentState = $this->current();

        $requirementsMet = new Collection();

        /** @var TransitionContract $transition */
        foreach ($this->transitionInstances($currentState) as $transition) {
            if (!$nextState || ($nextState && !in_array($nextState, $transition->suggests()))) {
                if ($transition->requirementsMet()) {
                    $requirementsMet->put($transition->name(), $transition);
                }
            }
        }

        // Sort the transitions based on priority.
        $requirementsMet->sort(function ($a, $b) {
            if ($a->priority() == $b->priority()) {
                return 0;
            }

            return ($a->priority() < $b->priority() ? -1 : 1);
        });

        return $requirementsMet->first();
    }

    /**
     * @param StateContract $state
     * @return Collection
     * @throws TransitioningException
     */
    public function transitionInstances(StateContract $state = null)
    {
        $collection = new Collection();

        if ($state) {
            foreach ($state->transitions() as $transitionClass) {
                if (!class_exists($transitionClass)) {
                    throw new TransitioningException("$transitionClass not found" .
                        ($state ? " while in state " . get_class($state) : ''));
                }

                /** @var TransitionContract $instance */
                $instance = new $transitionClass($this->model);
                $collection->put($instance->name(), $instance);
            }
        }

        return $collection;
    }

    /**
     * Tries to move through a specific transition.
     *
     * @param TransitionContract $transition
     * @param bool $force
     * @return Response|StateContract
     * @throws TransitioningException
     */
    public function moveThrough(TransitionContract $transition, $force = false)
    {
        // Prevent transitions when Object already in transition.
        if (!$force && $this->isInTransition()) {
            throw new TransitioningException('Already in transition');
        }

        // Prevent non automated transitions from running automated.
        if (!$force && !$transition->automated() && app()->runningInConsole()) {
            $this->log($transition, 'cannot run automated');
            throw new TransitioningException("Transition cannot be automated.");
        }

        $current = $this->current();

        $this->setModelState($transition);

        event(new Transitioning($transition));

        try {
            $result = $transition->fire();
        } catch (TransitioningException $e) {
            $this->log($transition, 'transition failed, reset attempt');
            $reset = $transition->reset();

            if ($reset instanceof StateContract) {
                $this->setModelState($reset);

                return $reset;
            }

            return $current;
        }

        $state = Arr::get($result, 'state');

        if ($result instanceof StateContract) {
            $state = $result;
        }

        if ($state) {
            $this->log($state, "transition returned state, setting as current");
            $this->setModelState($state);
        }

        event(new Transitioned($transition));

        if (($response = Arr::get($result, 'response'))) {
            return $response;
        } elseif ($state) {
            return $state;
        }
    }

    /**
     * @param      $stateOrTransition
     * @param null $message
     */
    protected function log($stateOrTransition, $message = null)
    {
        if ($this->logger) {
            $from = $this->current() ? $this->current()->name() : 'x';
            $to   = $stateOrTransition->name();

            $this->logger->info("State-machine: $from >> $to : $message");
        }
    }

    /**
     * Updates the state of the model.
     *
     * @param $stateOrTransition
     */
    public function setModelState($stateOrTransition)
    {
        if (is_subclass_of($stateOrTransition, TransitionContract::class)) {
            $type = 'transition';
        } elseif (is_subclass_of($stateOrTransition, StateContract::class)) {
            $type = 'state';
        }

        $this->model->state = "{$type}.{$stateOrTransition->name()}";

        if ($this->model->isDirty('state') && $this->model->exists) {
            $this->model->save();
        }
    }

    /**
     * @param LoggerContract $logger
     * @return $this
     */
    public function setLogger(LoggerContract $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Retries the last transition.
     *
     * @param TransitionContract|null $transition
     *
     * @return StateContract|bool
     * @throws TransitioningException
     */
    public function retry(TransitionContract $transition = null)
    {
        if ($this->current() instanceof TransitionContract) {
            return $this->moveThrough($transition ?: $this->current(), true);
        }

        return false;
    }

    /**
     * Sets the current state as the initial one.
     *
     * @return StateContract
     */
    public function restart() : StateContract
    {
        return $this->moveTo($this->resolveStateByPosition('initial'));
    }

    /**
     * Tries to move to a specific state.
     *
     * @param StateContract $state
     * @return StateContract
     * @throws TransitioningException
     */
    public function moveTo(StateContract $state) : StateContract
    {

        $transition = $this->identifyNextTransition($state);

        if ($transition) {
            return $this->moveThrough($transition);
        }

        throw new TransitioningException("No transition found for moving to {$state->name}.");
    }

    /**
     * @return MachineDefinitionContract
     */
    public function getDefinition(): MachineDefinitionContract
    {
        return $this->definition;
    }

    /**
     * @return ProcessedByStatemachine|Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param ProcessedByStatemachine $model
     * @return $this
     */
    public function setModel(ProcessedByStatemachine $model)
    {
        $this->model = $model;

        return $this;
    }
}
