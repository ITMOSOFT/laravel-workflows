<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('itmosoft-workflows.tables.history', 'workflow_transition_history'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')
                ->constrained(config('itmosoft-workflows.tables.instances', 'workflow_instances'))
                ->cascadeOnDelete();
            $table->string('from_step_key', 120)->nullable();
            $table->string('to_step_key', 120)->nullable();
            $table->nullableMorphs('actor');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('itmosoft-workflows.tables.history', 'workflow_transition_history'));
    }
};
