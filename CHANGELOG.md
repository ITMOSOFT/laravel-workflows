# Changelog

## [1.0.1] - 2026-11-17

### Fixed
- Reset the workflow step relationship ordering before selecting the final step, ensuring instances are marked completed when they transition to the last defined step.

## [1.0.0] - 2026-11-17

### Added
- Workflow definitions with unique keys and ordered steps.
- Polymorphic workflow subjects.
- Workflow instance start, transition, and cancellation.
- JSON context and per-transition history.
- Transaction-protected state changes with row-level locking.
- Config publishing, migrations, PHPUnit tests, and GitHub Actions CI.
