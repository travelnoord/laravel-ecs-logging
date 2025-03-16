<?php

use Hamidrezaniazi\Pecs\Monolog\EcsFormatter;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Travelnoord\Logging\Facades\Ecs;
use Travelnoord\Logging\Fields\Soap;
use Travelnoord\Logging\Taps\AddsScrubbingFormatter;

use function Orchestra\Testbench\Pest\defineEnvironment;

defineEnvironment(function (Application $app) {
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
});

it('should filter out the password', function () {
    Ecs::secret('password');
    Ecs::secret(['secret-key', true, null]);
    Ecs::secret(['secret-key&test']);

    Log::debug('Logging my password and my secret-key');

    expect('laravel.log')->toContainLine([
        'message' => 'Logging my ***** and my *****',
    ]);

    expect('laravel.log')->not->toContainLine([
        'message' => 'Logging my password and my secret-key',
    ]);
});

it('should filter out response body', function () {
    Ecs::secret('my-secret-password');

    Log::debug('Logging the response', [
        new Soap(
            responseBodyContent: '<auth><pass>my-secret-password</pass></auth>',
        ),
    ]);

    expect('laravel.log')->toContainLine([
        'message' => 'Logging the response',
        'soap.response.body.content' => '<auth><pass>*****</pass></auth>',
    ]);
});
