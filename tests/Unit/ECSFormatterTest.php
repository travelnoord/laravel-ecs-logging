<?php

use Hamidrezaniazi\Pecs\Fields\Base;
use Hamidrezaniazi\Pecs\Fields\User;
use Hamidrezaniazi\Pecs\Properties\PairList;
use Hamidrezaniazi\Pecs\Properties\ValueList;
use Illuminate\Support\Facades\Log;
use Travelnoord\Logging\Facades\Ecs;
use Travelnoord\Logging\Fields\Field;

it('formats the log line', function () {
    Log::debug('test');

    expect('laravel.log')->toContainLine([
        'message'     => 'test',
        'log.level'   => 'DEBUG',
    ]);
});

it('should log context correctly', function () {
    Log::debug('with context', [
        'test',
        new Base(labels: (new PairList())->put('label_key', 'tttest')),
        ...Field::labels(['label_key' => 'hellow1']),
        ...Field::labels(['testert' => 'hellow2']),
        new User(id: '1'),
        'label_key' => 'label value',
        ...Field::tags('kept', 'it'),
        new Base(tags: (new ValueList())->push('new value')->push('it')),
    ]);

    expect('laravel.log')->toContainLine([
        'message'     => 'with context',
        'log.level'   => 'DEBUG',
        'user.id'     => '1',
        'tags'        => ['test', 'kept', 'it', 'new value'],
        'labels'      => ['label_key' => 'label value'],
    ]);
});

it('should log an exception in the error key', function () {
    Log::error('Something went wrong', [
        ...Field::fromThrowable($exception = new \Exception('Test')),
    ]);

    expect('laravel.log')->toContainLine([
        'message'   => 'Something went wrong',
        'log.level' => 'ERROR',
        'error.message' => 'Test',
    ]);
});

it('should log an exception', function () {
    $exception = new \Exception('Test');

    Log::error($exception->getMessage(), [
        ...Field::fromThrowable($exception),
    ]);

    expect('laravel.log')->toContainLine([
        'message'   => $exception->getMessage(),
        'log.level' => 'ERROR',
        'error.message' => 'Test',
    ]);
});

it('should log an exception context in the error key', function () {
    $exception = new \Exception('Test 1');

    Log::critical($exception->getMessage(), [
        ...Field::fromThrowable($exception),
    ]);

    expect('laravel.log')->toContainLine([
        'message'       => 'Test 1',
        'log.level'     => 'CRITICAL',
        'error.type'    => 'Exception',
        'error.message' => 'Test 1',
    ]);

    $runtimeException = new \RuntimeException('Test 2');

    Log::error($runtimeException->getMessage(), [
        ...Field::fromThrowable($runtimeException),
    ]);

    expect('laravel.log')->toContainLine([
        'message'       => 'Test 2',
        'log.level'     => 'ERROR',
        'error.type'    => 'RuntimeException',
        'error.message' => 'Test 2',
    ]);

    $runtimeException = new \InvalidArgumentException('Test 3');

    Log::critical('Something went wrong', [
        ...Field::fromThrowable($runtimeException),
    ]);

    expect('laravel.log')->toContainLine([
        'message'       => 'Something went wrong',
        'log.level'     => 'CRITICAL',
        'error.type'    => 'InvalidArgumentException',
        'error.message' => 'Test 3',
    ]);
});

it('should filter out the password', function () {
    Ecs::secret('password');

    Ecs::secret(['secret-key', true, null]);

    Log::debug('Logging my password and my secret-key');

    expect('laravel.log')->toContainLine([
        'message' => 'Logging my ***** and my *****',
    ]);
});

test('log levels', function (string $level, string $message) {
    Log::log($level, $message);

    expect('laravel.log')->toContainLine([
        'message'     => $message,
        'log.level'   => strtoupper($level),
    ]);
})->with('log levels provider');

/**
 * @return array<array<string>>
 */
dataset('log levels provider', function () {
    return [
        ['debug', 'debug message'],
        ['info', 'info message'],
        ['notice', 'notice message'],
        ['warning', 'warning message'],
        ['error', 'error message'],
        ['critical', 'critical message'],
        ['alert', 'alert message'],
        ['emergency', 'emergency message'],
    ];
});
