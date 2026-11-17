<?php

namespace ItmoSoft\Workflows\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowTransitionHistory extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('itmosoft-workflows.tables.history', 'workflow_transition_history');
    }

    protected function casts(): array
    {
        return ['context' => 'array'];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
