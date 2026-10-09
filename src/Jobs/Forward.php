<?php

namespace Hyn\Statemachine\Jobs;

use Hyn\Statemachine\Contracts\MachineDefinitionContract;
use Hyn\Statemachine\Contracts\ProcessedByStatemachine;
use Hyn\Statemachine\Statemachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Attempts to move one model forward. Unique per model, so a backed up queue
 * never runs two attempts for the same model side by side.
 */
class Forward implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Releases the lock should a worker die mid-job; otherwise it is released
     * once the job finishes or fails.
     *
     * @var int
     */
    public $uniqueFor = 600;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * @param ProcessedByStatemachine|Model $model
     * @param string $definition class name of a MachineDefinitionContract
     */
    public function __construct(
        public ProcessedByStatemachine $model,
        public string $definition
    ) {}

    public function uniqueId(): string
    {
        return get_class($this->model) . ':' . $this->model->getKey();
    }

    public function handle(): void
    {
        /** @var MachineDefinitionContract $definition */
        $definition = new $this->definition;

        (new Statemachine($this->model, $definition))->forward();
    }
}
