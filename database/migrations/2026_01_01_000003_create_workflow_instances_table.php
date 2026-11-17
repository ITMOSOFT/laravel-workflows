<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('itmosoft-workflows.tables.instances', 'workflow_instances'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')
                ->constrained(config('itmosoft-workflows.tables.definitions', 'workflow_definitions'));
            $table->nullableMorphs('subject');
            $table->string('current_step_key', 120);
            $table->string('status', 24)->default('active')->index();
            $table->json('context')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['workflow_definition_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('itmosoft-workflows.tables.instances', 'workflow_instances'));
    }
};
