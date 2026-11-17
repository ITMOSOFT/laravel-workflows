<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('itmosoft-workflows.tables.steps', 'workflow_definition_steps'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')
                ->constrained(config('itmosoft-workflows.tables.definitions', 'workflow_definitions'))
                ->cascadeOnDelete();
            $table->string('step_key', 120);
            $table->string('name');
            $table->unsignedInteger('step_order');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['workflow_definition_id', 'step_key']);
            $table->unique(['workflow_definition_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('itmosoft-workflows.tables.steps', 'workflow_definition_steps'));
    }
};
