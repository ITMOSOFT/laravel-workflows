<?php

namespace ItmoSoft\Workflows\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-workflows.tables.instances', 'workflow_instances');
    }

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function history(): HasMany
    {
        return $this->hasMany(WorkflowTransitionHistory::class, 'workflow_instance_id')
            ->orderBy('id');
    }
}
