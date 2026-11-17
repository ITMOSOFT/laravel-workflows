<?php

namespace ItmoSoft\Workflows;

use Illuminate\Support\ServiceProvider;

class WorkflowsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/workflows.php', 'itmosoft-workflows');
        $this->app->singleton(WorkflowManager::class);
        $this->app->alias(WorkflowManager::class, 'itmosoft.workflows');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/workflows.php' => config_path('itmosoft-workflows.php'),
        ], 'itmosoft-workflows-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
