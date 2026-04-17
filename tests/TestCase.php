<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Tests;

use GraystackIT\HelpSpace\HelpSpaceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [HelpSpaceServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('helpspace.api_key', 'test-api-key');
        $app['config']->set('helpspace.client_id', 'test-client-id');
        $app['config']->set('helpspace.base_url', 'https://api.helpspace.com');
    }
}
