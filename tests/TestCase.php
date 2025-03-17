<?php

namespace Travelnoord\Logging\Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Travelnoord\Logging\Facades\Ecs;
use Travelnoord\Logging\Formatter\EcsFormatter;
use Travelnoord\Logging\LogServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Travelnoord\Logging\Taps\AddsScrubbingFormatter;

class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            LogServiceProvider::class,
        ];
    }

    /**
     * Override application aliases.
     *
     * @param Application $app
     *
     * @return string[]
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Ecs' => Ecs::class,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Remove all log files from the test directory
        array_map('unlink', array_filter((array)glob(logsPath('*.log'))));
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app->useStoragePath(realpath(__DIR__ . '/storage') ?: '');

        /** @var Repository $config */
        $config = $app->make('config');

        $config->set('logging.default', 'filebeat');
        $config->set('logging.channels.filebeat', [
            'driver'    => 'single',
            'tap' => [AddsScrubbingFormatter::class],
            'formatter' => EcsFormatter::class,
            'path'      => logsPath('laravel.log'),
            'level'     => 'debug',
        ]);
    }
}
