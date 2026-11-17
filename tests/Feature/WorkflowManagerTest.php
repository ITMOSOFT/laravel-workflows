<?php

namespace ItmoSoft\Workflows\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ItmoSoft\Workflows\Exceptions\WorkflowException;
use ItmoSoft\Workflows\Tests\Fixtures\Order;
use ItmoSoft\Workflows\Tests\Fixtures\User;
use ItmoSoft\Workflows\Tests\TestCase;
use ItmoSoft\Workflows\WorkflowManager;

class WorkflowManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
        });

        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_it_defines_a_workflow_and_ordered_steps(): void
    {
        $definition = app(WorkflowManager::class)->define('order-process', 'Order Process', [
            ['key' => 'submitted', 'name' => 'Submitted'],
            ['key' => 'review', 'name' => 'Review'],
            ['key' => 'done', 'name' => 'Done'],
        ]);

        $this->assertCount(3, $definition->steps);
        $this->assertSame('submitted', $definition->steps[0]->step_key);
        $this->assertSame(3, $definition->steps[2]->step_order);
    }

    public function test_it_starts_an_instance_at_the_first_step(): void
    {
        [$definition, $order] = $this->workflow();
        $instance = app(WorkflowManager::class)->start($definition, $order, ['priority' => 'high']);

        $this->assertSame('active', $instance->status);
        $this->assertSame('submitted', $instance->current_step_key);
        $this->assertSame('high', $instance->context['priority']);
        $this->assertCount(1, $instance->history);
    }

    public function test_it_transitions_and_records_history(): void
    {
        [$definition, $order] = $this->workflow();
        $manager = app(WorkflowManager::class);
        $instance = $manager->start($definition, $order);
        $actor = User::query()->create(['name' => 'Operator']);

        $instance = $manager->transition($instance, 'review', $actor, ['note' => 'Checked']);

        $this->assertSame('review', $instance->current_step_key);
        $this->assertSame('active', $instance->status);
        $this->assertCount(2, $instance->history);
        $this->assertSame('Checked', $instance->history[1]->context['note']);
    }

    public function test_it_marks_instance_completed_at_final_step(): void
    {
        [$definition, $order] = $this->workflow();
        $manager = app(WorkflowManager::class);
        $instance = $manager->start($definition, $order);
        $instance = $manager->transition($instance, 'review');
        $instance = $manager->transition($instance, 'done');

        $this->assertSame('completed', $instance->status);
        $this->assertNotNull($instance->completed_at);
    }

    public function test_it_rejects_unknown_target_steps(): void
    {
        [$definition, $order] = $this->workflow();
        $manager = app(WorkflowManager::class);
        $instance = $manager->start($definition, $order);

        $this->expectException(WorkflowException::class);
        $manager->transition($instance, 'missing');
    }

    public function test_it_rejects_transition_to_current_step(): void
    {
        [$definition, $order] = $this->workflow();
        $manager = app(WorkflowManager::class);
        $instance = $manager->start($definition, $order);

        $this->expectException(WorkflowException::class);
        $manager->transition($instance, 'submitted');
    }

    public function test_it_can_cancel_an_active_instance(): void
    {
        [$definition, $order] = $this->workflow();
        $instance = app(WorkflowManager::class)->start($definition, $order);
        $instance = app(WorkflowManager::class)->cancel($instance);

        $this->assertSame('cancelled', $instance->status);
        $this->assertNotNull($instance->completed_at);
    }

    public function test_it_rejects_empty_workflow_definitions(): void
    {
        $this->expectException(WorkflowException::class);
        app(WorkflowManager::class)->define('empty', 'Empty', []);
    }

    public function test_it_rejects_duplicate_step_keys(): void
    {
        $this->expectException(WorkflowException::class);
        app(WorkflowManager::class)->define('dup', 'Duplicate', [
            ['key' => 'same', 'name' => 'One'],
            ['key' => 'same', 'name' => 'Two'],
        ]);
    }

    private function workflow(): array
    {
        $definition = app(WorkflowManager::class)->define('order-process', 'Order Process', [
            ['key' => 'submitted', 'name' => 'Submitted'],
            ['key' => 'review', 'name' => 'Review'],
            ['key' => 'done', 'name' => 'Done'],
        ]);
        $order = Order::query()->create(['reference' => 'ORD-100']);

        return [$definition, $order];
    }
}
