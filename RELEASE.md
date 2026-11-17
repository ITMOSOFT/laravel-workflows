# Release checklist

1. Run `composer install`.
2. Run `composer test` and ensure all tests pass.
3. Review package contents and `CHANGELOG.md`.
4. Commit the source to `main`.
5. Create and push the `v1.0.0` tag only after tests and CI pass.
6. Create a GitHub Release with the title and notes below.
7. Submit the repository URL to Packagist.

## GitHub Release title

`v1.0.0 - Initial Release`

## GitHub Release notes

### Highlights
- Workflow definitions and ordered steps.
- Polymorphic workflow instances.
- Start, transition, and cancel operations.
- JSON context and transition history.
- Transactional state changes.

### Installation
```bash
composer require itmosoft/laravel-workflows
php artisan vendor:publish --tag=itmosoft-workflows-config
php artisan migrate
```

### Requirements
- PHP 8.3+
- Laravel 12 or 13

### Limitations
No visual builder, queues, timers, conditional rule engine, notifications, parallel execution,
or approval/assignee system.
