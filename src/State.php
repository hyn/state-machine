<?php

namespace Hyn\Statemachine;


use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Hyn\Statemachine\Contracts\StateContract;
use Illuminate\Database\Eloquent\Model;

abstract class State extends Named implements StateContract
{
    /**
     * @var ProcessedByStatemachine|Model
     */
    protected $model;

    public function __construct(ProcessedByStatemachine $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function isInitial() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() : bool
    {
        return false;
    }
}
