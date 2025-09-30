<?php

declare(strict_types=1);

use Linkxtr\QrCode\QrCodeServiceProvider;

beforeEach(function () {
    // Additional setup for each test if needed
});

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific test case class.
|
*/

uses(\Tests\TestCase::class)
    ->beforeEach(function () {
        // Additional setup for each test if needed
    })
    ->in(__DIR__);

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

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to make them available in all your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Test Service Provider
|--------------------------------------------------------------------------
*/

uses()
    ->beforeEach(fn () => app()->register(QrCodeServiceProvider::class))
    ->in('Feature');
