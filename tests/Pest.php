<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Arr;
use Illuminate\Testing\Constraints\ArraySubset;
use PHPUnit\Framework\AssertionFailedError;
use Travelnoord\Logging\Tests\TestCase;

pest()->extends(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toContainLine', function (array $expected, string $message = ''): void {

    $expectedFile = logsPath($this->value);

    expect($expectedFile)->toBeFile();

    $lines = array_filter(explode(PHP_EOL, (string)file_get_contents($expectedFile)));

    $throwable = null;

    foreach ($lines as $line) {

        try {
            expect($line)->json()->toContainValues($expected, $message);

            return;
        } catch (AssertionFailedError $exception) {
            $throwable = $exception;
        }
        //
        //        expect(Arr::undot(expect($line)->json()->value))->dump()->toMatchArray(Arr::undot($expected));
    }

    if ($throwable instanceof \Throwable) {
        throw $throwable;
    }
});


expect()->extend('toContainValues', function (array $expected, string $message = ''): void {
    $this->toBeArray();

    $constraint = new ArraySubset(Arr::undot($expected));

    $constraint->evaluate(Arr::undot($this->value), $message);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function logsPath(?string $path = null): string
{
    return realpath(__DIR__ . '/storage/logs') . ($path ? '/' . ltrim($path, '/') : '');
}
