<?php

use GuzzleHttp\Psr7\Response;
use Hamidrezaniazi\Pecs\Fields\User;
use Illuminate\Log\Context\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Travelnoord\Logging\Facades\Ecs;
use Travelnoord\Logging\Fields\Field;

use function Orchestra\Testbench\Pest\defineRoutes;

it('should log the user id', function () {
    Ecs::add(...Field::fromUser(new \Travelnoord\Logging\Tests\User(100)));

    Log::stack(['filebeat', 'filebeat'])->debug('hello');

    expect('laravel.log')->toContainLine([
        'message' => 'hello',
        'user.id' => '100',
    ]);
});

defineRoutes(function (Router $router) {
    $router->get('test', function (Request $request) {
        Ecs::add(...Field::fromHttpRequest($request));

        Log::debug('log from a route');

    });
});

it('should log request data', function () {
    $this->get('test', [
        'referer' => 'https://google.com',
        'x-request-id' => '2345',
    ]);

    expect('laravel.log')->toContainLine([
        'message'               => 'log from a route',
        'http.request.method'   => 'GET',
        'http.request.referer' => 'https://google.com',
        'http.request.id'       => 2345,
        'http.version'          => '1.1',
        'url.original'          => 'http://localhost/test',
    ]);
});

it('should log psr request and response', function () {
    $request = new \GuzzleHttp\Psr7\Request('POST', 'http://google.test', [
        'X-Request-Id' => [$requestId = '5678'],
        'User-Agent'   => ['My User Agent'],
    ], 'body');

    $response = new Response(404, [], 'not found');

    Log::debug('doing psr request', [
        ...Field::psrRequest($request),
    ]);

    expect('laravel.log')->toContainLine([
        'message'                    => 'doing psr request',
        'url.original'               => 'http://google.test',
        'user_agent.original'        => 'My User Agent',
        'http.request.id'            => '5678',
        'http.request.method'        => 'POST',
        'http.request.body.content'  => 'body',
        'http.version'               => '1.1',
    ]);

    Log::debug('doing psr response', [
        ...Field::fromPsrResponse($requestId, $response),
    ]);

    expect('laravel.log')->toContainLine([
        'message'                    => 'doing psr response',
        'http.request.id'            => '5678',
        'http.response.status_code'  => 404,
        'http.response.body.content' => 'not found',
        'http.version'               => '1.1',
    ]);
});

it('should add a global scope', function () {

    Ecs::add(new User(id: '1'));

    Context::add('job_id', 1234);
    Context::add('failed', false);
    Context::add('success', true);

    Log::debug('doing something');

    expect('laravel.log')->toContainLine([
        'message'       => 'doing something',
        'user.id'       => 1,
        'labels.job_id' => 1234,
        'labels.failed' => 0,
        'labels.success' => '1',
    ]);
});

it('should add a context scope', function () {

    Ecs::add(new User(id: '1'));

    Log::debug('doing something', [
        new User(id: '1'),
        'job_id' => 1234,
        'failed' => false,
    ]);

    expect('laravel.log')->toContainLine([
        'message'       => 'doing something',
        'user.id'       => 1,
        'labels.job_id' => 1234,
        'labels.failed' => 0,
    ]);
});

it('should throw an exception', function () {
    $this->expectException(\TypeError::class);

    /* @phpstan-ignore-next-line  */
    Ecs::label('failed', new \stdClass());
});

it('should only scope the log', function () {
    Log::debug('first message');

    expect('laravel.log')
        ->toContainLine(['message' => 'first message'])
        ->not->toContainLine([
            'message' => 'first message',
            'user.id' => 2,
        ]);

    Context::scope(function () {
        Ecs::add(new User(id: '2'))
            ->label('test.key', 3);

        Log::debug('second message', [
            'tester.key' => 4,
        ]);

        expect('laravel.log')->toContainLine([
            'message'  => 'second message',
            'user.id'  => '2',
            'labels.tester_key' => 4,
        ]);

        Log::debug('third message');

        expect('laravel.log')->toContainLine([
            'message'  => 'third message',
            'user.id'  => '2',
            'labels.test_key' => 3,
        ]);
    });

    Log::debug('fourth message');

    expect('laravel.log')->toContainLine([
        'message' => 'fourth message',
    ]);

    expect('laravel.log')->not->toContainLine([
        'message' => 'fourth message',
        'user.id' => 2,
    ]);
})->skip(!method_exists(Repository::class, 'scope'));
