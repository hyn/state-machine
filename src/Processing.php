<?php

namespace Hyn\Statemachine;

use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Hyn\Statemachine\Contracts\StateContract;
use Hyn\Statemachine\Contracts\TransitionContract;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class Processing
{
    /**
     * @var ProcessedByStatemachine
     */
    public $model;
    /**
     * @var StateContract
     */
    public $from;
    /**
     * @var TransitionContract
     */
    public $transition;

    /**
     * @var Response|null
     */
    public $response;
    /**
     * @var StateContract|null
     */
    public $to;

    public function __construct(ProcessedByStatemachine $model, StateContract $from, TransitionContract $transition)
    {
        $this->model = $model;
        $this->from = $from;
        $this->transition = $transition;
    }

    public function process()
    {
        $result = $this->transition->fire();

        $this->processResult($result);

        return $result;
    }

    public function reset()
    {
        $result = $this->transition->reset();

        $this->processResult($result);

        return $result;
    }

    protected function processResult($result)
    {
        if (is_array($result)) {
            $this->response = Arr::get($result, 'response');
            $this->to = Arr::get($result, 'state');
        }

        if ($result instanceof StateContract) {
            $this->to = $result;
        }

        if ($result instanceof Response) {
            $this->response = $result;
        }
    }
}
