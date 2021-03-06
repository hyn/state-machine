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
use InvalidArgumentException;

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

    public function __construct(
        ProcessedByStatemachine $model,
        MachineDefinitionContract $definition = null
    ) {
        $this->setModel($model);
        $this->retrieveDefinition($definition);
    }

    protected function retrieveDefinition(MachineDefinitionContract $definition = null)
    {
        if (! $definition) {
            foreach (config('state-machine.definitions', []) as $definition) {
                /** @var MachineDefinitionContract $definition */
                $definition = resolve($definition);

                if (in_array(get_class($this->model), $definition->models())) {
                    break;
                }
            }
        }

        $this->definition = $definition;
    }

    /**
     * Moves forward based on the priorities and met requirements of the transitions.
     *
     * @return StateContract|bool
     * @throws InvalidStateException
     * @throws TransitioningException
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
     * @throws InvalidStateException
     */
    public function isInTransition() : bool
    {
        return is_subclass_of($this->current(), TransitionContract::class);
    }

    /**
     * Loads the current state.
     *
     * @return StateContract|TransitionContract
     * @throws InvalidStateException
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
     * @param string $seek
     * @return StateContract|TransitionContract
     * @throws InvalidStateException
     */
    public function resolveByName(string $seek = '')
    {
        if (empty($seek) || !strstr($seek, '.')) {
            throw new InvalidStateException($seek);
        }

        [$type, $_] = explode('.', $seek);

        $type = Str::plural($type);

        foreach (Arr::get($this->definition->mapping(), $type, []) as $mapped) {
            /** @var State|Transition $instance */
            $instance = new $mapped($this->model);

            if ($instance->name() === $seek) {
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
    public function resolveStateByPosition(string $position)
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

    public function identifyNextTransition(StateContract $nextState = null): ?TransitionContract
    {
        $currentState = $this->current();

        $requirementsMet = new Collection();

        /** @var TransitionContract $transition */
        foreach ($this->transitionInstances($currentState) as $transition) {
            if (!$nextState || in_array($nextState, $transition->suggests())) {
                // Do not offer a transition that can't be automated.
                if (!$nextState && app()->runningInConsole() && !$transition->automated()) {
                    continue;
                }

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
     * @param StateContract|null $state
     * @return Collection
     * @throws TransitioningException
     */
    public function transitionInstances(StateContract $state = null): Collection
    {
        $collection = new Collection();

        if ($state) {
            foreach ($state->transitions() as $transitionClass) {
                if (!class_exists($transitionClass)) {
                    throw new TransitioningException(
                        "$transitionClass not found" .
                        ($state ? " while in state " . get_class($state) : '')
                    );
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
     * @throws TransitioningException|InvalidStateException
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

        $transitioning = new Processing(
            $this->getModel(),
            $this->current(),
            $transition
        );

        $this->setModelState($transition);

        event(new Transitioning($transitioning));

        try {
            $transitioning->process();
        } catch (TransitioningException $e) {
            $this->log($transition, 'transition failed, reset attempt');
            $transitioning->reset();

            if ($transitioning->to) {
                $this->setModelState($transitioning->to);

                return $transitioning->to;
            }

            return $transitioning->from;
        }

        if ($transitioning->to) {
            $this->log($transitioning->to, "transition returned state, setting as current");
            $this->setModelState($transitioning->to);
        }

        event(new Transitioned($transitioning));

        if ($transitioning->response) {
            return $transitioning->response;
        } elseif ($transitioning->to) {
            return $transitioning->to;
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
     * @param TransitionContract|StateContract $stateOrTransition
     */
    public function setModelState($stateOrTransition)
    {
        $this->model->state = $stateOrTransition->name();

        if ($this->model->isDirty('state') && $this->model->exists) {
            $this->model->save();
        }
    }

    public function setLogger(LoggerContract $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function retry(TransitionContract $transition = null)
    {
        if ($this->current() instanceof TransitionContract) {
            return $this->moveThrough($transition ?: $this->current(), true);
        }

        return false;
    }

    public function restart() : StateContract
    {
        return $this->moveTo($this->resolveStateByPosition('initial'));
    }

    public function moveTo(StateContract $state) : StateContract
    {

        $transition = $this->identifyNextTransition($state);

        if ($transition) {
            return $this->moveThrough($transition);
        }

        throw new TransitioningException("No transition found for moving to {$state->name}.");
    }

    public function getDefinition(): MachineDefinitionContract
    {
        return $this->definition;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel(ProcessedByStatemachine $model): self
    {
        $this->model = $model;

        return $this;
    }
}
