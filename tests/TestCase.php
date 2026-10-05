<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        // Never boot tests with cached configuration (which can contain live credentials).
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.testing.php')) {
            throw new RuntimeException('Remove the disposable bootstrap/cache/config.testing.php before running tests.');
        }

        return parent::createApplication();
    }
}
