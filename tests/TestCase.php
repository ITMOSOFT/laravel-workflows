<?php

namespace ItmoSoft\Workflows\Tests;

use ItmoSoft\Workflows\WorkflowsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [WorkflowsServiceProvider::class];
    }
}
