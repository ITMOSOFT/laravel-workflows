<?php

namespace ItmoSoft\Workflows;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ItmoSoft\Workflows\Exceptions\WorkflowException;
use ItmoSoft\Workflows\Models\WorkflowDefinition;
use ItmoSoft\Workflows\Models\WorkflowDefinitionStep;
use ItmoSoft\Workflows\Models\WorkflowInstance;
use ItmoSoft\Workflows\Models\WorkflowTransitionHistory;

class WorkflowManager
{
    /**
     * @param array<int, array{key:string,name:string,metadata?:array}> $steps
     */
    public function define(string $key, string $name, array $steps): WorkflowDefinition
    {
        if (trim($key) === '' || trim($name) === '') {
            throw new WorkflowException('Workflow key and name are required.');
        }

        if ($steps === []) {
            throw new WorkflowException('A workflow must contain at least one step.');
        }

        $seen = [];
        foreach ($steps as $step) {
            if (! isset($step['key'], $step['name']) || trim($step['key']) === '' || trim($step['name']) === '') {
                throw new WorkflowException('Each step requires a non-empty key and name.');
            }
            if (isset($seen[$step['key']])) {
                throw new WorkflowException('Workflow step keys must be unique.');
            }
            $seen[$step['key']] = true;
        }

        return DB::transaction(function () use ($key, $name, $steps) {
            $definition = WorkflowDefinition::query()->create([
                'key' => $key,
                'name' => $name,
                'is_active' => true,
            ]);

            foreach (array_values($steps) as $index => $step) {
                $definition->steps()->create([
                    'step_key' => $step['key'],
                    'name' => $step['name'],
                    'step_order' => $index + 1,
                    'metadata' => $step['metadata'] ?? [],
                ]);
            }

            return $definition->load('steps');
        });
    }

    public function start(
        WorkflowDefinition $definition,
        Model $subject,
        array $context = [],
    ): WorkflowInstance {
        if (! $definition->is_active) {
            throw new WorkflowException('Cannot start an inactive workflow definition.');
        }

        $firstStep = $definition->steps()->first();
        if (! $firstStep) {
            throw new WorkflowException('The workflow definition has no steps.');
        }

        return DB::transaction(function () use ($definition, $subject, $context, $firstStep) {
            $instance = WorkflowInstance::query()->create([
                'workflow_definition_id' => $definition->getKey(),
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'current_step_key' => $firstStep->step_key,
                'status' => 'active',
                'context' => $context,
                'started_at' => now(),
            ]);

            $this->record($instance, null, $firstStep->step_key, null, $context);

            return $instance->load('definition.steps', 'history');
        });
    }

    public function transition(
        WorkflowInstance $instance,
        string $targetStepKey,
        ?Model $actor = null,
        array $context = [],
    ): WorkflowInstance {
        return DB::transaction(function () use ($instance, $targetStepKey, $actor, $context) {
            $locked = WorkflowInstance::query()->whereKey($instance->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'active') {
                throw new WorkflowException('Only active workflow instances can transition.');
            }

            $definition = WorkflowDefinition::query()->findOrFail($locked->workflow_definition_id);
            $target = $definition->steps()->where('step_key', $targetStepKey)->first();

            if (! $target) {
                throw new WorkflowException('The target step does not exist in this workflow.');
            }

            $from = $locked->current_step_key;
            if ($from === $targetStepKey) {
                throw new WorkflowException('A workflow cannot transition to its current step.');
            }

            $mergedContext = array_merge($locked->context ?? [], $context);
            $locked->current_step_key = $targetStepKey;

            $lastStep = $definition->steps()->reorder()->orderByDesc('step_order')->first();
            if ($lastStep && $lastStep->step_key === $targetStepKey) {
                $locked->status = 'completed';
                $locked->completed_at = now();
            }

            $locked->context = $mergedContext;
            $locked->save();

            $this->record($locked, $from, $targetStepKey, $actor, $context);

            return $locked->load('definition.steps', 'history');
        });
    }

    public function cancel(WorkflowInstance $instance, ?Model $actor = null, array $context = []): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $actor, $context) {
            $locked = WorkflowInstance::query()->whereKey($instance->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'active') {
                throw new WorkflowException('Only active workflow instances can be cancelled.');
            }

            $from = $locked->current_step_key;
            $locked->status = 'cancelled';
            $locked->completed_at = now();
            $locked->context = array_merge($locked->context ?? [], $context);
            $locked->save();

            $this->record($locked, $from, null, $actor, $context);

            return $locked->load('history');
        });
    }

    private function record(
        WorkflowInstance $instance,
        ?string $from,
        ?string $to,
        ?Model $actor,
        array $context,
    ): void {
        $instance->history()->create([
            'from_step_key' => $from,
            'to_step_key' => $to,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'context' => $context,
        ]);
    }
}
