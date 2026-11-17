<?php

namespace ItmoSoft\Workflows\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowDefinition extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-workflows.tables.definitions', 'workflow_definitions');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowDefinitionStep::class, 'workflow_definition_id')
            ->orderBy('step_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'workflow_definition_id');
    }
}
