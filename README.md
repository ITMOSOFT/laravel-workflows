# Laravel Workflows

A general-purpose stateful workflow engine for Laravel applications.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-workflows
php artisan vendor:publish --tag=itmosoft-workflows-config
php artisan migrate
```

## Define a workflow

```php
use ItmoSoft\Workflows\WorkflowManager;

$manager = app(WorkflowManager::class);

$definition = $manager->define('order-process', 'Order Process', [
    ['key' => 'submitted', 'name' => 'Submitted'],
    ['key' => 'review', 'name' => 'Under Review'],
    ['key' => 'completed', 'name' => 'Completed'],
]);
```

## Start an instance

```php
$instance = $manager->start(
    definition: $definition,
    subject: $order,
    context: ['priority' => 'high'],
);
```

## Transition an instance

```php
$instance = $manager->transition(
    instance: $instance,
    targetStepKey: 'review',
    actor: auth()->user(),
    context: ['note' => 'Initial checks passed'],
);
```

Moving an instance to the final defined step marks it `completed`. An active instance can
also be cancelled:

```php
$instance = $manager->cancel($instance, auth()->user(), ['reason' => 'Withdrawn']);
```

## Statuses

- `active`
- `completed`
- `cancelled`

## Important behavior

This package records workflow definitions, ordered steps, current instance state, context,
and transition history. It deliberately allows the application to choose any defined target
step; it does not impose a linear-only transition graph. Your application must authorize
transitions and decide which transitions are valid for each business process.

## Scope limitations

No visual builder, HTTP routes, UI, queues, timers, automatic retries, conditional rule engine,
notifications, parallel execution, or approval/assignee system is included. This is not a
marketing automation engine.

## Testing

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
