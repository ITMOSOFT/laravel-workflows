<?php

namespace ItmoSoft\Workflows\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowDefinitionStep extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-workflows.tables.steps', 'workflow_definition_steps');
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }
}
